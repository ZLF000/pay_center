<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>支付宝即时到账交易接口接口</title>
</head>
<?php
require_once("../conn/conn.php");
require_once("../conn/function.php");
require_once("alipay.config.php");
require_once("lib/alipay_submit.class.php");

$type=$_POST["type"];
if(is_array($_POST["email"])){
    for ($i=0 ;$i<count($_POST["email"]);$i++ ) {
        $email=$email.$_POST["email"][$i]." ";
    }
}else{
    $email=$_POST["email"];
}
$id=intval($_POST["id"]);
$M_id=intval($_POST["M_id"]);
$num=intval($_POST["num"]);
$out_trade_no = $_POST["order_id"];
$body=$type."|".$id."|".''."|".$email."|".$num."|".$M_id."|".'';
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

if($type=="news"){
    $sql="select * from sl_news where N_id=".$id;
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $subject=mb_substr($row["N_title"],0,10,"utf-8")."...-付费阅读";
    $total_fee=$row["N_price"]*$N_discount;
}
if($type=="product"){
    $sql="select * from sl_product where P_id=".$id;
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $subject=mb_substr($row["P_title"],0,10,"utf-8")."...-购买";
    $total_fee=$row["P_price"]*$num*$P_discount;
}

if(isMobile()){
    $parameter = array(
        "service"       => "alipay.wap.create.direct.pay.by.user",
        "partner"       => $alipay_config['partner'],
        "seller_id"  => $alipay_config['seller_id'],
        "payment_type"  => $alipay_config['payment_type'],
        "notify_url"    => $alipay_config['notify_url'],
        "return_url"    => $alipay_config['return_url'],

        "anti_phishing_key"=>$alipay_config['anti_phishing_key'],
        "exter_invoke_ip"=>$alipay_config['exter_invoke_ip'],
        "out_trade_no"  => $out_trade_no,
        "subject"   => $subject,
        "total_fee" => $total_fee,
        "body"  => $body,
        "it_b_pay" => "1440m",
        "app_pay" => "Y",
        "_input_charset"    => trim(strtolower($alipay_config['input_charset']))
    );
}else{
    $parameter = array(
        "service"       => $alipay_config['service'],
        "partner"       => $alipay_config['partner'],
        "seller_id"  => $alipay_config['seller_id'],
        "payment_type"  => $alipay_config['payment_type'],
        "notify_url"    => $alipay_config['notify_url'],
        "return_url"    => $alipay_config['return_url'],

        "anti_phishing_key"=>$alipay_config['anti_phishing_key'],
        "exter_invoke_ip"=>$alipay_config['exter_invoke_ip'],
        "out_trade_no"  => $out_trade_no,
        "subject"   => $subject,
        "total_fee" => $total_fee,
        "body"  => $body,
        "_input_charset"    => trim(strtolower($alipay_config['input_charset']))
    );
}

$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
echo $html_text;
?>
</body>
</html>