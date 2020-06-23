<?php
    require_once("../conn/conn.php");
    $mid = 1;  
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>发货内容</title>
    <script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
</head>
<body>
<form action="alipayapi.php" method="post" class="buy" id="test">
    <p><button class="btn btn-info" type="submit">立即支付</button></p>
    <input type="hidden" value="<?php echo $_GET['pid'];?>" name="id">
    <input type="hidden" value="<?php echo $_GET['email'];?>" name="email">
    <input type="hidden" name="M_id" value="<?php echo $mid; ?>">
    <input type="hidden" value="product" name="type">
    <input type="hidden" value="<?php echo $_GET['order_id'];?>" name="order_id">
    <input type="number" name="num" value="1" class="form-control" id="amount" min="1" max="42">
</form>
</body>
<script>
    $('#test').submit();
</script>
</html>