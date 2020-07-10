<?php

require_once("../../conn/conn.php");
require_once("../../conn/function.php");

$APPID = $C_wx_appid;
$MCHID = $C_wx_mchid;
$KEY = $C_wx_key;
$APPSECRET = $C_wx_appsecret;

if($MCHID=="" || $KEY=="") {
	die();
}

$xml = file_get_contents("php://input");

$notify = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

if ($notify['return_code'] == 'SUCCESS' && $notify['result_code'] == 'SUCCESS') {

    $sign = $notify['sign'];
    unset($notify['sign']);
    $order_id = $notify['out_trade_no'];
    $mySign = getSign($notify, $KEY);

    if ($mySign == $sign) {
        //订单处理
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://dd.8ipay.com/api/listen/index');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'from' => '5',
            'out_trade_no' => $order_id,
            'amount' => $notify['total_fee'],
            'client_ip' => $_SERVER['REMOTE_ADDR'],
            'sign' => MD5($notify['total_fee'] . $order_id . $_SERVER['REMOTE_ADDR'] . 'c000abf62131245b85cf' . '5') 
            ]));
        $result = curl_exec($ch);
        curl_close($ch);
        
        $body = explode("|",$notify['attach']);
		$type = $body[0];
		$id = intval($body[1]);
		$genkey = $body[2];
		$email = $body[3];
		$num = intval($body[4]);
		$M_id = intval($body[5]);
	    notify(t($notify['transaction_id']),$type,$id,$genkey,$email,$num,$M_id,($notify['total_fee']/100),$D_domain,"微信支付");
        
    }
    echo 'SUCCESS';
} else {
    echo 0;
}

function returnXml(){
    header("Content-type:text/xml;");
    $xml = "<?xml version='1.0' encoding='UTF-8'?>\n";
    $xml .= "<xml>\n";
    $xml .= "<return_code>SUCCESS</return_code>\n";
    $xml .= "<return_msg>OK</return_msg>\n";
    $xml .= "</xml>\n";
    echo  $xml;
}

function getSign($data, $key){
    //签名步骤一：按字典序排序参数
    ksort($data);
    $stringA = '';
    foreach ($data as $k => $v) {
        if ($v) {
            $stringA .= $k . '=' . $v . '&';
        }
    }
    //签名步骤二：在string后加入KEY
    $stringA = $stringA."key=".$key;

    //签名步骤三：MD5加密
    $stringA = MD5($stringA);
    //签名步骤四：所有字符转为大写
    $result_ = strtoupper($stringA);
    return $result_;
}



?>