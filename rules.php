<?php
// rules.php - 違規規則（學生唯讀 / 管理員可管理）

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$me       = current_user();
$identity = $me['identity'] ?? '';
$isStaff  = in_array($identity, ['管理員', '舍監']);

// PDO 連線
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
    die('資料庫連線失敗');
}

$msg   = $_GET['msg'] ?? '';
$error = '';

// =======================
// 管理員操作
// =======================
if ($isStaff && $_SERVER['REQUEST_METHOD'] === 'POST') {

    // 新增
    if ($_POST['action'] === 'create') {

        $article_no = trim($_POST['article_no']);
        $content    = trim($_POST['content']);
        $points     = (int)$_POST['points'];

        if ($article_no === '' || $content === '' || $points < 0) {
            $error = '新增失敗：資料不完整';
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO penalty (article_no, content, points)
                 VALUES (?, ?, ?)"
            );
            $stmt->execute([$article_no, $content, $points]);
            header("Location: rules.php?msg=created");
            exit;
        }
    }

    // 更新
    if ($_POST['action'] === 'update') {

        $id         = (int)$_POST['id'];
        $article_no = trim($_POST['article_no']);
        $content    = trim($_POST['content']);
        $points     = (int)$_POST['points'];

        if ($id <= 0 || $article_no === '' || $content === '' || $points < 0) {
            $error = '更新失敗：資料不完整';
        } else {
            $stmt = $pdo->prepare(
                "UPDATE penalty
                 SET article_no = ?, content = ?, points = ?
                 WHERE id = ?"
            );
            $stmt->execute([$article_no, $content, $points, $id]);
            header("Location: rules.php?msg=updated");
            exit;
        }
    }
}

// 刪除（GET）
if ($isStaff && isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $stmt = $pdo->prepare("SELECT article_no FROM penalty WHERE id = ?");
    $stmt->execute([$id]);
    $rule = $stmt->fetch();

    if ($rule) {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM violation WHERE article_no = ?"
        );
        $stmt->execute([$rule['article_no']]);

        if ((int)$stmt->fetchColumn() === 0) {
            $stmt = $pdo->prepare("DELETE FROM penalty WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: rules.php?msg=deleted");
            exit;
        } else {
            header("Location: rules.php?msg=used");
            exit;
        }
    }
}

// 撈規則
$rules = $pdo->query(
    "SELECT id, article_no, content, points FROM penalty ORDER BY id ASC"
)->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="container my-4">
    <h2 class="mb-3">違規規則</h2>

    <?php if ($msg === 'created'): ?><div class="alert alert-success">新增成功</div><?php endif; ?>
    <?php if ($msg === 'updated'): ?><div class="alert alert-success">更新成功</div><?php endif; ?>
    <?php if ($msg === 'deleted'): ?><div class="alert alert-success">刪除成功</div><?php endif; ?>
    <?php if ($msg === 'used'): ?><div class="alert alert-danger">規則已被使用，無法刪除</div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

    <!-- 新增（管理員） -->
    <?php if ($isStaff): ?>
        <div class="card mb-4">
            <div class="card-header">新增規則</div>
            <div class="card-body">
                <form method="post" class="row g-2">
                    <input type="hidden" name="action" value="create">
                    <div class="col-md-3"><input name="article_no" class="form-control" placeholder="條例"></div>
                    <div class="col-md-6"><input name="content" class="form-control" placeholder="內容"></div>
                    <div class="col-md-1"><input name="points" type="number" min="0" class="form-control"></div>
                    <div class="col-md-2"><button class="btn btn-danger w-100">新增</button></div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- 列表 -->
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>條例</th>
            <th>內容</th>
            <th>扣點</th>
            <?php if ($isStaff): ?><th style="width:180px;">操作</th><?php endif; ?>
        </tr>
        </thead>
        <tbody>

        <?php foreach ($rules as $r): ?>
            <tr>
                <form method="post">
                    <td><?= $r['id'] ?></td>

                    <td>
                        <?php if ($isStaff): ?>
                            <input type="text" name="article_no" class="form-control"
                                   value="<?= h($r['article_no']) ?>">
                        <?php else: ?>
                            <?= h($r['article_no']) ?>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if ($isStaff): ?>
                            <input type="text" name="content" class="form-control"
                                   value="<?= h($r['content']) ?>">
                        <?php else: ?>
                            <?= h($r['content']) ?>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if ($isStaff): ?>
                            <input type="number" name="points" class="form-control"
                                   value="<?= (int)$r['points'] ?>">
                        <?php else: ?>
                            <?= (int)$r['points'] ?>
                        <?php endif; ?>
                    </td>

                    <?php if ($isStaff): ?>
                        <td>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">

                            <button class="btn btn-sm btn-warning me-1">更新</button>

                            <a href="rules.php?delete=<?= $r['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('確定要刪除？');">
                                刪除
                            </a>
                        </td>
                    <?php endif; ?>
                </form>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
