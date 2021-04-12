<?php
$datos = json_decode(file_get_contents('php://input'), true);

//error_log(print_r($datos, TRUE), 0);
/*error_log(print_r($datos['bateria'] / 273, TRUE), 0);
error_log(print_r($datos['sonido'] / 273, TRUE), 0);
error_log(print_r($datos['peso'] / 273, TRUE), 0);
error_log(print_r($datos['temperatura_ext'] / 273.7, TRUE), 0);
error_log(print_r($datos['temperatura_int'] / 273.7, TRUE), 0);
error_log(print_r($datos['humedad_int'] / 595.7818, TRUE), 0);
error_log(print_r($datos['coordenadasGPS']['lat'], TRUE), 0);
error_log(print_r($datos['coordenadasGPS']['lng'], TRUE), 0);*/

$latitud = substr($datos['coordenadasGPS']['lat'], 0, strpos($datos['coordenadasGPS']['lat'], '.') + 6);
$longitud = substr($datos['coordenadasGPS']['lng'], 0, strpos($datos['coordenadasGPS']['lng'], '.') + 6);
$peso = number_format(($datos['peso'] / 273.7), 1);
$sonido = number_format(($datos['sonido'] / 273.7), 0);
$bateria = number_format(($datos['bateria'] / 273.7), 0);
$temp_ext = number_format(($datos['temperatura_ext'] / 273.7), 1);
$temp_int = number_format(($datos['temperatura_int'] / 273.7), 1);
$humedad_int = number_format(($datos['humedad_int'] / 595.78), 1);

//************************ALARMAS********************
$correo = "luisandrespg@gmail.com";
$alarma = "ALARMA SMART RURAL COLMENA";
$mensajepeso = "PESO = $peso, PESO ELEVADO, RECOGER MIEL!!!.";
$mensajesonido = "SONIDO = $sonido, SONIDO DE LA COLMENA POR DEBAJO DEL NIVEL, POSIBLE ENJAMBRÓN. REVISAR!!.";
$mensajebateria = "BATERIA = $bateria, BATERIA CON BAJA CARGA, CAMBIAR PILAS O RECARGAR.";
$mensajetemp_ext = "TEMPERATURA EXTERIOR = $temp_ext, TEMPERATURA EXTERIOR EXTREMA.";
$mensajetemp_int = "TEMPERATURA INTERIOR = $temp_int, TEMPERATURA INTERIOR FUERA DE LOS PARÁMETROS IDÓNEOS. REVISAR!!.";
$mensajehum_int = "HUMEDAD INTERIOR = $humedad_int, HUMEDAD INTERIOR FUERA DE LOS PARÁMETROS IDÓNEOS. REVISAR!!.";

if ($peso>20) {
    $ledpeso = 255;
    mail ($correo, $alarma, $mensajepeso);
}
else {
    $ledpeso = 0;
}
if ($sonido<5) {
    $ledsonido = 255;
    mail ($correo, $alarma, $mensajesonido);
}
else {
    $ledsonido = 0;
}
if ($bateria<20 && $bateria>0) {
    $ledbateria = 255;
    mail ($correo, $alarma, $mensajebateria);
}
else {
    $ledbateria = 0;
}
if ($temp_ext>30 || $temp_ext<10) {
    $ledtemp_ext = 255;
    mail ($correo, $alarma, $mensajetemp_ext);
}
else {
    $ledtemp_ext = 0;
}
if (($temp_int>35 || $temp_int<15)&&($temp_int>0)) {
    $ledtemp_int= 255;
    mail ($correo, $alarma, $mensajetemp_int);
}
else {
    $ledtemp_int = 0;
}
if (($humedad_int>60 || $humedad_int<30)&&($humedad_int>0)) {
    $ledhum_int= 255;
    mail ($correo, $alarma, $mensajehum_int);
}
else {
    $ledhum_int = 0;
}

/*
error_log(print_r($datos, TRUE), 0);
error_log(print_r($peso, TRUE), 0);
error_log(print_r($sonido, TRUE), 0);
error_log(print_r($bateria, TRUE), 0);
error_log(print_r($temp_ext, TRUE), 0);
error_log(print_r($temp_int, TRUE), 0);
error_log(print_r($humedad_int, TRUE), 0);
*/
//$url = 'https://api.thinger.io/v1/users/luisandrespg/buckets/smartruralcolmena/data';
//$bearer = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiJzbWFydHJ1cmFsY29sbWVuYSIsInVzciI6Imx1aXNhbmRyZXNwZyJ9.mXehiC164eXnik4i6bi39c-p9cOwny8Gp-zDFm1uYu8';

//**********************PREPARACIÓN MENSAJE*****************************

$mensaje = '{
                "latitud" : "'.$latitud.'",
                "longitud" : "'.$longitud.'",  
                 "peso" : "'.$peso.'",
                 "sonido" : "'.$sonido.'",
                "bateria" : "'.$bateria.'",
                "temperatura_ext" : "'.$temp_ext.'",
                "temperatura_int" : "'.$temp_int.'",
                "humedad_int" : "'.$humedad_int.'"
             }';

$mensajealarmas = '{
                 "peso" : "'.$ledpeso.'",
                 "sonido" : "'.$ledsonido.'",
                 "bateria" : "'.$ledbateria.'",
                "temperatura_ext" : "'.$ledtemp_ext.'",
                "temperatura_int" : "'.$ledtemp_int.'",
                "humedad_int" : "'.$ledhum_int.'"
             }';

if (($datos['peso']!=null || $datos['sonido']!=null || $datos['bateria']!=null || $datos['temperatura_ext']!=null || $datos['temperatura_int']!=null || $datos['humedad_int']!=null))
{
    enviar_datos_API($mensaje);
    enviar_datos_API2($mensajealarmas);
}
else {
    //error_log(print_r("muere script", TRUE), 0);
    exit();
}

function enviar_datos_API($mensaje){
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.thinger.io/v1/users/luisandrespg/buckets/smartruralcolmena/data',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $mensaje,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiJzbWFydHJ1cmFsY29sbWVuYW5vZGUiLCJ1c3IiOiJsdWlzYW5kcmVzcGcifQ.mibyoNDAjM2v8Fel4wJetOynRh5Ipc_W8BPZwGI4L2E'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    //echo $response;
    //error_log(print_r($response, TRUE), 0);
    //error_log(print_r($mensaje, TRUE), 0);
}

function enviar_datos_API2($mensajealarmas){
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.thinger.io/v1/users/luisandrespg/buckets/smartruralcolmenaLED/data',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $mensajealarmas,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiJzbWFydHJ1cmFsY29sbWVuYW5vZGUiLCJ1c3IiOiJsdWlzYW5kcmVzcGcifQ.mibyoNDAjM2v8Fel4wJetOynRh5Ipc_W8BPZwGI4L2E'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    //echo $response;
    //error_log(print_r($response, TRUE), 0);
    //error_log(print_r($mensaje, TRUE), 0);
}

