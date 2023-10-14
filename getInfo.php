<?php
require_once('systems/bootstrap.php');

$ip = getIP();

$stmt = $pdo->prepare('SELECT `user_id`, `expired_at` FROM `licenses` WHERE `ip` = ?');

$stmt->execute([
    $ip
]);

$license = $stmt->fetch();


$stmt = $pdo->prepare('SELECT COUNT(`id`) as `total` FROM `users`');
$stmt->execute();
$total = $stmt->fetch()['total'];

if ($license == null || !getStatus($license['expired_at'])) {
    echo json_encode([
        'status' => false,
        'message' => 'License hết hạn'
    ]);
} else {
    $stmt = $pdo->prepare('SELECT `id`, `name` FROM `users` WHERE `id` = ?');
    $stmt->execute([
        $license['user_id']
    ]);

    $user = $stmt->fetch();

    echo json_encode([
        'status' => true,
        'message' => 'Hoạt động',
        'name' => $user['name'],
        'expired' => $license['expired_at'],
        'total' => $total
    ]);
}
