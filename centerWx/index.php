<?php
    require_once("../conn/conn.php");
    require_once("../conn/function.php");
    $sql="SELECT * FROM `sl_product` WHERE P_id =" . $_GET['pid'];
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $mid = 1;
    if($row) {
      $price = $row['P_price'];
    } else {
        echo '产品id有误';
        die();
    }
    
    $email = '\'' . $_GET['email'] . '\'';
    $pid = $_GET['pid'];
    $order_id = '\'' . $_GET['order_id'] . '\'';
    $is_mobile = isMobile();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <style>
        body{
            margin: 0;
            padding: 0;
        }
        .head {
            height: 80px;
            padding: 5px 30px;
            position:relative;
        }
        .head img {
            position:absolute;
            top:0;
            bottom:0;
            margin:auto;
        }
        .middle {
            padding: 10px 15px;
            background-color: #29242421;
            height: 130px;
            font-size: 15px;
            color: #211C1C;
            position: relative;
        }

    </style>
</head>
<script src="/centerWx/static/js/qrcode.min.js"></script>
<script src="/centerWx/static/js/jquery.min.js"></script>
<body>
<div class="head">
    <img src="/centerWx/static/img/wxpaylogo.png" alt="" width="200" style="margin-bottom: 15px">
</div>
<div class="middle">
    <div style="font-size: 20px;font-weight: 600;margin-bottom: 15px;">
        <span>订单编号：<?php echo $order_id;?></span>
    </div>
    <div style="font-size: 20px;font-weight: 600;margin-bottom: 15px;">
        <span>订单类型：动态微信收款</span>
    </div>
    <div style="font-size: 20px;font-weight: 600;margin-bottom: 15px;">
        <span>应付金额：￥<?php echo $price;?></span>
    </div>
    <div style="font-size: 20px;font-weight: 600;margin-bottom: 15px;">
        <span>邮箱：<?php echo $email;?></span>
    </div>
</div>
<div id="billImage" style="margin-bottom: 15px;width: 150px;height: 150px;"></div>
</body>
<script>
    qr();
    function qr() {
        $.ajax({
            url: 'native.php',
            data: {
                'order_id': <?php echo $order_id;?>,
                'pid': <?php echo $pid; ?>,
                'email': <?php echo $email; ?>,
                'mid': <?php echo $mid; ?>,
            },
            dataType: 'json',
            type: 'post',
            success:function (res) {
                if (res.state) {
                    <?php if ($is_mobile) { ?>
                    location.href = res.url;
                    <?php } else { ?>
                    var qrcode = new QRCode('billImage', {colorDark: '#000000',colorLight: '#ffffff',correctLevel: QRCode.CorrectLevel.H});
                    qrcode.makeCode(res.url);
                    <?php } ?>
                }
            }
        });
    }
</script>
</html>