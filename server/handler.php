<?php
require_once(dirname(__FILE__) . "/config.php");

$action = $_GET['action'];

if ($action == 'stop' || $action == 'reload') {
    $client = stream_socket_client('tcp://127.0.0.1:' . $setting['port'], $errno,$errstr,1);
    $content = json_encode([
        'cmd' => $action,
    ]);
    fwrite($client,$content,strlen($content));
    $res = fread($client, 8180);
    fclose($client);
    echo $res;
    die();
} else if($action == 'start') {
    exec('cd '.__DIR__.'/ && php Server.php', $out);
    echo json_encode($out);
    die();
}
