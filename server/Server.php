<?php
require_once(dirname(dirname(dirname(__FILE__))) . "/conn/conn.php");
require_once(dirname(__FILE__) . "/config.php");
//创建Server对象，监听 127.0.0.1:9501端口
$serv = new Swoole\Server("127.0.0.1", $setting['port']);

$serv->set(array(
    'worker_num'    => 4,     // worker process num
    'dispatch_mode' => 1,
    'daemonize'     => 1,
    'log_file'      => dirname(__FILE__) . '/swoole.log',
    'log_level'     => 0,
));

//监听连接进入事件
$serv->on('Connect', function ($serv, $fd) {
    echo "Client: Connect.\n";
});

//监听数据接收事件
$serv->on('Receive', function ($serv, $fd, $from_id, $data) {
    $receiveData = json_decode($data, true);
    $responseData = [];
    switch ($receiveData['cmd']) {
        case 'reload':
            $serv->reload();
            $responseData['msg'] = '系统正在重启中';
            break;
        case 'stop':
            $serv->shutdown();
            $responseData['msg'] = '系统正在关闭';
            break;
        case 'ping':
            $serv->send($fd, 'pong');
            $serv->close($fd, true);
        default:
            break;
    }

    $responseData['sys_info'] = $serv->stats();
    $serv->send($fd, json_encode($responseData, JSON_UNESCAPED_UNICODE));
    $serv->close($fd, true);
});

//监听连接关闭事件
$serv->on('Close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

$serv->on('WorkerStart', function ($server, $worker_id) use($conn, $setting){
    if (!$server->taskworker) {
        if($worker_id == 0) {
            $server->tick(1000, function ($id) use($server, $conn, $setting){
                $sql="select * from yzf_notify_task where status = '0';";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    $transactionId = $row['order_id'];
                    $noResult = mysqli_query($conn, "select `L_no` from sl_list where `L_genkey` = '" . $transactionId . "' limit 1;");
                    while ($nos = mysqli_fetch_assoc($noResult)) {
                        $transactionId = $nos['L_no'];
                    }
                    $order_id = substr($row['order_id'], 3);
                    $amount = $row['amount'];
                    $ch = curl_init();
                    $data['from'] = $setting['from'];
                    $data['out_trade_no'] = $order_id;
                    $data['amount'] = $amount;
                    $data['transactionId'] = $transactionId;
                    $data['sign'] = MD5($amount . $order_id . $transactionId . $setting['key'] . $setting['from']);
                    curl_setopt($ch, CURLOPT_URL, $setting['server'] . '/api/listen/index');
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                    $res = curl_exec($ch);
                    curl_close($ch);
                    $res = json_decode($res, true);
                    if ($res['code'] == 200 && $res['message'] == 'SUCCESS') {
                        mysqli_query($conn, "update yzf_notify_task set status = '1' where id = " . $row['id']);
                    } else {
                        if($row['times'] <= 5) {
                            $sq1 = "UPDATE `yzf_notify_task` SET `times`=" . ($row['times'] + 1) . " WHERE id = " . $row['id'];
                            mysqli_query($conn, $sq1);
                        } else {
                            $sq2 = "UPDATE `yzf_notify_task` SET `status`='-1' WHERE id = " . $row['id'];
                            mysqli_query($conn, $sq2);
                        }
                    }
                }
            });
        }
    }
});

//启动服务器
$serv->start();