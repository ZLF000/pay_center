<?php
require_once("../conn/conn.php");
require_once("../conn/function.php");
require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");

//计算得出通知验证结果
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();

if($verify_result) {//验证成功
    $out_trade_no = $_POST['out_trade_no'];
    $trade_no = t($_POST['trade_no']);
    //交易状态
    $trade_status = $_POST['trade_status'];
    $total_fee = $_POST["total_fee"];

    if($_POST['trade_status'] == 'TRADE_FINISHED') {
        //判断该笔订单是否在商户网站中已经做过处理
        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
        //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
        //如果有做过处理，不执行商户的业务程序

        //注意：
        //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知

        //调试用，写文本函数记录程序运行情况是否正常
        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
    }
    else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {

        if ($_POST["trade_status"] = "TRADE_SUCCESS") {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://dd.8ipay.com/api/listen/index');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'from' => '5',
                'out_trade_no' => $out_trade_no,
                'amount' => $total_fee*100,
                'client_ip' => $_SERVER['REMOTE_ADDR'],
                'sign' => MD5($total_fee*100 . $out_trade_no . $_SERVER['REMOTE_ADDR'] . 'c000abf62131245b85cf' . '5') 
            ]));
            $result = curl_exec($ch);
            curl_close($ch);
            $body = $_POST['body'];
            $body = explode("|",$_POST["body"]);
    		$type = $body[0];
    		$id = intval($body[1]);
    		$genkey = $body[2];
    		$email = $body[3];
    		$no=intval($body[4]);
    		$M_id=intval($body[5]);
	        notify($trade_no,$type,$id,$genkey,$email,$no,$M_id,$total_fee,$D_domain,"支付宝");
        }
        //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

        echo "success";		//请不要修改或删除

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    }
    else {
        //验证失败
        echo "fail";

        //调试用，写文本函数记录程序运行情况是否正常
        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
    }
}
?>