<?php
    require_once("../server/config.php");
    $genkey = $_GET['genkey'];
    $type = 'product';
    $M_id = 1;
    $num = 1;
    $id = $_GET['pid'];
    $email = $_GET['email'];
    doCurl($setting['domain'] . '/?type=productinfo&id=' . $id, []);
    sleep(2);
    doCurl($setting['domain'] . '/member/unlogin.php?type=product&id=' . $id . '&genkey=' . $genkey, []);
    sleep(2);
    
    $result = doCurl($setting['domain'] . '/pay/alipay/alipayapi.php', [
            'genkey' => $genkey,
            'type' => $type,
            'email' => $email,
            'id' => $id,
            'M_id' => $M_id,
            'num' => $num
        ]);
    print_r($result);
    
    
    function doCurl($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

?>