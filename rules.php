<?php
// penalty.php - 違規規則管理（PDO 版本）

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
require_role(['管理員', '舍監']);

// ===== PDO 連線 =====
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=room;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die("資料庫連線失敗：" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

$msg   = $_GET['msg'] ?? '';
$error = '';

// 表單保留值
$formArticle = trim($_POST['article_no'] ?? '');
$formContent = trim($_POST['content'] ?? '');
$formPoints  = $_POST['points'] ?? '';

// =======================
// 新增規則
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {

    $article_no = trim($_POST['article_no'] ?? '');
    $content    = trim($_POST['content'] ?? '');
    $points     = (int)($_POST['points'] ?? 0);

    if ($article_no === '' || $content === '' || $points <= 0) {
        $error = '請輸入「條例」、「規則內容」，且扣點需大於 0。';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO penalty (article_no, content, points) VALUES (?, ?, ?)"
        );
        $stmt->execute([$article_no, $content, $points]);
        header("Location: " . BASE_URL . "/rules.php?msg=created");
        exit;
    }
}

// =======================
// 編輯規則
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {

    $id         = (int)($_POST['id'] ?? 0);
    $article_no = trim($_POST['article_no'] ?? '');
    $content    = trim($_POST['content'] ?? '');
    $points     = (int)($_POST['points'] ?? 0);

    if ($id <= 0) {
        $error = '缺少規則 ID。';
    } elseif ($article_no === '' || $content === '' || $points <= 0) {
        $error = '請輸入完整資料，且扣點需大於 0。';
    } else {
        $stmt = $pdo->prepare(
            "UPDATE penalty SET article_no = ?, content = ?, points = ? WHERE id = ?"
        );
        $stmt->execute([$article_no, $content, $points, $id]);
        header("Location: " . BASE_URL . "/rules.php?msg=updated");
        exit;
    }
}

// =======================
// 刪除規則（若已被使用禁止）
// =======================
if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];
    if ($id <= 0) {
        header("Location: " . BASE_URL . "/rules.php?msg=error");
        exit;
    }

    // 取得 article_no
    $stmt = $pdo->prepare("SELECT article_no FROM penalty WHERE id = ?");
    $stmt->execute([$id]);
    $rule = $stmt->fetch();

    if (!$rule) {
        header("Location: " . BASE_URL . "/rules.php?msg=not_found");
        exit;
    }

    // 檢查 violation 是否使用過
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM violation WHERE article_no = ?"
    );
    $stmt->execute([$rule['article_no']]);
    $used = (int)$stmt->fetchColumn();

    if ($used > 0) {
        header("Location: " . BASE_URL . "/rules.php?msg=used");
        exit;
    }

    // 可刪除
    $stmt = $pdo->prepare("DELETE FROM penalty WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: " . BASE_URL . "/rules.php?msg=deleted");
    exit;
}

// =======================
// 撈 penalty 清單
// =======================
$stmt  = $pdo->query(
    "SELECT id, article_no, content, points FROM penalty ORDER BY id ASC"
);
$rules = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="container my-4">
    <h2 class="mb-3">違規規則管理</h2>

    <?php if ($msg === 'created'): ?>
        <div class="alert alert-success">✅ 新增規則成功</div>
    <?php elseif ($msg === 'updated'): ?>
        <div class="alert alert-success">✅ 規則已更新</div>
    <?php elseif ($msg === 'deleted'): ?>
        <div class="alert alert-success">✅ 規則已刪除</div>
    <?php elseif ($msg === 'used'): ?>
        <div class="alert alert-danger">❌ 此規則已被違規紀錄使用，禁止刪除</div>
    <?php elseif ($msg === 'not_found'): ?>
        <div class="alert alert-danger">❌ 找不到該規則</div>
    <?php elseif ($msg === 'error'): ?>
        <div class="alert alert-danger">❌ 操作失敗</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>

    <!-- 新增規則 -->
    <div class="card mb-4">
        <div class="card-header">新增違規規則</div>
        <div class="card-body">
            <form method="post" class="row g-2">
                <input type="hidden" name="action" value="create">

                <div class="col-md-3">
                    <input type="text"
                           name="article_no"
                           class="form-control"
                           placeholder="條例（例：第十一條）"
                           value="<?= h($formArticle) ?>">
                </div>

                <div class="col-md-6">
                    <input type="text"
                           name="content"
                           class="form-control"
                           placeholder="規則內容"
                           value="<?= h($formContent) ?>">
                </div>

                <div class="col-md-1">
                    <input type="number"
                           name="points"
                           class="form-control"
                           min="1"
                           placeholder="點數"
                           value="<?= h((string)$formPoints) ?>">
                </div>

                <div class="col-md-2">
                    <button class="btn btn-danger w-100">新增</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 規則列表 -->
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>條例</th>
            <th>規則內容</th>
            <th>扣點</th>
            <th style="width:200px;">操作</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($rules)): ?>
            <tr>
                <td colspan="5" class="text-center text-muted py-4">目前沒有規則</td>
            </tr>
        <?php else: ?>
            <?php foreach ($rules as $r): ?>
                <tr>
                    <form method="post">
                        <td><?= (int)$r['id'] ?></td>

                        <td>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <input type="text" name="article_no" class="form-control"
                                   value="<?= h($r['article_no']) ?>">
                        </td>

                        <td>
                            <input type="text" name="content" class="form-control"
                                   value="<?= h($r['content']) ?>">
                        </td>

                        <td>
                            <input type="number" name="points" class="form-control"
                                   min="1" value="<?= (int)$r['points'] ?>">
                        </td>

                        <td>
                            <button class="btn btn-sm btn-warning me-1">更新</button>
                            <a class="btn btn-sm btn-outline-danger"
                               href="<?= BASE_URL ?>/rules.php?delete=<?= (int)$r['id'] ?>"
                               onclick="return confirm('確定要刪除此規則？');">
                                刪除
                            </a>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
