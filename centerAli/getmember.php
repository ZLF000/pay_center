<?php 
     require_once("../../conn/conn.php");
     $sql = 'SELECT M_id,M_login FROM `sl_member` WHERE M_del=0;';
     $result = mysqli_query($conn, $sql);
     $members = [];
     while ($row = mysqli_fetch_row($result)) {
        $members[] = $row;
    }
    echo json_encode($members);
    die();
?>