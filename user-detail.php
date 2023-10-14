<?php

$title = 'Chi tiết người dùng';

require_once('layouts/header.php');

if (!isset($_SESSION['id'])) {
    header('Location: ' . SITE_URL . '/login.php');
}

if ($_SESSION['role'] != '1') {
    header('Location: ' . SITE_URL . '/index.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $id = $_SESSION['id'];

    $stmt = $pdo->prepare('SELECT `id`, `name`, `email`, `password`, `role` FROM `users` WHERE `id` = ?');
    $stmt->execute([$id]);

    $user = $stmt->fetch();

    $stmt = $pdo->prepare('SELECT `id`, `ip`, `note`, `expired_at` FROM `licenses` WHERE `user_id` = ?');
    $stmt->execute([$id]);

    $licenses = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['type']) ? $_POST['type'] : '';
    $id = isset($_POST['id']) ? $_POST['id'] : '';

    if ($action == 'edit') {
        $loai = isset($_POST['loai']) ? $_POST['loai'] : '';

        $stmt = $pdo->prepare('SELECT `expired_at` FROM `licenses` WHERE `id` = ?');
        $stmt->execute([$id]);

        $license = $stmt->fetch();

        if ($loai == 1) {
            $expire = date('Y-m-d H:i:s', strtotime($license['expired_at'] . '+1 day'));
        } else if ($loai == 2) {
            $expire = date('Y-m-d H:i:s', strtotime($license['expired_at'] . '+1 week'));
        } else {
            $expire = date('Y-m-d H:i:s', strtotime($license['expired_at'] . '+1 month'));
        }


        $stmt = $pdo->prepare('UPDATE `licenses` SET `expired_at` = ? WHERE `id` = ?');
        $stmt->execute([$expire, $id]);

        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        $stmt = $pdo->prepare('DELETE FROM `licenses` WHERE `id` = ?');
        $stmt->execute([$id]);

        header('Location: ' . $_SERVER['HTTP_REFERER']);
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
        <h1 class="text-2xl text-center mb-3">Danh sách license của <?= $user['name'] ?></h1>
        <a href="/add-license.php?id=<?= $user['id'] ?>" type="button" data-id="10" class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">Thêm license</a>

        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 mt-5">
                <thead class="text-lg text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            STT
                        </th>
                        <th scope="col" class="px-6 py-3">
                            IP
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Note
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Trạng thái
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Ngày hết hạn
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Hành động
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($licenses as $key => $license) :
                    ?>
                        <tr class="bg-white border-b text-lg">
                            <td class="px-6 py-4">
                                <?= $key + 1 ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= $license['ip'] ?>
                            </td>
                             <td class="px-6 py-4">
                                <?= $license['note'] ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= getStatus($license['expired_at']) ? '<span class="bg-green-100 text-green-800 text-lg font-medium mr-2 px-2.5 py-0.5 rounded border border-green-400">Hoạt động</span>'
                                    :
                                    '<span class="bg-red-100 text-red-800 text-lg font-medium mr-2 px-2.5 py-0.5 rounded border border-red-400">Hết hạn</span>' ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= $license['expired_at'] ?>
                            </td>
                            <td>
                                <button type="button" data-id="<?= $license['id'] ?>" class="btn-edit inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">Gia hạn</button>
                                <button type="button" data-id="<?= $license['id'] ?>" class="btn-delete inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Xoá</button>
                            </td>
                        </tr>
                    <?php
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="relative z-10 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true" id="modal">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Gia hạn</h3>
                            <div class="mt-2">
                                <form method="POST" id="form">
                                    <input type="hidden" name="id" id="license">
                                    <input type="hidden" name="type" value="edit">
                                    <div class="my-2">
                                        <label for="loai">Loại</label>
                                        <select id="loai" name="loai" class="border-[1px] border-slate-200 rounded-md p-2 w-full mt-2">
                                            <option value="1">1 ngày</option>
                                            <option value="2">1 tuần</option>
                                            <option value="3">1 tháng</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button id="submit" type="button" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Gia hạn</button>
                    <button id="cancel" type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Hủy</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="relative z-10 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true" id="delete-modal">

    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Xoá</h3>
                            <form method="POST" id="form-delete">
                                <input type="hidden" name="type" value="delete">
                                <input type="hidden" name="id" id="license-delete">
                            </form>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Bạn có chắc chắn muốn xoá?</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button id="delete" type="button" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Xoá</button>
                    <button id="cancel-delete" type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Huỷ</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#cancel").on("click", function() {
            $("#modal").addClass("hidden");
        });

        $("#cancel-delete").on("click", function() {
            $("#delete-modal").addClass("hidden");
        });

        $(".btn-edit").on("click", function() {
            var id = $(this).data("id");
            $("#license").val(id);
            $("#modal").removeClass("hidden");
        });

        $(".btn-delete").on("click", function() {
            var id = $(this).data("id");
            $("#license-delete").val(id);
            $("#delete-modal").removeClass("hidden");
        });

        $("#submit").on("click", function() {
            $("#form").submit();
            $("#modal").addClass("hidden");
        });

        $("#delete").on("click", function() {
            $("#form-delete").submit();
            $("#delete-modal").addClass("hidden");
        });
    });
</script>

<?php require_once('layouts/footer.php'); ?>