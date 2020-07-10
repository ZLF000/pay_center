<?php
require_once("../../conn/conn.php");
require_once("../../conn/function.php");


$APPID = $C_wx_appid;
$MCHID = $C_wx_mchid;
$KEY = $C_wx_key;
$APPSECRET = $C_wx_appsecret;

$type = 'product';
$id=intval($_POST['pid']);
$M_id=intval($_POST['mid']);
$num=intval(1);
$out_trade_no = $_POST['order_id'];

$pcr = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
$NOTIFY_URL = "http://www.hzywwl.com/centerWx/notify_url.php";
$native_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

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

if(is_array($_POST["email"])){
    for ($i=0 ;$i<count($_POST["email"]);$i++ ) {
        $email=$email.$_POST["email"][$i]."__";
    }
    $email= substr($email,0,strlen($email)-2);
}else{
    $email=$_POST["email"];
}

$sql="select * from sl_product where P_id=".$id;
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$body=mb_substr($row["P_title"],0,10,"utf-8")."...-购买";
$total_fee=$row["P_price"]*100*$num*$P_discount;
$params['appid'] = $APPID;
$params['mch_id'] = $MCHID;
$params['body'] = $body;
$params['total_fee'] = $total_fee;
$params['nonce_str'] = createNoncestr();
$params['out_trade_no'] = $out_trade_no;
$params['spbill_create_ip'] = $_SERVER["REMOTE_ADDR"];
$params['notify_url'] = $NOTIFY_URL;
$params['trade_type'] = 'NATIVE';
$params['attach'] = $type."|".$id."|".''."|".$email."|".'1'."|".$M_id."|".'';
if (isMobile()) {
    $params['trade_type'] = 'MWEB';
    $params['scene_info'] = '{"h5_info": {"type":"Wap","wap_url": ' . gethttp(). $_SERVER["HTTP_HOST"] . ',"wap_name": '.$C_title.'}}';
};

$params['sign'] = getSign($params, $KEY);
$xml = arrayToXml($params);
$res = postXmlCurl($xml, $native_url);
$result = xmlToArray($res);

if ($result['return_code'] == 'SUCCESS') {
    if ($result['result_code'] == 'SUCCESS') {
        if (isMobile()) {
            $code_url = $result['mweb_url'];
        } else {
            $code_url = $result['code_url'];
        }
        echo json_encode(['state' => 1, 'url' => $code_url]);
        die();
    }
} else {
    echo json_encode(['state' => 0, 'msg' => 'error']);
    die();
}

function arrayToXml($arr)
{
    $xml = "<xml>";
    foreach ($arr as $key => $val) {
        if (is_numeric($val)) {
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";

        } else {
            $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }

    }
    $xml .= "</xml>";
    return $xml;
}

function createNoncestr($length = 32)
{
    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
    $str   = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}

function getSign($arr, $key) {
    ksort($arr);
    $stringA = '';
    foreach ($arr as $k => $v) {
        if ($v) {
            $stringA .= $k . '=' . $v . '&';
        }
    }
    $stringA .= 'key=' . $key;
    return strtoupper(MD5($stringA));
}

function postXmlCurl($xml, $url, $second = 30)
{
    //初始化curl
    $ch = curl_init();
    //设置超时
    curl_setopt($ch, CURLOPT_TIMEOUT, $second);
    //这里设置代理，如果有的话
    //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
    //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //严格校验2
    //设置header
    curl_setopt($ch, CURLOPT_HEADER, false);
    //要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //post提交方式
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    //运行curl
    $data = curl_exec($ch);
    //返回结果
    if ($data) {
        curl_close($ch);
        return $data;
    } else {
        $error = curl_errno($ch);
        echo "curl出错，错误码:$error" . "<br>";
        echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
        curl_close($ch);
        return false;
    }
}

function xmlToArray($xml)
{
    //将XML转为array
    $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $array_data;
}


?>


