<?php
$datos = json_decode(file_get_contents('php://input'), true);

if (( ! empty($datos['latitudGPS']))){
    //error_log(print_r($datos, TRUE), 0);
    $latitud = number_format(($datos['latitudGPS'] / 1000000), 6);
    $longitud = number_format(($datos['longitudGPS'] / 1000000), 6);


    $mensaje = '{
                "latitud" : "'.$latitud.'",
                "longitud" : "'.$longitud.'"  
             }';
    if (($latitud>20 && $latitud<60) && ($longitud>-10 && $longitud <10))
    {
        enviar_datos_API($mensaje);
    }
    else {
        //error_log(print_r("muere sccript", TRUE), 0);
        exit();
    }

} else {

    //error_log(print_r("muere sccript", TRUE), 0);
    exit();
}




function enviar_datos_API($mensaje){
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.thinger.io/v1/users/luisandrespg/buckets/smartruralcolmenaGPS/data',
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

    //error_log(print_r($response, TRUE), 0);
    //error_log(print_r($mensaje, TRUE), 0);
}