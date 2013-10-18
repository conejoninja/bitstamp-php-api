<?php
require_once 'BitStampAPI.php';

$bapi = new BitStampAPI('key', 'secret', 'client id');
$ticker = $bapi->ticker();
print_r($ticker);

?>