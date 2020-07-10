<?php
require_once("../../conn/conn.php");
require_once("../../conn/function.php");
require_once("../server/config.php");

    $APPID = $C_wx_appid;
    $MCHID = $C_wx_mchid;
    $KEY = $C_wx_key;
    $D_domain=splitx($_SERVER["HTTP_HOST"],"/pay",0);
    
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

    if($row["P_vip"]==1){
        $total_fee=$row["P_price"]*100*$num*$P_discount;
    }else{
        $total_fee=$row["P_price"]*100*$num;
    }
    $P_title=$row["P_title"];
    $P_pic=splitx($row["P_pic"],"|",0);
    $P_mid=$row["P_mid"];
    
    mysqli_query($conn, "insert into sl_orders(O_pid,O_mid,O_time,O_type,O_price,O_num,O_content,O_title,O_pic,O_address,O_state,O_genkey,O_sellmid) values($id,$M_id,'".date('Y-m-d H:i:s')."',0,".($total_fee/100).",$num,'','$P_title','$P_pic','$email',0,'$genkey',$P_mid)");
    
    $send['appid'] = $APPID;
    $send['attach'] = $type."|".$id."|".$genkey."|".$email."|".$num."|".$M_id."|".intval($_SESSION["uid"]);
    $send['bank_type'] = '';
    $send['cash_fee'] = '';
    $send['fee_type'] = '';
    $send['is_subscribe'] = '';
    $send['mch_id'] = $MCHID;
    $send['nonce_str'] = '';
    $send['openid'] = '';
    $send['out_trade_no'] = '';
    $send['result_code'] = 'SUCCESS';
    $send['return_code'] = 'SUCCESS';
    $send['time_end'] = '';
    $send['total_fee'] = $total_fee;
    $send['trade_type'] = '';
    $send['transaction_id'] = date("YmdHis");
    $send['sign'] = getSign($send, $KEY);
    $xml = arrayToXml($send);
    
    $res = doCurl($setting['domain'] . '/pay/wxpay/notify_url.php', $xml, 1);

    if($res === 0) {
        echo 'failure';
    } else {
        echo 'success';
    }
    die();
    
function doCurl($url, $data = [], $method = 0) {
    $header[] = "Content-type: text/xml";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    if ($method == 1) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
    
function arrayToXml($data){
	if(!is_array($data) || count($data) <= 0){
		return false;
	}
	$xml = "<xml>";
	foreach ($data as $key=>$val){
		if (is_numeric($val)){
			$xml.="<".$key.">".$val."</".$key.">";
		}else{
			$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
		}
	}
	$xml.="</xml>";
	return $xml; 
}

    
    
function getSign($arr, $key) {
    ksort($arr);
    $stringA = '';
    foreach ($arr as $k => $v) {
        $stringA .= $k . '=' . $v . '&';
    }

    $stringA .= 'key=' . $key;
    $stringA = MD5($stringA);
    $stringA = strtoupper($stringA);
    return $stringA;
}
    
    
    