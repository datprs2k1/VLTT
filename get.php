<?php
require_once('systems/bootstrap.php');

$ip = getIP();

$stmt = $pdo->prepare('SELECT `user_id`, `expired_at` FROM `licenses` WHERE `ip` = ?');

$stmt->execute([
    $ip
]);

$license = $stmt->fetch();

if ($license == null || !getStatus($license['expired_at'])) {
    echo json_encode([
        'status' => false,
        'message' => 'License hết hạn'
    ]);
} else {

    $re = '/\\\\n/m';

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $cauhoi = isset($_GET['cauhoi']) ? $_GET['cauhoi'] : '';
        $a = isset($_GET['A']) ?  preg_replace($re, "", $_GET['A']) : '';
        $b = isset($_GET['B']) ?  preg_replace($re, "", $_GET['B']) : '';
        $c = isset($_GET['C']) ?  preg_replace($re, "", $_GET['C']) : '';
        $d = isset($_GET['D']) ?  preg_replace($re, "", $_GET['D']) : '';


        $stmt = $pdo->prepare('SELECT * FROM `hoadang` WHERE MATCH(cauhoi) AGAINST(? IN NATURAL LANGUAGE MODE)');

        $stmt->execute([
            $cauhoi
        ]);

        $data = $stmt->fetchAll();

        $dapan = isset($data[0]['cautraloi']) ? $data[0]['cautraloi'] : "";

        $percents = 80;

        $sima = similar_text($dapan, $a, $perc);
        $simb = similar_text($dapan, $b, $perc);
        $simc = similar_text($dapan, $c, $perc);
        $simd = similar_text($dapan, $d, $perc);

        $max = max($sima, $simb, $simc, $simd);

        $select = 0;

        if ($sima == $max) {
            $select = 1;
        } else if ($simb == $max) {
            $select = 2;
        } else if ($simc == $max) {
            $select = 3;
        } else {
            $select = 4;
        }
        
        echo json_encode([
            'status' => true,
            'message' => $select
        ]);
    }
}
