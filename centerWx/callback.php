<?php
require_once("../../conn/conn.php");
require_once("../../conn/function.php");
require_once("../../pay_center/server/config.php");

$APPID = $C_wx_appid;
$MCHID = $C_wx_mchid;
$KEY = $C_wx_key;
$APPSECRET = $C_wx_appsecret;

if($MCHID=="" || $KEY=="") {
    die();
}

$postArr = file_get_contents("php://input");
libxml_disable_entity_loader(true);
$postObj = simplexml_load_string( $postArr );
$appid=$postObj->appid;
$attach=$postObj->attach;
$bank_type=$postObj->bank_type;
$cash_fee=$postObj->cash_fee;
$device_info=$postObj->device_info;
$fee_type=$postObj->fee_type;
$is_subscribe=$postObj->is_subscribe;
$mch_id=$postObj->mch_id;
$nonce_str=$postObj->nonce_str;
$openid=$postObj->openid;
$out_trade_no=$postObj->out_trade_no;
$result_code=$postObj->result_code;
$return_code=$postObj->return_code;
$time_end=$postObj->time_end;
$total_fee=$postObj->total_fee;
$trade_type=$postObj->trade_type;
$transaction_id=$postObj->transaction_id;
$sign=$postObj->sign;
$O_ids=$attach;
$coupon_fee=$postObj->coupon_fee;
$arr = json_decode(json_encode(simplexml_load_string($postArr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
unset($arr['sign']);
$newsign = getSign($arr,$KEY);
$D_domain=splitx($_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"],"/pay",0);
if($newsign==$sign){
    if($result_code=="SUCCESS") {
        if(substr_count($attach,"|")==6){
            $body = explode("|",$attach);
            $type = $body[0];
            $id = intval($body[1]);
            $genkey = $body[2];
            $email = $body[3];
            $num = intval($body[4]);
            $M_id = intval($body[5]);
            $_SESSION["uid"]=intval($body[6]);
            notify(t($transaction_id),$type,$id,$genkey,$email,$num,$M_id,($total_fee/100),$D_domain,"微信支付");
            if(preg_match("/yzf\d{22}/", $genkey)) {
                callSystem($conn, $setting['from'], $setting['key'], $setting['server'], $genkey, $total_fee/100, $transaction_id);
            }
        }else{
            $M_id=intval(splitx($O_ids,"|",0));
            $L_genkey=splitx($O_ids,"|",1);
            $sql="Select * from sl_list where L_no='".t($transaction_id)."'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            if (mysqli_num_rows($result) <= 0) {
                mysqli_query($conn,"update sl_member set M_money=M_money+".($total_fee/100)." where M_id=".intval($M_id));
                mysqli_query($conn, "insert into sl_list(L_mid,L_no,L_title,L_time,L_money,L_genkey) values($M_id,'$transaction_id','帐号充值','".date('Y-m-d H:i:s')."',".($total_fee/100).",'$L_genkey')");
                sendmail("有用户通过微信充值","用户ID：".$M_id."<br>充值金额：".($total_fee/100)."元<br>交易单号：".$transaction_id,$C_email);
            }
        }
        exit('<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>');
    } else {
        exit('<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>');
    }
} else {
    exit('<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>');
}

function callSystem($conn, $from, $key, $server, $genkey, $amount, $transactionId) {
    $order_id = substr($genkey, 3);
    $ch = curl_init();
    $data['from'] = $from;
    $data['out_trade_no'] = $order_id;
    $data['amount'] = $amount;
    $data['transactionId'] = strval($transactionId);
    $data['sign'] = MD5($amount . $order_id . $transactionId . $key . $from);
    curl_setopt($ch, CURLOPT_URL, $server . '/api/listen/index');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $res = curl_exec($ch);
    curl_close($ch);
    $res = json_decode($res, true);
    if ($res['code'] == 200 && $res['message'] == 'SUCCESS') {
        myLog($data['out_trade_no'] . '回调成功');
        mysqli_query($conn, "INSERT INTO `notify_record`(`order_id`) VALUES ($order_id)");
    } else {
        myLog($data['out_trade_no'] . '回调失败,回调返回:' . $res);
    }
}


function posturl($url,$data){
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_exec($curl);
    curl_close($curl);
}
function getSign($arr, $key) {
    // ksort($arr);
    $stringA = '';
    foreach ($arr as $k => $v) {
        if($v)
            $stringA .= $k . '=' . $v . '&';
    }

    $stringA .= 'key=' . $key;
    $stringA = MD5($stringA);
    $stringA = strtoupper($stringA);
    return $stringA;
}
function myLog($content){
    $filename = 'wxLog.log';
    $Ts = fopen($filename,"a+");
    fputs($Ts,"执行日期：" . date('Y-m-d H:i:s',time()) .  ' ' . "\n" .$content."\n");
    fclose($Ts);
}


?>