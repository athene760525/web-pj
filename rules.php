<?php
// rules.php - 住宿規範與扣點條款【管理員專用後台】
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

// ★ 權限嚴格控管：僅限「管理員」進入。
// 舍監雖然在 navbar 看得到後台選單，但點擊此功能會被踢回 penalty.php
if (user_identity() !== '管理員') {
    header("Location: penalty.php?msg=permission_denied");
    exit;
}

// PDO 連線
try {
    $pdo = new PDO("mysql:host=localhost;dbname=room;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) { 
    die("連線失敗：" . $e->getMessage()); 
}

// --- 處理新增 (Create) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $stmt = $pdo->prepare("INSERT INTO penalty (article_no, content, points) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['article_no'], $_POST['content'], (int)$_POST['points']]);
    header("Location: rules.php?msg=created"); exit;
}

// --- 處理更新 (Update) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $stmt = $pdo->prepare("UPDATE penalty SET article_no = ?, content = ?, points = ? WHERE id = ?");
    $stmt->execute([$_POST['article_no'], $_POST['content'], (int)$_POST['points'], (int)$_POST['id']]);
    header("Location: rules.php?msg=updated"); exit;
}

// --- 處理刪除 (Delete) ---
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM penalty WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header("Location: rules.php?msg=deleted"); exit;
}

// 撈取資料
$rules = $pdo->query("SELECT * FROM penalty ORDER BY article_no ASC")->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-tools text-danger me-2"></i>住宿規範與扣點管理 <small class="text-muted" style="font-size: 0.5em;">(管理員模式)</small></h3>
        <a href="penalty.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-eye me-1"></i> 返回前端預覽
        </a>
    </div>

    <!-- 訊息提示區 -->
    <?php 
    $msg = $_GET['msg'] ?? '';
    if($msg == 'created') echo '<div class="alert alert-success">✅ 成功新增一條條文</div>';
    if($msg == 'updated') echo '<div class="alert alert-info">ℹ️ 條文內容已更新</div>';
    if($msg == 'deleted') echo '<div class="alert alert-warning">🗑️ 條文已刪除</div>';
    ?>

    <!-- 新增條文區塊 -->
    <div class="card mb-4 border-success shadow-sm">
        <div class="card-header bg-success text-white">
            <i class="fas fa-plus-circle me-1"></i> 新增規章條文
        </div>
        <div class="card-body bg-light">
            <form method="post" class="row g-2">
                <input type="hidden" name="action" value="create">
                <div class="col-md-2">
                    <label class="small text-muted">條例編號</label>
                    <input type="text" name="article_no" class="form-control" placeholder="如: 1-1" required>
                </div>
                <div class="col-md-7">
                    <label class="small text-muted">規章內容說明</label>
                    <input type="text" name="content" class="form-control" placeholder="請輸入完整描述..." required>
                </div>
                <div class="col-md-2">
                    <label class="small text-muted">扣點分值</label>
                    <input type="number" name="points" class="form-control" placeholder="點數" min="0" required>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-success w-100">新增</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 條文列表與快速編輯 -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white shadow-sm">
            <thead class="table-dark text-center">
                <tr>
                    <th width="10%">編號</th>
                    <th>規範內容</th>
                    <th width="10%">扣點</th>
                    <th width="18%">管理操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rules)): ?>
                    <tr><td colspan="4" class="text-center py-4 text-muted">目前沒有條文資料</td></tr>
                <?php endif; ?>

                <?php foreach ($rules as $r): ?>
                <tr>
                    <form method="post">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <td>
                            <input type="text" name="article_no" class="form-control form-control-sm text-center fw-bold" value="<?= h($r['article_no']) ?>">
                        </td>
                        <td>
                            <textarea name="content" class="form-control form-control-sm" rows="1"><?= h($r['content']) ?></textarea>
                        </td>
                        <td>
                            <input type="number" name="points" class="form-control form-control-sm text-center text-danger fw-bold" value="<?= $r['points'] ?>">
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button class="btn btn-warning btn-sm px-3">
                                    <i class="fas fa-save me-1"></i>更新
                                </button>
                                <a href="rules.php?delete=<?= $r['id'] ?>" 
                                   class="btn btn-outline-danger btn-sm" 
                                   onclick="return confirm('警告！刪除此條文可能會影響歷史違規紀錄的查閱，確定要刪除嗎？')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>