<?php
$file = fopen("test.txt","w");

  $subId = isset($_REQUEST['subId']) ? $_REQUEST['subId'] : null;
  $transId = isset($_REQUEST['transId']) ? $_REQUEST['transId'] : null;
  $reward = isset($_REQUEST['reward']) ? $_REQUEST['reward'] : null;
  $signature = isset($_REQUEST['signature']) ? $_REQUEST['signature'] : null;

fwrite($file, "$subId|$transId|$reward|$signature");
?>