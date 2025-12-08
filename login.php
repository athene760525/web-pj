<?php
// login.php - 登入頁面

require_once __DIR__ . "/includes/config.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/db.php";

// 如果已登入，直接導回首頁或其它頁
// 你的專案有 current_user()，我們用它來判斷
if (current_user()) {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

// 處理登入提交
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $account  = trim($_POST["account"] ?? '');
    $password = trim($_POST["password"] ?? '');

    if ($account === '' || $password === '') {
        $errors[] = "帳號與密碼不可空白。";
    } else {
        // 用 SQL 從 users 資料表撈帳號
        $sql  = "SELECT account, name, password, identity FROM users WHERE account = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $errors[] = "系統錯誤，請稍後再試。";
        } else {
            $stmt->bind_param("s", $account);
            $stmt->execute();
            $result = $stmt->get_result();
            $user   = $result->fetch_assoc();

            if (!$user) {
                // 找不到帳號
                $errors[] = "帳號或密碼錯誤。";
            } else {
                // 目前你的資料表是存明碼（例如 st82st82）
                // 先用明碼比對，之後再改成 password_hash / password_verify
                if ($user["password"] !== $password) {
                    $errors[] = "帳號或密碼錯誤。";
                } else {
                    // 登入成功：設定 Session，讓 current_user() 可以讀到
                    // （auth.php 裡多半就是從 $_SESSION['user'] 取資料）
                    $_SESSION['user'] = [
                        'account'  => $user['account'],
                        'name'     => $user['name'],
                        'identity' => $user['identity'],
                    ];

                    // 登入後導回首頁或你想去的頁面
                    header("Location: " . BASE_URL . "/index.php");
                    exit;
                }
            }
        }
    }
}
?>

<?php include __DIR__ . "/includes/header.php"; ?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow" style="max-width: 420px; width: 100%;">
        <div class="card-header text-center">
            <h4 class="mb-0">宿舍管理系統登入</h4>
        </div>
        <div class="card-body">

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e): ?>
                        <div><?= htmlspecialchars($e) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="mb-3">
                    <label for="account" class="form-label">帳號 / 學號</label>
                    <input type="text"
                           class="form-control"
                           id="account"
                           name="account"
                           required
                           value="<?= htmlspecialchars($_POST["account"] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">密碼</label>
                    <input type="password"
                           class="form-control"
                           id="password"
                           name="password"
                           required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    登入
                </button>
            </form>
        </div>

        <div class="card-footer text-center text-muted" style="font-size: 0.9rem;">
            只有住宿相關人員可使用本系統
        </div>
    </div>
</div>

<?php include __DIR__ . "/includes/footer.php"; ?>
