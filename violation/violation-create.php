<?php
// violation/violation-create.php

require_once "../includes/config.php";
require_once "../includes/auth.php";
require_once "../includes/db.php";
require_once "../includes/functions.php";

require_login();
require_role(['管理員', '舍監']);

$error = '';
$success = '';

// =======================
// 1️⃣ 搜尋學生
// =======================
$q = $_GET['q'] ?? '';
$student = null;
$household = null;

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
        $household = $student['household_id'];
    } else {
        $error = '找不到該學生，或該學生目前未住宿。';
    }
}

// =======================
// 2️⃣ 取得 rules 清單
// =======================
$rules = [];
$ruleSql = "SELECT id, content, points FROM rules ORDER BY id ASC";
$ruleRes = $conn->query($ruleSql);
while ($r = $ruleRes->fetch_assoc()) {
    $rules[] = $r;
}

// =======================
// 3️⃣ 新增違規（POST）
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $household_id = $_POST['household_id'] ?? '';
    $stid         = $_POST['StID'] ?? '';
    $rule_id      = $_POST['rule_id'] ?? '';

    if (!$household_id || !$stid || !$rule_id) {
        $error = '資料不完整，請重新操作。';
    } else {

        // 撈規則
        $stmt = $conn->prepare("SELECT content, points FROM rules WHERE id = ?");
        $stmt->bind_param("i", $rule_id);
        $stmt->execute();
        $rule = $stmt->get_result()->fetch_assoc();

        if (!$rule) {
            $error = '找不到違規規則。';
        } else {
            $content = $rule['content'];
            $points  = $rule['points'];

            $sql = "
                INSERT INTO violation
                (household_id, StID, content, points, v_time)
                VALUES (?, ?, ?, ?, NOW())
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "issi",
                $household_id,
                $stid,
                $content,
                $points
            );

            if ($stmt->execute()) {
                $success = '違規紀錄已成功新增。';
            } else {
                $error = '新增失敗，請稍後再試。';
            }
        }
    }
}
?>

<?php include "../includes/header.php"; ?>
<?php include "../includes/navbar.php"; ?>

<main class="container my-4">
    <h2 class="mb-3">新增違規紀錄</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= h($success) ?></div>
        <a href="violation.php" class="btn btn-secondary">返回違規列表</a>
    <?php endif; ?>

    <!-- ① 搜尋學生 -->
    <form method="get" class="mb-4">
        <label class="form-label">搜尋學生（學號 / 姓名）</label>
        <div class="input-group">
            <input type="text" name="q" class="form-control"
                   value="<?= h($q) ?>"
                   placeholder="輸入學號或姓名">
            <button class="btn btn-outline-primary">搜尋</button>
        </div>
    </form>

    <?php if ($student): ?>
        <!-- ② 顯示學生 -->
        <div class="card mb-4">
            <div class="card-body">
                <p><strong>學號：</strong><?= h($student['account']) ?></p>
                <p><strong>姓名：</strong><?= h($student['name']) ?></p>
            </div>
        </div>

        <!-- ③ 新增違規 -->
        <form method="post">
            <input type="hidden" name="household_id" value="<?= h($household) ?>">
            <input type="hidden" name="StID" value="<?= h($student['account']) ?>">

            <div class="mb-3">
                <label class="form-label">違規規則</label>
                <select name="rule_id" class="form-select" required>
                    <option value="">請選擇</option>
                    <?php foreach ($rules as $r): ?>
                        <option value="<?= $r['id'] ?>">
                            <?= h($r['content']) ?>（<?= $r['points'] ?> 點）
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button class="btn btn-danger">新增違規</button>
            <a href="violation.php" class="btn btn-secondary">取消</a>
        </form>
    <?php endif; ?>
</main>

<?php include "../includes/footer.php"; ?>
