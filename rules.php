<?php
// rules.php - 違規規則管理（PDO 版本）
// 路徑：專案根目錄 /rules.php

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
require_role(['管理員', '舍監']); // 只有管理員/舍監可進

// ===== PDO 連線（獨立用 PDO，不用你原本的 mysqli）=====
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

// 讓新增表單失敗時可保留輸入
$formContent = trim($_POST['content'] ?? '');
$formPoints  = $_POST['points'] ?? '';

// ===== 新增規則 =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $content = trim($_POST['content'] ?? '');
    $points  = (int)($_POST['points'] ?? 0);

    if ($content === '' || $points <= 0) {
        $error = '請輸入「規則內容」且扣點需大於 0。';
    } else {
        $stmt = $pdo->prepare("INSERT INTO rules (content, points) VALUES (?, ?)");
        $stmt->execute([$content, $points]);
        header("Location: " . BASE_URL . "/rules.php?msg=created");
        exit;
    }
}

// ===== 編輯規則 =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id      = (int)($_POST['id'] ?? 0);
    $content = trim($_POST['content'] ?? '');
    $points  = (int)($_POST['points'] ?? 0);

    if ($id <= 0) {
        $error = '缺少規則 ID。';
    } elseif ($content === '' || $points <= 0) {
        $error = '請輸入「規則內容」且扣點需大於 0。';
    } else {
        $stmt = $pdo->prepare("UPDATE rules SET content = ?, points = ? WHERE id = ?");
        $stmt->execute([$content, $points, $id]);
        header("Location: " . BASE_URL . "/rules.php?msg=updated");
        exit;
    }
}

// ===== 刪除規則（有被使用就禁止）=====
// 你目前 violation 表沒有 rule_id，所以只能用 content 去判斷是否被使用（先用 rules.content 比對 violation.content）
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    if ($id <= 0) {
        header("Location: " . BASE_URL . "/rules.php?msg=error");
        exit;
    }

    // 先取出該 rule 的 content
    $stmt = $pdo->prepare("SELECT content FROM rules WHERE id = ?");
    $stmt->execute([$id]);
    $rule = $stmt->fetch();

    if (!$rule) {
        header("Location: " . BASE_URL . "/rules.php?msg=not_found");
        exit;
    }

    // 檢查 violation 是否使用過這個 content
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM violation WHERE content = ?");
    $stmt->execute([$rule['content']]);
    $used = (int)$stmt->fetchColumn();

    if ($used > 0) {
        header("Location: " . BASE_URL . "/rules.php?msg=used");
        exit;
    }

    // 未被使用才可刪
    $stmt = $pdo->prepare("DELETE FROM rules WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: " . BASE_URL . "/rules.php?msg=deleted");
    exit;
}

// ===== 撈規則列表 =====
$stmt  = $pdo->query("SELECT id, content, points FROM rules ORDER BY id ASC");
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

                <div class="col-md-8">
                    <input type="text"
                           name="content"
                           class="form-control"
                           placeholder="規則內容"
                           value="<?= h($formContent) ?>">
                </div>

                <div class="col-md-2">
                    <input type="number"
                           name="points"
                           class="form-control"
                           placeholder="扣點"
                           min="1"
                           value="<?= h((string)$formPoints) ?>">
                </div>

                <div class="col-md-2">
                    <button class="btn btn-danger w-100">新增</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 規則列表 -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
            <tr>
                <th style="width:70px;">ID</th>
                <th>規則內容</th>
                <th style="width:120px;">扣點</th>
                <th style="width:220px;">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($rules)): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">目前沒有規則</td>
                </tr>
            <?php else: ?>
                <?php foreach ($rules as $r): ?>
                    <tr>
                        <td><?= (int)$r['id'] ?></td>

                        <td>
                            <form method="post" class="d-flex gap-2">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">

                                <input type="text"
                                       name="content"
                                       class="form-control"
                                       value="<?= h($r['content']) ?>">
                        </td>

                        <td>
                                <input type="number"
                                       name="points"
                                       class="form-control"
                                       min="1"
                                       value="<?= (int)$r['points'] ?>">
                        </td>

                        <td>
                                <button class="btn btn-sm btn-warning me-1">更新</button>

                                <a class="btn btn-sm btn-outline-danger"
                                   href="<?= BASE_URL ?>/rules.php?delete=<?= (int)$r['id'] ?>"
                                   onclick="return confirm('確定要刪除此規則？（若已被使用會禁止刪除）');">
                                    刪除
                                </a>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
