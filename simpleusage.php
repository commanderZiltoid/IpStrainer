<?php

require('IpStrainer.php');

$strainer = new IpStrainer($_SERVER['REMOTE_ADDR']);
if(!$strainer->verifyIP()){
    echo 'IP has been blocked due to rapid requests';
    die;
}
echo 'IP valid...continue';