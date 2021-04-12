<?php

$_id = $_GET["id"];
$_ack = $_GET["ack"];
$accion = recibir_datos_desde_API();
if ($accion == 1) {
    if ($_ack == "true") {
        echo "{";
        echo "\"" . $_id . "\" : { \"downlinkData\" : \"000000000000000$accion\" }";
        echo "}";

    }
    header("HTTP/1.0 200 OK");
    header("Content-Type : application/json");
}
else {
    if ($_ack == "true") {
        echo "{";
        echo "\"". $_id ."\" : { \"noData\" : true }";
        echo "}";
    }
    header("HTTP/1.0 204 No Content");
}

function recibir_datos_desde_API(){
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.thinger.io/v1/users/luisandrespg/buckets/smartruralcolmenaACCION/data?items=1&sort=desc',
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

    return $respuesta_json[0]['val']['accion'];
}
