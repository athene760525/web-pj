<?php
// profile.php - 個人資料 / 密碼修改 / 舍監 & 管理員幫學生重設密碼

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

require_login();

$me = current_user();                 // 目前登入者（from session）
$myAccount = $me['account'];          // 目前登入者帳號

// 目標帳號：
// 若有給 ?account=... 且為舍監/管理員，則可以幫該帳號重設密碼
// 否則就只能操作自己的帳號
$targetAccount = $_GET['account'] ?? $myAccount;
$targetAccount = trim($targetAccount);

// 判斷是不是操作自己
$isSelf = ($targetAccount === $myAccount);

// 若要操作別人的帳號，必須是「管理員」或「舍監」
if (!$isSelf && !in_array($me['identity'], ['管理員', '舍監'], true)) {
    http_response_code(403);
    echo "您沒有權限變更此帳號的密碼。";
    exit;
}

// 從資料庫撈出目標帳號的使用者資料
$stmt = $conn->prepare("
    SELECT account, name, password, identity
    FROM users
    WHERE account = ?
");
$stmt->bind_param('s', $targetAccount);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    http_response_code(404);
    echo "找不到此帳號的使用者資料。";
    exit;
}

// 密碼規則檢查函式：至少 6 碼，只允許英文字母與數字，且需同時包含英文字母與數字
function validate_password_rule(string $pwd): ?string {
    if (strlen($pwd) < 6) {
        return '密碼長度至少須為 6 碼。';
    }
    if (!preg_match('/^[A-Za-z0-9]+$/', $pwd)) {
        return '密碼僅能由英文與數字組成。';
    }
    if (!preg_match('/[A-Za-z]/', $pwd)) {
        return '密碼至少需包含一個英文字母。';
    }
    if (!preg_match('/\d/', $pwd)) {
        return '密碼至少需包含一個數字。';
    }
    return null; // 通過
}

$msg = '';
$variant = 'danger';

// 處理表單送出
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword  = $_POST['old_password']  ?? '';
    $newPassword  = $_POST['new_password']  ?? '';
    $newPassword2 = $_POST['new_password2'] ?? '';

    if ($newPassword === '' || $newPassword2 === '') {
        $msg = '新密碼與確認新密碼欄位都必須填寫。';
    } elseif ($newPassword !== $newPassword2) {
        $msg = '兩次輸入的新密碼不一致。';
    } else {
        // 套用密碼規則檢查
        $ruleError = validate_password_rule($newPassword);
        if ($ruleError !== null) {
            $msg = $ruleError;
        } else {
            if ($isSelf) {
                // 自己改自己的密碼：需檢查舊密碼
                if ($oldPassword === '') {
                    $msg = '請輸入舊密碼。';
                } else {
                    $dbPassword = $user['password']; // 目前資料庫中的密碼（明碼）

                    // ※ 若未來使用 password_hash，這裡要改為 password_verify()
                    if ($oldPassword !== $dbPassword) {
                        $msg = '舊密碼不正確。';
                    } else {
                        // 舊密碼正確 → 更新新密碼
                        $stmt = $conn->prepare("
                            UPDATE users
                            SET password = ?
                            WHERE account = ?
                        ");
                        $stmt->bind_param('ss', $newPassword, $targetAccount);
                        $ok = $stmt->execute();
                        $stmt->close();

                        if ($ok) {
                            $msg = '密碼修改成功！';
                            $variant = 'success';
                        } else {
                            $msg = '密碼更新失敗，請稍後再試或洽系統管理員。';
                        }
                    }
                }
            } else {
                // 舍監 / 管理員幫學生重設密碼：不需舊密碼
                $stmt = $conn->prepare("
                    UPDATE users
                    SET password = ?
                    WHERE account = ?
                ");
                $stmt->bind_param('ss', $newPassword, $targetAccount);
                $ok = $stmt->execute();
                $stmt->close();

                if ($ok) {
                    $msg = '已成功重設該學生的密碼。';
                    $variant = 'success';
                } else {
                    $msg = '密碼更新失敗，請稍後再試或洽系統管理員。';
                }
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container mt-4" style="max-width: 600px;">
    <h3 class="mb-3">
        <?php if ($isSelf): ?>
            個人資料與密碼設定
        <?php else: ?>
            幫學生重設密碼
        <?php endif; ?>
    </h3>

    <?php if ($msg): ?>
        <div class="alert alert-<?= h($variant) ?>">
            <?= h($msg) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <!-- 帳號：顯示但不能修改 -->
        <div class="mb-3">
            <label class="form-label">帳號（學號）</label>
            <input type="text" class="form-control"
                   value="<?= h($user['account']) ?>" disabled>
        </div>

        <!-- 姓名：顯示但不能修改 -->
        <div class="mb-3">
            <label class="form-label">姓名</label>
            <input type="text" class="form-control"
                   value="<?= h($user['name']) ?>" disabled>
        </div>

        <!-- 身分：顯示但不能修改 -->
        <div class="mb-3">
            <label class="form-label">身份</label>
            <input type="text" class="form-control"
                   value="<?= h($user['identity']) ?>" disabled>
        </div>

        <hr>

        <?php if ($isSelf): ?>
            <p class="text-muted mb-2">
                修改自己的密碼時，必須先輸入舊密碼。<br>
                新密碼規則：至少 6 碼，僅能包含英文與數字，且必須同時包含英文字母與數字。
            </p>

            <!-- 自己改密碼：需要舊密碼 -->
            <div class="mb-3">
                <label class="form-label">舊密碼</label>
                <input type="password" name="old_password" class="form-control">
            </div>
        <?php else: ?>
            <p class="text-muted mb-2">
                您目前以「<?= h($me['identity']) ?>」身分登入，可幫此學生重設密碼。<br>
                不需輸入舊密碼，但請依照密碼規則設定新密碼。
            </p>
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label">新密碼</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">再輸入一次新密碼</label>
            <input type="password" name="new_password2" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">
            儲存密碼
        </button>

        <!-- 這裡不分身份都顯示返回按鈕，只是文字不同 -->
        <?php if ($isSelf): ?>
            <a href="<?= BASE_URL ?>/index.php" class="btn btn-secondary ms-2">返回首頁</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/household.php" class="btn btn-secondary ms-2">返回住戶列表</a>
        <?php endif; ?>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
