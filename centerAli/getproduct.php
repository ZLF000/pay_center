<?php

    require_once("../conn/conn.php");
    if ($_GET['mid']) {
        $sql="SELECT * FROM `sl_product` WHERE P_mid IN (" . $_GET['mid'] . ") AND P_del = 0 ORDER BY ABS(`P_price` - " . $_GET['amount'] . ") LIMIT 1;";
    } else {
        $sql="SELECT * FROM `sl_product` WHERE P_del = 0 ORDER BY ABS(`P_price` - " . $_GET['amount'] . ") LIMIT 1;";
    }
    
    $result = mysqli_query($conn, $sql);
    
    while ($row1 = mysqli_fetch_assoc($result)) {
        if($row1['P_selltype'] == 1){
            $sql = "SELECT * FROM `sl_csort` WHERE S_id = " . $row1['P_sell'] . " AND S_del = 0;";
            $result2 = mysqli_query($conn, $sql);
            $row2 = mysqli_fetch_assoc($result2);
            if($row2) {
                $sql = "SELECT count(*) as count FROM `sl_card` WHERE C_sort = " . $row2['S_id'] . " AND C_del = 0;";
                $result3 = mysqli_query($conn, $sql);
                $row3 = mysqli_fetch_assoc($result3);
                if($row3['count'] > 0){
                    echo json_encode([
                      'code' => 200, 
                      'pid' => $row1['P_id'], 
                      'amount' => $row1['P_price']
                    ]);
                    die();
                }
            }
        } else {
             echo json_encode([
                      'code' => 200, 
                      'pid' => $row1['P_id'], 
                      'amount' => $row1['P_price']
                    ]);
                    die();
        }
    }
    echo json_encode(['code'=> 400, 'msg' => '无可用产品']);
    die();
?>