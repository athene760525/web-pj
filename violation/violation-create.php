<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";
require_once "../includes/db.php";
require_once "../includes/functions.php";

// 權限檢查
require_login();
require_role(['管理員', '舍監']);

$error = '';
$success = '';
$defaultVTime = date('Y-m-d\TH:i'); 

// ==========================================
// 1. 搜尋學生邏輯
// ==========================================
$q = $_GET['q'] ?? '';
$student = null;
$household_id = null;

if ($q !== '') {
    $sql = "
        SELECT u.account, u.name, h.id AS household_id
        FROM users u
        JOIN household h ON h.StID = u.account
        WHERE (u.account LIKE ? OR u.name LIKE ?)
          AND h.check_out_at IS NULL
        ORDER BY h.id DESC
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    $like = "%$q%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();

    if ($student) {
        $household_id = $student['household_id'];
    } else {
        $error = '找不到該學生，或該學生目前未住宿。';
    }
}

// ==========================================
// 2. 取得 penalty 清單 (主鍵使用 id)
// ==========================================
$penalties = [];
// 這裡同時抓取 id, article_no, content, points
$pSql = "SELECT id, article_no, content, points FROM penalty ORDER BY article_no ASC";
$pRes = $conn->query($pSql);
if ($pRes) {
    while ($row = $pRes->fetch_assoc()) {
        $penalties[] = $row;
    }
}

// ==========================================
// 3. 處理新增違規 POST
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $form_h_id    = $_POST['household_id'] ?? '';
    $form_stid    = $_POST['StID'] ?? '';
    $form_penalty_id = $_POST['penalty_id'] ?? ''; // 這裡接收的是 penalty 表的 id
    $form_v_time  = $_POST['v_time'] ?? '';

    if (!$form_h_id || !$form_stid || !$form_penalty_id) {
        $error = '請確認已選擇學生與違規條例。';
    } else {
        // 使用 id 撈取該條例的內容與點數
        $stmt = $conn->prepare("SELECT content, points FROM penalty WHERE id = ?");
        $stmt->bind_param("i", $form_penalty_id);
        $stmt->execute();
        $rule = $stmt->get_result()->fetch_assoc();

        if (!$rule) {
            $error = '找不到該違規條例。';
        } else {
            $content = $rule['content'];
            $points  = $rule['points'];
            $v_time_sql = $form_v_time ? str_replace('T', ' ', $form_v_time) . ':00' : date('Y-m-d H:i:s');

            $insertSql = "INSERT INTO violation (household_id, StID, content, points, v_time) VALUES (?, ?, ?, ?, ?)";
            $insStmt = $conn->prepare($insertSql);
            $insStmt->bind_param("issis", $form_h_id, $form_stid, $content, $points, $v_time_sql);

            if ($insStmt->execute()) {
                // 統計該生目前總扣點
                $countSql = "SELECT SUM(points) as total FROM violation WHERE StID = ?";
                $cStmt = $conn->prepare($countSql);
                $cStmt->bind_param("s", $form_stid);
                $cStmt->execute();
                $totalPoints = $cStmt->get_result()->fetch_assoc()['total'];

                $success = "違規紀錄已成功新增！<br>學生：<strong>" . htmlspecialchars($student['name']) . "</strong><br>累計扣點：<strong class='text-danger'>{$totalPoints}</strong> 點。";
            } else {
                $error = '資料庫新增失敗。';
            }
        }
    }
}
?>

<?php include "../includes/header.php"; ?>
<?php include "../includes/navbar.php"; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-danger text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-gavel me-2"></i>管理員操作：新增違規扣點</h5>
                </div>
                <div class="card-body p-4">
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                        <div class="mb-3"><a href="violation.php" class="btn btn-secondary btn-sm">返回列表</a></div>
                    <?php endif; ?>

                    <!-- 步驟 1：搜尋 -->
                    <form method="get" class="mb-4">
                        <label class="form-label fw-bold">第一步：搜尋學生（學號或姓名）</label>
                        <div class="input-group">
                            <input type="text" name="q" class="form-control" placeholder="例如：S112001..." value="<?= htmlspecialchars($q) ?>">
                            <button class="btn btn-dark" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>

                    <?php if ($student): ?>
                        <hr class="my-4">
                        <!-- 步驟 2：填單 -->
                        <form method="post">
                            <input type="hidden" name="action" value="create">
                            <input type="hidden" name="household_id" value="<?= $student['household_id'] ?>">
                            <input type="hidden" name="StID" value="<?= $student['account'] ?>">

                            <div class="alert alert-info py-2">
                                <i class="fas fa-user-check me-2"></i> 
                                正在為 <strong><?= htmlspecialchars($student['name']) ?></strong> (<?= htmlspecialchars($student['account']) ?>) 新增紀錄
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">第二步：選擇違反條例</label>
                                <select name="penalty_id" class="form-select" required>
                                    <option value="">-- 請選擇規範條款 --</option>
                                    <?php foreach ($penalties as $p): ?>
                                        <option value="<?= $p['id'] ?>">
                                            第 <?= htmlspecialchars($p['article_no']) ?> 條：<?= htmlspecialchars($p['content']) ?> (扣 <?= $p['points'] ?> 點)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">第三步：核對日期時間</label>
                                <input type="datetime-local" name="v_time" class="form-control" value="<?= $defaultVTime ?>">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger btn-lg">確認送出並扣點</button>
                                <a href="violation.php" class="btn btn-link text-muted mt-2">取消操作</a>
                            </div>
                        </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>