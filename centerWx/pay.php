<?php
require_once("../../conn/conn.php");
require_once("../../conn/function.php");
require_once("../server/config.php");

$scheme = $_SERVER['REQUEST_SCHEME']; //协议
$domain = $_SERVER['HTTP_HOST']; //域名/主机
$requestUri = $_SERVER['REQUEST_URI']; //请求参数
//将得到的各项拼接起来
$currentUrl = $scheme . "://" . $domain . $requestUri;

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

$genkey = $_GET['genkey'];
if($redirect) {
    $redirect = $scheme . "://" . $domain . '/pay_center/centerWx/check.php?url=' . urlencode($redirect) . '&genkey=' .$genkey;
}

//防重复控制
$sql = "select * from sl_orders where O_genkey = '" . $genkey . "'";
$res = mysqli_query($conn, $sql);
if (mysqli_num_rows($res) > 0) {
    if($redirect != '') {
        Header("Location: $redirect");
    }
    echo '订单重复';
    die;
}
$type = 'product';
$M_id = 1;
$num = 1;
$id = $_GET['pid'];
$email = $_GET['email'];


doCurl($setting['domain'] . '/?type=productinfo&id=' . $id, []);
doCurl($setting['domain'] . '/member/unlogin.php?type=product&id=' . $id . '&genkey=' . $genkey, []);
$isMobile = 0;
if(isMobile()){
    $isMobile = 1;
} else {
    $url = $setting['domain'] . '/pay/wxpay/native.php';
    $result = doCurl($url, [
        'genkey' => $genkey,
        'type' => $type,
        'email' => $email,
        'id' => $id,
        'M_id' => $M_id,
        'num' => $num
    ]);
}


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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>收银台</title>
    <style>
        html {
            color: #4D4D4D;
        }
        #header {
            height: 60px;
            background-color: #fff;
            border-bottom: 1px solid #d9d9d9;
            margin-top: 0px;
        }

        #header .header-title {
            width: 250px;
            height: 60px;
            float: left;
        }

        #header .logo {
            float: left;
            height: 31px;
            width: 95px;
            margin-top: 14px;
            text-indent: -9999px;
            background: none;
        !important
        }

        #header .logo-title {
            font-size: 16px;
            font-weight: normal;
            font-family: "Microsoft YaHei", 微软雅黑, "宋体";
            border-left: 1px solid #676d70;
            color: #676d70;
            height: 20px;
            float: left;
            margin-top: 15px;
            margin-left: 10px;
            padding-top: 10px;
            padding-left: 10px;
        }

        .header-container {
            width: 950px;
            margin: 0 auto;
        }

        #footer #ServerNum {
            color: #eff0f1;
        }

        #order.order-bow .orderDetail-base,
        #order.order-bow .ui-detail {
            border-bottom: 3px solid #bbb;
            background: #eff0f1;
            color: #000;
        }

        #order.order-bow .orderDetail-base, #order.order-bow .ui-detail {
            border-bottom: 3px solid #b3b3b3;
        }

        .alipay-logo {
            display: block;
            width: 114px;
            position: relative;
            left: 0;
            top: 10px;
            float: left;
            height: 40px;
            background-position: 0 0;
            background-repeat: no-repeat;
            background-image: url('/pay_center/centerWx/static/img/wxpaylogo.png');
            background-size: contain;
        }

        #container {
            width: 950px;
            margin: 0 auto;
            overflow: hidden;
        }

        #order {
            position: relative;
            z-index: 10;
        }

        #center {
            overflow: hidden;
            position: relative;
            z-index: 1;
            width: 950px;
            min-height: 460px;
            background-color: #fff;
            border-bottom: 3px solid #b3b3b3;
            border-top: 3px solid #b3b3b3;
        }
    </style>
</head>
<body>
<div id="header">
    <div class="header-container fn-clear">
        <div class="header-title">
            <div class="alipay-logo"></div>
            <span class="logo-title">我的收银台</span>
        </div>
    </div>
</div>
<div id="container">
    <div id="order">
        <div style="margin-top: 15px;padding: 16px 23px;position: relative;">
            <span style="color: #000;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;font-weight: 700;font-size: 14px;">
                <div id="time"></div>
            </span>
            <div style="height: 22px;overflow: hidden;padding-top: 14px;">
                <span style="color: #000;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;font-weight: 700;font-size: 14px;float: left;">
                    商品编号: <?php echo $id;?>
                </span>
                <span style="bottom: 36px;position: absolute;right: 23px;text-align: right;z-index: 1;color: #000;">

                </span>
            </div>
        </div>
    </div>
    <div id="center">
        <div style="margin: 0 auto;position: relative;width: 300px;color: #4D4D4D;">
            <div style="display: block;width: auto;margin: 0;padding: 0;margin-top: 75px;margin-bottom: 16px;">
                <div style="text-align: center;font-size: 12px">扫一扫付款（元）</div>
            </div>
            <div style="position: relative;width: 168px;height: auto;min-height: 168px;margin: 0 auto;padding: 6px;border: 1px solid #d3d3d3;box-shadow: 1px 1px 1px #ccc">
                <div id="qrImg" style="width: 168px;height: 168px"></div>
            </div>
        </div>
    </div>
</div>
</body>
<script src="/pay_center/centerWx/static/js/qrcode.min.js"></script>
<script src="/pay_center/centerWx/static/js/jquery.min.js"></script>
<script>

    <?php if($isMobile == '1'){ ?>
    $.ajax({
        url: '../../pay/wxpay/native.php',
        data: {
            'genkey' : '<?php echo $genkey; ?>',
            'type' : '<?php echo $type; ?>',
            'email' : '<?php echo $email; ?>',
            'id' : '<?php echo $id; ?>',
            'M_id' : '<?php echo $M_id; ?>',
            'num' : '<?php echo $num; ?>'
        },
        type: 'post',
        success:function(res){
            window.location.href = res + <?php echo '\'&redirect_url=' . $redirect . '\''; ?>;
        }
    });
    <?php } else { ?>
    var qrcode = new QRCode('qrImg', {width:168,height:168,colorDark: '#000000',colorLight: '#ffffff',correctLevel: QRCode.CorrectLevel.H});
    qrcode.makeCode('<?php echo $result; ?>');
    <?php } ?>


</script>
</html>