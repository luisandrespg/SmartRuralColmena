<?php
//echo '<meta http-equiv="refresh" content="5">';
//echo 'Ultimo reload a las : ' . date('H:i:s');
echo '<br>';
imprimir_error_log();
//imprimir_datos_desde_API();

function imprimir_error_log(){
    echo '<h1>Información que has enviado a http://smart-rural.cruzestudio.es/api.php</h1>';
    echo '<pre>';
    echo file_get_contents('error_log');
    echo '</pre>';
    echo '<br><br>';
    echo '<h1>FIN: Información que has enviado a http://smart-rural.cruzestudio.es/api.php</h1>';
    echo '<hr>';
}

function imprimir_datos_desde_API(){
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.thinger.io/v1/users/luisandrespg/buckets/smartruralcolmena/data?items=1&sort=desc',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiJzbWFydHJ1cmFsY29sbWVuYW5vZGUiLCJ1c3IiOiJsdWlzYW5kcmVzcGcifQ.mibyoNDAjM2v8Fel4wJetOynRh5Ipc_W8BPZwGI4L2E'
        ),
    ));
    $thinger_respuesta = curl_exec($curl);
    curl_close($curl);
    $respuesta_json = json_decode($thinger_respuesta, true, 4);




//2021-02-05T01:18:30.219Z
    $dt = new \DateTime();
    $dt->setTimestamp(substr($respuesta_json[0]['ts'], 0, 10));
    highlight_string("<?php\n\$data =\n" . var_export($dt, true) . ";\n?>");



    echo '<hr>';
    highlight_string("<?php\n\$data =\n" . var_export($respuesta_json, true) . ";\n?>");
}



