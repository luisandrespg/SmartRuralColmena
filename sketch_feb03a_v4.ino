#include "SigFox.h"
#include "DHT.h"
#include "conversions.h"
#include "ArduinoLowPower.h"
#include "TinyGPS.h" 
#include "string.h"
#include "HX711.h" 

#define TPLPIN 5                     // Pin Done del Timer TPL
#define audioPIN A0                  // pin analogico sonido
#define ANALOGPILA A1                // pin analogico bateria
#define DHTPIN1 0                    // Pin digital DHT11
#define DHTPIN2 1                    // Pin digital DHT22
#define pinData 7                    // Pin digital DATOS PESO
#define pinClk 9                     // Pin digital CLOCK PESO
#define SLEEPTIME 30 * 60 * 1000      // tiempo hasta la proxima conexion 30 min
#define SLEEPGPS 2 * 60 * 1000        // tiempo para envío de coordenadas GPS 1s
#define sampleWindow 50              // Ancho ventana en mS (50 mS = 20Hz)
#define CALIBRACION 22500.0          // Datos calibración bascula

int DEBUG = 0;
int SIGFO = 1; 

HX711 bascula;
DHT dht(DHTPIN1, DHT11);
DHT dht2(DHTPIN2, DHT22);
TinyGPS gps;

void setup() {

if (DEBUG) {
  Serial.begin(115200);                // iniciamos consola a otra velocidad para que no interfiera con gps
  while (!Serial) {}
} 
  Serial1.begin(9600);                // iniciamos gps
  
  if (!SigFox.begin()) {              // iniciamos sigfox
                  if (DEBUG) {
                            Serial.println("ERROR!");
                            }
                  return;
                }
  
  analogReference(AR_INTERNAL2V23);        // tensión de referencia para calcular la tension de la bateria
  
  SigFox.end();                           //apagamos el modulo sigfox

  SigFox.debug();                         //iniciamos debug para el indicador del led de tx

  pinMode(TPLPIN, OUTPUT);                //configuramos pin TPL  
 
  dht.begin();                            //iniciamos sensor temperatura DHT11 (exterior)
  dht2.begin();                           //iniciamos sensor temperatura DHT22 (interior)
  
  bascula.begin(pinData, pinClk);         // Iniciar sensor
  bascula.set_scale(CALIBRACION);         // Aplicar la calibración
  bascula.tare();                         // Iniciar la tara,  No tiene que haber nada sobre el peso
}

void loop() {

// ************  DECLARACION DE VARIABLES GPS
    
    int GPS = 0;
    int newData = false;
    float latitud = 0.0f, longitud = 0.0f;


//************* OBTENCIÓN DATO BATERIA

    int analogValor = analogRead(ANALOGPILA);                                                  
    float voltaje, porcentaje; 
    voltaje = analogValor * (4.49/1023);                 // maximo voltaje en baterias LiPo = 4.49
    porcentaje = (voltaje - 3) * (100/1.3);              // Calculos 4.3 - 3 = 1.3; margen voltajes para porcentaje
    if (porcentaje <= 0) porcentaje = 0;                 // si las pilas no están conectadas da una lectura de 0
    

// ************  DECLARACION DE VARIABLES Y OBTENCION DE DATOS DE SENSORES

    float temperatura = dht.readTemperature();            //obtenemos temperatura exterior
    float humedad = dht.readHumidity();                   //obtenemos humedad exterior
    float temperatura2 = dht2.readTemperature();          //obtenemos temperatura interior
    float humedad2 = dht2.readHumidity();                 //obtenemos humedad interior

        if (isnan(temperatura) || isnan(humedad)||isnan(temperatura2) || isnan(humedad2)) {
                      return;
                      if (DEBUG) {
                                  Serial.println("[DHT] Error leyendo DHT");   //COMPROBAMOS LECTURAS SENSORES TEMPERATURA Y HUMEDAD
                                }
                       }

// ************  DECLARACION DE VARIABLES Y OBTENCION DE DATOS DE PESO

  float peso;
  peso = abs(bascula.get_units());                       // obtenemos peso

// ************  DECLARACION DE VARIABLES AUDIO Y LEEMOS SENSOR AUDIO
    
    float mediaaudio = 0;
    int sumaudio = 0, sumcuenta = 100;
    unsigned int sample, audio;

for (int i = 0; i<sumcuenta; i++)                       //BUCLE PARA HACER MEDIA DE 100 DATOS OBTENIDOS
{ 
   unsigned long startMillis= millis();
   unsigned int peakToPeak = 0;
   unsigned int signalMax = 0;
   unsigned int signalMin = 1024;  
   
   while (millis() - startMillis < sampleWindow)       //coge datos durante 50 ms
   {
      sample = analogRead(audioPIN);
      if (sample < 1024)
      {
         if (sample > signalMax)
         {
            signalMax = sample;                      // Actualizar máximo
         }
         else if (sample < signalMin)
         {
            signalMin = sample;                     // Actualizar mínimo
         }
      }
   }
   peakToPeak = signalMax - signalMin;              // Amplitud del sonido
   audio = (peakToPeak * 100) / 1024;  
   sample = 0;
   delay(1);
   sumaudio = sumaudio + audio;
   
}
    
 mediaaudio = sumaudio / sumcuenta;                 // añadimos el valor medio de sonido

// ******************** REPRESENTAMOS POR CONSOLA
   if (DEBUG) { 
      Serial.print("analogValor: ");
      Serial.println(analogValor);
      Serial.print("Voltaje: ");
      Serial.print(voltaje);
      Serial.println(" V ");
      Serial.print("Porcentaje: ");
      Serial.print(porcentaje);
      Serial.println(" %");
      Serial.print("[DHT] Temperatura exterior: ");
      Serial.print(temperatura);
      Serial.println(" ºC");
      Serial.print("[DHT] Temperatura interior: ");
      Serial.print(temperatura2);
      Serial.println(" ºC");
      Serial.print("[DHT] Humedad interior: ");
      Serial.print(humedad2);
      Serial.println(" %");
      Serial.print("Nivel de sonido: ");
      Serial.print(mediaaudio);
      Serial.println(" %");
      Serial.print("Nivel de peso: ");
      Serial.print(peso, 1);
      Serial.println(" Kg");
   }
   
    SigFox.begin();
    delay(100);
            
    SigFox.status();
    delay(1);

 // ***************  CONVERTIMOS DATOS A 2 BYTES (SHORT)

      short porcentajeSigFox = (short)convertoFloatToInt16(porcentaje, 60, -60);
      short sonidoSigFox = (short)convertoFloatToInt16(mediaaudio, 60, -60);
      short pesoSigFox = (short)convertoFloatToInt16(peso, 60, -60);
      short temperaturaSigFox =  (short)convertoFloatToInt16(temperatura, 60, -60);
      short temperatura2SigFox =  (short)convertoFloatToInt16(temperatura2, 60, -60);
      unsigned short humedad2SigFox = (unsigned short)convertoFloatToUInt16(humedad2, 110);
 
 // ***************  INICIAMOS PAQUETE DE DATOS A SIGFOX     
 
 if (SIGFO) {     
      SigFox.beginPacket();                     // INICIALIZAMOS PAQUETE PARA ENVIO     
 
      SigFox.write(pesoSigFox);                 //ENVIAMOS PAQUETES EN TRAMOS DE 2 BYTES
      SigFox.write(sonidoSigFox);
      SigFox.write(porcentajeSigFox);
      SigFox.write(temperaturaSigFox);
      SigFox.write(temperatura2SigFox);
      SigFox.write(humedad2SigFox);
 }
 if (DEBUG) { 
      Serial.print("[SIG] peso SigFox (volt*273,7): ");
      Serial.println(pesoSigFox);
      Serial.print("[SIG] sonido SigFox (volt*273,7): ");
      Serial.println(sonidoSigFox);
      Serial.print("[SIG] voltaje SigFox (volt*273,7): ");
      Serial.println(porcentajeSigFox);
      Serial.print("[SIG] Temperatura SigFox exterior (Temp*273,7): ");
      Serial.println(temperaturaSigFox);
      Serial.print("[SIG] Temperatura SigFox interior (Temp*273,7): ");
      Serial.println(temperatura2SigFox);
      Serial.print("[SIG] Humedad SigFox interior (Hum*595.7818): ");
      Serial.println(humedad2SigFox);
 }
  if (SIGFO) {
      int resultado = SigFox.endPacket(true);                   // Terminamos de enviar el mensaje con ack=true (SOLICITAMOS RESPUESTA)
 
        if (DEBUG) {
            Serial.print("Resultado envío:  ");                 // COMPROBAMOS PAQUETE ENVIADO
            Serial.println(resultado);
        }
          
  if (SigFox.parsePacket()) {                                   // Nos mantenemos a la espera del backend de sigfox durante 30 seg
      
      if (DEBUG) {
            Serial.println("ESPERANDO ACCIÓN DEL SERVIDOR...");
          }
          
  while (SigFox.available()) {                                 // leemos dato de sigfox
 
                    GPS = SigFox.read();                       // leemos y lo introducimos en la variable GPS
                    }

  } else {
 
            if (DEBUG) {
                        Serial.println("NO HAY ACCION DEL SERVIDOR");
                      }
        }
 
if (DEBUG) {
    Serial.print("valor GPS FINAL: ");                        //comprobamos variable GPS por consola
    Serial.println(GPS);
    }
  }

//************************ SI SE HA PULSADO BOTÓN SE MANDAN COORDENADAS DE GPS EXACTAS*****************************
 
if (GPS == 1) {
  
        if (DEBUG) {   
            Serial.println(" Buscando GPS ... ");
            } 
            
 for (unsigned long start = millis(); millis() - start < SLEEPGPS;)  // intentamos la conexión gps durante SLEPPGPS seg
      {
        
        while (Serial1.available())
        {
          
         char c = Serial1.read();                                    // leemos la secuencia gps recibida
              if (gps.encode(c))                 
              {
                  newData = true;     
              }
         }
         if (newData) {break;} 
      }
     
   if (newData)
   {
      gps.f_get_position(&latitud, &longitud);     // añadimos los datos obtenidos del gps
     
   }
      
      if (DEBUG) {
          Serial.print("LAT=");
          Serial.println(latitud, 6);
          Serial.print(" LON=");
          Serial.println(longitud,6);
      }
   
// ***************  INICIAMOS PAQUETE DE DATOS A SIGFOX CON DATOS GPS
    
    SigFox.beginPacket();            
      
    long latitudSigFox=latitud*1000000;                       // TRANSFORMAMOS DATOS A LONG
    long longitudSigFox=longitud*1000000;
      
    SigFox.write(latitudSigFox);                              //enviamos coordenadas
    SigFox.write(longitudSigFox);                           
   
      int resultado = SigFox.endPacket();                     // Terminamos de enviar el mensaje con ack = false
  
  if (DEBUG) {
      Serial.print("Resultado envío:  ");                     // comprobamos envío
      Serial.println(resultado);
        } 
    }  
    
 SigFox.end();                                                // apagamos sigfox   
 if (DEBUG) {
  Serial.println("[TPL] Apagado alimentación módulo TPL5110");
 }
  digitalWrite(TPLPIN, LOW);                                  // Apagar el TPL5110
  delay(1);
  digitalWrite(TPLPIN, HIGH);
  delay(1);                                                                                        
 LowPower.sleep(SLEEPTIME);                                  //apagamos dispositivo para ahorro de bateria durante SLEEPTIME seg                                              
 }
