<?php
#部署代码用!
$shell = "pwd && git pull 2>&1";
exec($shell,$out);
echo $project."<br/>";
print_r($out);
?>