<?php

$title = "Đăng nhập";

require_once('systems/bootstrap.php');

$notif = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $hashedPassword = md5(md5($password));

    $stmt = $pdo->prepare('SELECT `id`, `name`, `email`, `password`, `role` FROM `users` WHERE (`email` = ?) AND `password` = ?');
    $stmt->execute([
        $email,
        $hashedPassword
    ]);

    $user = $stmt->fetch();

    if (!$user) {
        $notif = 'Tài khoản hoặc mật khẩu không chính xác';
    } else {
        $_SESSION['id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        header('location: /');
    }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <title>AUTO Login</title>

    <link rel="shortcut icon" href="<?= SITE_URL ?>/assets/images/favicon.png?v=<?= VERSION ?>" />
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/styles/app.css?v=<?= VERSION ?>" />
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.15.4/css/all.css" />
    <script type="text/javascript" src="https://cdn.tailwindcss.com"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins&display=swap');

        * {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-100">
    <div class="flex flex-col justify-center mt-[30%] md:mt-[10%] items-center">
        <div class="flex justify-center mb-7">
            <img src="<?= SITE_URL ?>/assets/images/favicon.png" class="w-1/6" />
        </div>
        <div class="bg-white w-5/6 md:w-1/4 rounded-md p-2">
            <form method="POST">
                <span class="text-xs text-red-500"><?= ($notif != '' ? $notif : '') ?></span>
                <div class="my-2">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="border-[1px] border-slate-200 rounded-md p-2 w-full mt-2" />
                </div>
                <div class="my-2">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" class="border-[1px] border-slate-200 rounded-md p-2 w-full mt-2" />
                </div>
                <div class="flex justify-center mt-5">
                    <button type="submit" id="loginBtn" class="bg-blue-400 text-white px-4 py-2 rounded">Đăng Nhập</button>
                </div>
            </form>

        </div>
    </div>
</body>

</html>