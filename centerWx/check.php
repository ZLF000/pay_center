<?php
require_once("../../conn/conn.php");
$genkey = $_GET['genkey'];
$redirect = isset($_GET['url']) ? $_GET['url'] : '';

$sql = "select * from sl_orders where O_genkey = '" . $genkey . "'";
$res = mysqli_query($conn, $sql);
if (mysqli_num_rows($res) > 0) {
    if($redirect != '') {
        Header("Location: $redirect");
    } else {
        echo '订单完成';
    }
} else {

    die;
}