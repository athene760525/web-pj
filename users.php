<?php
// users.php - 住戶資料管理頁（管理員 / 舍監）

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// 必須登入 & 身份檢查：只有「管理員 / 舍監」可以看
require_login();
require_role(['管理員', '舍監']);

$me      = current_user();
$myRole  = $me['identity'] ?? '';
$isAdmin = ($myRole === '管理員');

/* ====== 搜尋功能 ====== */

// 取得搜尋字串 (?q=)
$q = $_GET['q'] ?? '';

// 使用 functions.php 內的 build_user_search() 產生 WHERE / params / types
[$where, $params, $types] = build_user_search($q);

/* ====== 撈資料，不做排序 ====== */

$sql = "
    SELECT account, name, identity
    FROM users
    $where
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('SQL 錯誤：' . $conn->error);
}

if ($types !== '' && !empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container section py-4">
    <!-- 標題列 -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <div>
            <h1 class="section-title mb-1">住戶資料管理</h1>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                管理員可新增使用者與調整身份，舍監可協助修改姓名與重設密碼。
            </p>
        </div>

        <?php if ($isAdmin): ?>
            <div class="mt-3 mt-md-0">
                <!-- 只有管理員看得到「新增使用者」 -->
                <a href="<?= BASE_URL ?>/profile.php?mode=create" class="btn btn-main">
                    ＋ 新增使用者
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- 系統訊息提示區 -->
    <?php if (isset($_GET['reset']) && $_GET['reset'] === 'success'): ?>
        <div class="alert alert-success alert-sm">
            密碼已依身份與帳號規則，重設為預設值。
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['saved']) && $_GET['saved'] === '1'): ?>
        <div class="alert alert-success alert-sm">
            使用者資料已成功儲存。
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['created']) && $_GET['created'] === '1'): ?>
        <div class="alert alert-success alert-sm">
            新使用者已建立，預設密碼已依身份與帳號自動產生。
        </div>
    <?php endif; ?>

    <!-- 搜尋列 -->
    <div class="mb-3">
        <form class="row g-2 align-items-center" method="get">
            <div class="col-sm-6 col-md-4">
                <input type="text"
                       name="q"
                       class="form-control"
                       placeholder="輸入帳號或姓名搜尋"
                       value="<?= h($q) ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-main">
                    搜尋
                </button>
            </div>
            <?php if ($q !== ''): ?>
                <div class="col-auto">
                    <a href="<?= BASE_URL ?>/users.php" class="btn btn-link text-muted" style="font-size:0.9rem;">
                        清除搜尋
                    </a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- 資料卡片區塊 -->
    <div class="feature-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div style="font-size:0.9rem; color:#5d667c;">
                共 <?= (int)$result->num_rows ?> 筆資料
                <?php if ($q !== ''): ?>
                    ，搜尋關鍵字：<span class="fw-semibold"><?= h($q) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th style="width:20%;">帳號</th>
                    <th style="width:25%;">姓名</th>
                    <th style="width:20%;">身份</th>
                    <th style="width:35%;">管理操作</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-semibold">
                                <?= h($row['account']) ?>
                            </td>
                            <td>
                                <?= h($row['name']) ?>
                            </td>
                            <td>
                                <?php if ($row['identity'] === '管理員'): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        管理員
                                    </span>
                                <?php elseif ($row['identity'] === '舍監'): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        舍監
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                        住戶
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- 檢視 / 編輯 / 重設密碼：共用 profile.php -->
                                <a href="<?= BASE_URL ?>/profile.php?account=<?= urlencode($row['account']) ?>"
                                   class="btn btn-sm btn-outline-primary me-1">
                                    檢視 / 編輯
                                </a>

                                <?php if ($isAdmin || $myRole === '舍監'): ?>
                                    <!-- 進入 profile.php 後才按「重設密碼為預設值」，這裡只是說明 -->
                                    <span class="text-muted" style="font-size:0.8rem;">
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            目前沒有符合條件的使用者資料。
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

