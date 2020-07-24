<?php
error_reporting(0);
require_once(dirname(__FILE__) . "/config.php");
$client = stream_socket_client('tcp://127.0.0.1:' . $setting['port'], $errno,$errstr,1);
$content = json_encode([
    'cmd' => 'ping',
]);
fwrite($client,$content,strlen($content));
$res = fread($client, 8180);
fclose($client);
if ($res == 'pong') {
    echo '1';
} else {
    echo '0';
}
die();