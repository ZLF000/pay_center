<?php
require_once("../../conn/conn.php");
require_once("../../conn/function.php");
require_once("../server/config.php");

$genkey = $_GET['genkey'];
$type = 'product';
$M_id = 1;
$num = 1;
$id = $_GET['pid'];
$email = $_GET['email'];

$sql="Select * from sl_member where M_id=".intval($M_id);
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$M_id=$row["M_id"];
$M_email=$row["M_email"];
$M_money=$row["M_money"];
$M_viptime=$row["M_viptime"];
$M_viplong=$row["M_viplong"];

if($M_viplong-(time()-strtotime($M_viptime))/86400>0){
    $M_vip=1;
    if($M_viplong>30000){
        $N_discount=$C_n_discount2/10;
        $P_discount=$C_p_discount2/10;
    }else{
        $N_discount=$C_n_discount/10;
        $P_discount=$C_p_discount/10;
    }
}else{
    $M_vip=0;
    $N_discount=1;
    $P_discount=1;
}

$sql="select * from sl_product where P_id=".$id;
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$subject=mb_substr($row["P_title"],0,10,"utf-8")."...-购买";
$P_title=$row["P_title"];
$P_pic=splitx($row["P_pic"],"|",0);
$P_mid=$row["P_mid"];
if($row["P_vip"]==1){
    $total_fee=$row["P_price"]*$num*$P_discount;
}else{
    $total_fee=$row["P_price"]*$num;
}

mysqli_query($conn, "insert into sl_orders(O_pid,O_mid,O_time,O_type,O_price,O_num,O_content,O_title,O_pic,O_address,O_state,O_genkey,O_sellmid) values($id,$M_id,'".date('Y-m-d H:i:s')."',0,$total_fee,$num,'','$P_title','$P_pic','$email',0,'$genkey',$P_mid)");

$send['out_trade_no'] = '';
$send['trade_no'] = date("YmdHis");
$send['trade_status'] = 'TRADE_SUCCESS';
$send['total_fee'] = $total_fee;
$send['body'] = $type."|".$id."|".$genkey."||".$num."|".$M_id."|".intval($_SESSION["uid"]);

$res = doCurl($setting['domain'] . '/pay/alipay/notify_test.php', $send, 1);

echo $res;die();

function doCurl($url, $data = [], $method = 0) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if ($method == 1) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
