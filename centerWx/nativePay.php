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
//防重复控制
$sql = "select * from sl_orders where O_genkey = '" . $genkey . "'";
$res = mysqli_query($conn, $sql);
if (mysqli_num_rows($res) > 0) {
    echo '订单重复';
    die;
}

doCurl($setting['domain'] . '/?type=productinfo&id=' . $id, []);
sleep(2);
doCurl($setting['domain'] . '/member/unlogin.php?type=product&id=' . $id . '&genkey=' . $genkey, []);
sleep(2);

$amount = 0;
$sql="Select * from sl_member where M_id=".intval($M_id);
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$M_viptime=$row["M_viptime"];
$M_viplong=$row["M_viplong"];
if($M_viplong-(time()-strtotime($M_viptime))/86400>0){
    $M_vip=1;
    if($M_viplong>30000){
        $P_discount=$C_p_discount2/10;
    }else{
        $P_discount=$C_p_discount/10;
    }
}else{
    $M_vip=0;
    $P_discount=1;
}
$sql="select * from sl_product where P_id=".$id;
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
if($row["P_vip"]==1){
    $amount=p($row["P_price"])*100*$num*$P_discount;
}else{
    $amount=p($row["P_price"])*100*$num;
}
$orderId = substr($genkey, 3);
$url = $setting['domain'] . '/pay/wxpay/native.php';
$result = doCurl($url, [
    'genkey' => $genkey,
    'type' => $type,
    'email' => $email,
    'id' => $id,
    'M_id' => $M_id,
    'num' => $num
]);

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>支付</title>
    <style>
        body {
            background-color: #4595C2;
            font-family: "Microsoft YaHei", 微软雅黑, "宋体";
            padding: 10px;
        }

        .box {
            background-color: #d7eefc;
            border-radius: 30px;
            margin: 25px 15px;
            height: 1000px;
            border-bottom: 50px solid #ffffff;
        }

        .head {
            font-family: fantasy;
            height:100px;
            font-size:45px;
            vertical-align:middle;
            text-align:center;
        }
        .content {
            background-color: #ffffff;
            height: 880px;
            padding: 15px;
        }
        .content-head {
            height: 80px;
            border: 10px dotted #ff9f8b;
            border-radius: 15px;
            background: #ffeafb;
            color: #ff8749;
            font-size: 50px;
            line-height: 80px;
            font-weight: 500;
            text-align: center;
        }
        .content-amount {
            font-size: 100px;
            text-align: center;
            margin: 15px 0;
        }
        .content-order {
            font-size: 36px;
            text-align: center;
            margin: 15px 0;
            color: #8b8b8b;
        }
        .content-code {
            height: 500px;
            text-align: center;
        }
        .content-notice {
            font-size: 50px;
            color: #c11e00;
        }
        .notice {
            color: #fff;
            font-size: 40px;
            padding: 15px;
            line-height: 70px;
            font-family: monospace;
        }
    </style>
</head>
<body>
<div class="box">
    <div class="head">
        <img src="/static/img/微信.png" alt="" height="70" style="vertical-align: middle">
        <span style="line-height: 100px;color: #3b85b1;">微信支付,扫码向我付钱</span>
    </div>
    <div class="content">
        <div class="content-head">
            重复支付修改金额不到账
        </div>
        <div class="content-amount">
            <span>￥<?php echo $amount/100 ?></span>
        </div>
        <div class="content-order">
            <span>订单号: <?php echo $orderId ?></span>
        </div>
        <div class="content-code">
            <div id="qrImg" style="width: 400px;height: 400px;margin: 0 auto"></div>
        </div>
        <div class="content-notice">
            <span>*如付款风险被中断，请多次尝试付款</span>
        </div>
    </div>
</div>
<div class="notice">
    <span style="font-weight: 700;font-size: 45px">注意事项</span><br/>
    <span>
            。 尽量用第二部手机直接扫码支付，成功率100%<br/>
            。 如遇风控提示，关闭提示，继续付款<br/>
            。 风控升级！如遇风险提示，请发送二维码给任意微信好友，在聊天窗口长按二维码3-5秒扫一扫支付。<br/>
        </span>
</div>
</body>
<script src="/pay_center/centerWx/static/js/qrcode.min.js"></script>
<script src="/pay_center/centerWx/static/js/jquery.min.js"></script>
<script>
    var qrcode = new QRCode('qrImg', {width:400,height:400,colorDark: '#000000',colorLight: '#ffffff',correctLevel: QRCode.CorrectLevel.H});
    qrcode.makeCode('<?php echo $result; ?>');
</script>
</html>