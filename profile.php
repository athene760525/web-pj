<?php
// profile.php - 個人資料 / 密碼修改 / 管理員編輯/新增使用者 & 舍監協助重設密碼

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

require_login();

$me        = current_user();
$myAccount = $me['account'];
$myRole    = $me['identity'] ?? '';

$isAdmin   = ($myRole === '管理員');
$isManager = ($myRole === '舍監');

/**
 * 根據身份產生預設密碼
 * 住戶   → st + 帳號
 * 舍監   → th + 帳號
 */
function make_default_password(string $identity, string $account): string {
    switch ($identity) {
        case '住戶':
            return 'st' . $account;
        case '舍監':
            return 'th' . $account;
        default:
            return 'pwd' . $account;  // 萬一身份有其他值，就用這個備用
    }
}

/**
 * 使用者自己改密碼時要符合的規則
 */
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
    return null;
}

/* ====== 判斷模式：編輯 / 新增 ====== */

$mode          = $_GET['mode'] ?? 'edit';  // 'edit' or 'create'
$paramAccount  = $_GET['account'] ?? null;
$paramAccount  = $paramAccount !== null ? trim($paramAccount) : null;

$isCreate = ($mode === 'create');

// 只有「管理員」可以使用新增模式
if ($isCreate && !$isAdmin) {
    http_response_code(403);
    echo "只有管理員可以新增使用者。";
    exit;
}

// 編輯模式
if (!$isCreate) {
    $targetAccount = $paramAccount ?: $myAccount;  // 沒給 account → 看自己
    $isSelf        = ($targetAccount === $myAccount);

    // 若要編輯別人，必須是 管理員 或 舍監
    if (!$isSelf && !($isAdmin || $isManager)) {
        http_response_code(403);
        echo "您沒有權限變更此帳號的資料。";
        exit;
    }

    // 撈出目標使用者資料
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

} else {
    // 新增模式（只給管理員）
    $isSelf        = false;
    $targetAccount = null;
    $user = [
        'account'  => '',
        'name'     => '',
        'identity' => '住戶',
        'password' => '',
    ];
}

$msg     = '';
$variant = 'danger';

/* ====== 處理表單送出 ====== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 管理員：編輯使用者資料（可改姓名＋身份）
    if ($action === 'save_user' && $isAdmin && !$isCreate) {
        $newName     = trim($_POST['name'] ?? '');
        $newIdentity = trim($_POST['identity'] ?? '住戶');

        if ($newName === '') {
            $msg = '姓名不可空白。';
        } elseif (!in_array($newIdentity, ['管理員', '舍監', '住戶'], true)) {
            $msg = '身份不正確。';
        } else {
            $stmt = $conn->prepare("
                UPDATE users
                SET name = ?, identity = ?
                WHERE account = ?
            ");
            $stmt->bind_param('sss', $newName, $newIdentity, $user['account']);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                header("Location: " . BASE_URL . "/users.php?saved=1");
                exit;
            } else {
                $msg = '資料更新失敗，請稍後再試。';
            }
        }

    // 舍監：只能改姓名（身份不能更動）
    } elseif ($action === 'save_user' && $isManager && !$isCreate) {
        $newName = trim($_POST['name'] ?? '');

        if ($newName === '') {
            $msg = '姓名不可空白。';
        } else {
            $stmt = $conn->prepare("
                UPDATE users
                SET name = ?
                WHERE account = ?
            ");
            $stmt->bind_param('ss', $newName, $user['account']);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                header("Location: " . BASE_URL . "/users.php?saved=1");
                exit;
            } else {
                $msg = '資料更新失敗，請稍後再試。';
            }
        }

    // 管理員 / 舍監：重設密碼為預設值（依身份＋帳號產生）
    } elseif ($action === 'reset_default' && ($isAdmin || $isManager) && !$isCreate) {

        $defaultPassword = make_default_password($user['identity'], $user['account']);

        $stmt = $conn->prepare("
            UPDATE users
            SET password = ?
            WHERE account = ?
        ");
        $stmt->bind_param('ss', $defaultPassword, $user['account']);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            header("Location: " . BASE_URL . "/users.php?reset=success");
            exit;
        } else {
            $msg = '密碼重設失敗，請稍後再試。';
        }

    // 管理員：新增使用者（身份可選，預設密碼依規則產生）
    } elseif ($action === 'create_user' && $isAdmin && $isCreate) {
        $newAccount  = trim($_POST['account'] ?? '');
        $newName     = trim($_POST['name'] ?? '');
        $newIdentity = trim($_POST['identity'] ?? '住戶');

        if ($newAccount === '' || $newName === '') {
            $msg = '帳號與姓名不可空白。';
        } elseif (!in_array($newIdentity, ['管理員', '舍監', '住戶'], true)) {
            $msg = '身份不正確。';
        } else {
            // 檢查帳號是否存在
            $check = $conn->prepare("SELECT account FROM users WHERE account = ?");
            $check->bind_param('s', $newAccount);
            $check->execute();
            $exists = $check->get_result()->fetch_assoc();
            $check->close();

            if ($exists) {
                $msg = '此帳號已存在，請重新輸入。';
            } else {
                // 依身份＋帳號產生預設密碼
                $defaultPassword = make_default_password($newIdentity, $newAccount);

                $stmt = $conn->prepare("
                    INSERT INTO users (account, name, password, identity)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->bind_param('ssss', $newAccount, $newName, $defaultPassword, $newIdentity);
                $ok = $stmt->execute();
                $stmt->close();

                if ($ok) {
                    header("Location: " . BASE_URL . "/users.php?created=1");
                    exit;
                } else {
                    $msg = '新增使用者失敗，請稍後再試。';
                }
            }
        }

    // 一般使用者：修改自己的密碼（要符合規則）
    } elseif ($action === 'change_own_password' && $isSelf && !($isAdmin || $isManager)) {
        $oldPassword  = $_POST['old_password']  ?? '';
        $newPassword  = $_POST['new_password']  ?? '';
        $newPassword2 = $_POST['new_password2'] ?? '';

        if ($newPassword === '' || $newPassword2 === '') {
            $msg = '新密碼與確認新密碼欄位都必須填寫。';
        } elseif ($newPassword !== $newPassword2) {
            $msg = '兩次輸入的新密碼不一致。';
        } else {
            $ruleError = validate_password_rule($newPassword);
            if ($ruleError !== null) {
                $msg = $ruleError;
            } else {
                if ($oldPassword === '') {
                    $msg = '請輸入舊密碼。';
                } else {
                    $dbPassword = $user['password'];

                    if ($oldPassword !== $dbPassword) {
                        $msg = '舊密碼不正確。';
                    } else {
                        $stmt = $conn->prepare("
                            UPDATE users
                            SET password = ?
                            WHERE account = ?
                        ");
                        $stmt->bind_param('ss', $newPassword, $user['account']);
                        $ok = $stmt->execute();
                        $stmt->close();

                        if ($ok) {
                            $msg     = '密碼修改成功！';
                            $variant = 'success';
                        } else {
                            $msg = '密碼更新失敗，請稍後再試。';
                        }
                    }
                }
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container section py-4">
    <?php
    // 動態標題文字
    if ($isCreate) {
        $pageTitle   = '新增使用者';
        $pageSubtext = '由管理員為新使用者建立帳號，系統會依身份自動產生預設密碼。';
    } elseif (($isAdmin || $isManager) && !$isSelf) {
        $pageTitle   = '檢視 / 編輯使用者資料';
        $pageSubtext = '可調整學生基本資料，並依身份與帳號一鍵重設預設密碼。';
    } else {
        $pageTitle   = '個人資料與密碼設定';
        $pageSubtext = '檢視自己的基本資料，並依規則修改個人登入密碼。';
    }
    ?>

    <!-- 標題列 -->
    <div class="mb-3">
        <h1 class="section-title mb-1"><?= h($pageTitle) ?></h1>
        <p class="text-muted mb-0" style="font-size: 0.9rem;">
            <?= h($pageSubtext) ?>
        </p>
    </div>

    <!-- 訊息提示 -->
    <?php if ($msg): ?>
        <div class="alert alert-<?= h($variant) ?> mt-3">
            <?= h($msg) ?>
        </div>
    <?php endif; ?>

    <div class="feature-card mt-3">
        <?php if ($isCreate && $isAdmin): ?>
            <!-- 新增使用者表單（只有管理員） -->
            <form method="post">
                <input type="hidden" name="action" value="create_user">

                <div class="mb-3">
                    <label class="form-label">帳號（學號）</label>
                    <input type="text" name="account" class="form-control"
                           value="<?= h($user['account']) ?>" required>
                    <div class="form-text">帳號統一為學號或職員編號，建立後將作為登入帳號。</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">姓名</label>
                    <input type="text" name="name" class="form-control"
                           value="<?= h($user['name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">身份</label>
                    <select name="identity" class="form-select">
                        <option value="住戶"   <?= $user['identity'] === '住戶' ? 'selected' : '' ?>>住戶</option>
                        <option value="舍監"   <?= $user['identity'] === '舍監' ? 'selected' : '' ?>>舍監</option>
                        <option value="管理員" <?= $user['identity'] === '管理員' ? 'selected' : '' ?>>管理員</option>
                    </select>
                </div>

                <div class="alert alert-info py-2" style="font-size:0.85rem;">
                    新增後，系統會依 <strong>身份 + 帳號</strong> 自動設定預設密碼，例如：<br>
                    住戶：<code>st + 帳號</code>，舍監：<code>th + 帳號</code>。
                </div>

                <button type="submit" class="btn btn-main">建立使用者</button>
                <a href="<?= BASE_URL ?>/users.php" class="btn btn-secondary ms-2">返回列表</a>
            </form>

        <?php else: ?>
            <!-- 既有使用者資料（查看 / 編輯） -->
            <form method="post">
                <?php if (($isAdmin || $isManager) && !$isSelf): ?>
                    <input type="hidden" name="action" value="save_user">
                <?php endif; ?>

                <!-- 基本資料區 -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">帳號（學號）</label>
                        <input type="text" class="form-control"
                               value="<?= h($user['account']) ?>" disabled>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">姓名</label>
                        <?php if ($isAdmin || $isManager): ?>
                            <input type="text" name="name" class="form-control"
                                   value="<?= h($user['name']) ?>" required>
                        <?php else: ?>
                            <input type="text" class="form-control"
                                   value="<?= h($user['name']) ?>" disabled>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">身份</label>
                    <div class="row">
                        <div class="col-md-6">
                            <?php if ($isAdmin && !$isSelf): ?>
                                <select name="identity" class="form-select">
                                    <option value="住戶"   <?= $user['identity'] === '住戶' ? 'selected' : '' ?>>住戶</option>
                                    <option value="舍監"   <?= $user['identity'] === '舍監' ? 'selected' : '' ?>>舍監</option>
                                    <option value="管理員" <?= $user['identity'] === '管理員' ? 'selected' : '' ?>>管理員</option>
                                </select>
                            <?php else: ?>
                                <input type="text" class="form-control"
                                       value="<?= h($user['identity']) ?>" disabled>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (($isAdmin || $isManager) && !$isSelf): ?>
                    <!-- 管理員 / 舍監：管理他人帳號 -->
                    <hr class="my-3">

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <button type="submit" name="action" value="save_user" class="btn btn-main">
                            儲存資料
                        </button>

                        <button type="submit" name="action" value="reset_default"
                                class="btn btn-main"
                                onclick="return confirm('確定要將此使用者密碼重設為預設值嗎？');">
                            重設密碼為預設值
                        </button>

                        <a href="<?= BASE_URL ?>/users.php" class="btn btn-secondary ms-auto">
                            返回列表
                        </a>
                    </div>

                    <p class="text-muted mt-2" style="font-size:0.85rem;">
                        重設後的預設密碼會依目前身份與帳號自動產生，例如住戶：<code>st + 帳號</code>。
                    </p>

                <?php else: ?>
                    <!-- 一般使用者：修改自己的密碼 -->
                    <hr class="my-3">

                    <p class="text-muted mb-2" style="font-size:0.9rem;">
                        修改自己的密碼時，必須先輸入舊密碼。<br>
                        新密碼規則：至少 6 碼，僅能包含英文與數字，且必須同時包含英文字母與數字。
                    </p>

                    <input type="hidden" name="action" value="change_own_password">

                    <div class="mb-3">
                        <label class="form-label">舊密碼</label>
                        <input type="password" name="old_password" class="form-control">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">新密碼</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">再輸入一次新密碼</label>
                            <input type="password" name="new_password2" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-main">儲存密碼</button>
                    <a href="<?= BASE_URL ?>/index.php" class="btn btn-secondary ms-2">返回首頁</a>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
