<?php
    exec('cd /tmp && git clone git@github.com:ZLF000/pay_center.git 2>&1', $out);
    var_dump($out);