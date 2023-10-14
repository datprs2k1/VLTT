<?php
$subId = "23676f3e762f824d65ca5e837acb666b";
$transId = "TEST_988446";
$reward = "10.00";
$secret_key = "93d6ff44b3009feae1704376ef7949a6";
echo md5($subId . $transId . $reward . $secret_key);