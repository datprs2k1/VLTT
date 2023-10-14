<?php

$title = "Thêm license";

require_once('layouts/header.php');

if (!isset($_SESSION['id'])) {
    header('Location: ' . SITE_URL . '/login.php');
}

if ($_SESSION['role'] != '1') {
    header('Location: ' . SITE_URL . '/index.php');
}

$notif = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $id = $_SESSION['id'];
    $stmt = $pdo->prepare('SELECT `id`, `name` FROM `users` WHERE id = ?');
    $stmt->execute([$id]);

    $user = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_SESSION['id'];
    $note = isset($_POST['note']) ? $_POST['note'] : '';
    $loai = isset($_POST['loai']) ? $_POST['loai'] : '';

    if ($loai == 1) {
        $expire = date('Y-m-d H:i:s', strtotime('+1 day'));
    } else if ($loai == 2) {
        $expire = date('Y-m-d H:i:s', strtotime('+1 week'));
    } else {
        $expire = date('Y-m-d H:i:s', strtotime('+1 month'));
    }

    $stmt = $pdo->prepare('INSERT INTO `licenses` (`id`, `note`, `user_id`, `expired_at`) VALUES (?, ?, ?, ?)');
    $result = $stmt->execute([
        uniqid(),
        $note,
        $id,
        $expire,
    ]);

    if (!$result) {
        $notif = 'Tài khoản hoặc mật khẩu không chính xác';
    } else {
        header('location: /user-detail.php?id=' . $id);
    }
}

?>

<div class="relative mb-20 md:mb-40">
    <div class="flex justify-center h-[100px]">
        <img src="<?= SITE_URL . '/assets/covers/default.jpg' ?>" class="w-full md:w-2/3 rounded-b-xl object-cover" style="object-position: 50%" id="preview-cover" />
    </div>
    <div class="absolute left-[5%] md:left-[20%] bottom-[-4.5rem] md:bottom-[-8rem] w-[95%] md:w-[80%]">
        <div class="flex items-center space-x-3">
            <img src="<?= SITE_URL . '/assets/avatars/default.jpg' ?>" class="bg-white rounded-full min-w-[110px] h-[110px] md:w-1/6 md:h-[215px] p-1 object-cover object-center" id="preview-avatar" />

            <div class="w-full mt-10">
                <span class="text-sm md:text-lg font-bold">ABC</span>
            </div>
        </div>
    </div>
</div>

<div class="flex justify-center">
    <div class="bg-white mx-4 p-3 space-y-2 text-md text-slate-700 rounded-md w-full md:w-2/3">
        <h1 class="text-2xl text-center">Thêm license</h1>
        <div class="mt-2">
            <div class="flex flex-col justify-center items-center">
                <div class="bg-white w-5/6 md:w-1/4 rounded-md p-2">
                    <form method="POST" id="form">
                        <span class="text-xs text-red-500"><?= ($notif != '' ? $notif : '') ?></span>
                        <input type="hidden" name="id" id="id" value="<?= $user['id'] ?>">
                        <div class="my-2">
                            <div class="my-2">
                                <label for="user">Người dùng</label>
                                <input id="user" value="<?= $user['name'] ?>" type="text" class="border-[1px] border-slate-200 rounded-md p-2 w-full mt-2" disabled />
                            </div>
                        </div>
                        <div class="my-2">
                    <label for="note">Note</label>
                    <input type="text" id="note" name="note" class="border-[1px] border-slate-200 rounded-md p-2 w-full mt-2" />
                </div>
                        <div class="my-2">
                            <label for="loai">Loại</label>
                            <select id="loai" name="loai" class="border-[1px] border-slate-200 rounded-md p-2 w-full mt-2">
                                <option value="1">1 ngày</option>
                                <option value="2">1 tuần</option>
                                <option value="3">1 tháng</option>
                            </select>
                        </div>
                        <div class="flex justify-center mt-5">
                            <button type="submit" id="loginBtn" class="bg-blue-400 text-white px-4 py-2 rounded">Thêm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<?php require_once('layouts/footer.php'); ?>