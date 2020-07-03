<?php
require_once("../conn/conn.php");

if ($_GET['mid']) {
    $sql = "SELECT P_price FROM `sl_product` WHERE P_mid IN (" . $_GET['mid'] . ") AND P_del = 0 GROUP BY P_price ORDER BY ABS(`P_price` - " . $_GET['amount'] . ")";
} else {
    $sql="SELECT P_price FROM `sl_product` WHERE P_del = 0 GROUP BY P_price ORDER BY ABS(`P_price` - " . $_GET['amount'] . ");";
}

$result1 = mysqli_query($conn, $sql);

while ($price = mysqli_fetch_assoc($result1)) {
    if($_GET['mid']) {
        $sql = "SELECT * FROM `sl_product` WHERE P_mid IN (" . $_GET['mid'] . ") AND P_del = 0 AND P_price = " . $price['P_price'];
    } else {
        $sql = "SELECT * FROM `sl_product` WHERE P_del = 0 AND P_price = " . $price['P_price'];
    }

    $products = mysqli_query($conn, $sql);
    $proArr = [];
    while($row = mysqli_fetch_assoc($products)) {
        $proArr[] = $row;
    }
    shuffle($proArr);
    foreach ($proArr as $v) {
        if($v['P_selltype'] == 1){
            $sql = "SELECT * FROM `sl_csort` WHERE S_id = " . $v['P_sell'] . " AND S_del = 0;";
            $result2 = mysqli_query($conn, $sql);
            $row2 = mysqli_fetch_assoc($result2);
            if($row2) {
                $sql = "SELECT count(*) as count FROM `sl_card` WHERE C_sort = " . $row2['S_id'] . " AND C_del = 0;";
                $result3 = mysqli_query($conn, $sql);
                $row3 = mysqli_fetch_assoc($result3);
                if($row3['count'] > 0){
                    echo json_encode([
                        'code' => 200,
                        'pid' => $v['P_id'],
                        'amount' => $v['P_price']
                    ]);
                    die();
                }
            }
        }   else {
            echo json_encode([
                'code' => 200,
                'pid' => $v['P_id'],
                'amount' => $v['P_price']
            ]);
            die();
        }
    }
}

echo json_encode(['code'=> 400, 'msg' => '无可用产品']);
die();
?>