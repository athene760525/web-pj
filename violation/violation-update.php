<?php
// violation/violation-edit.php

require_once "../includes/config.php";
require_once "../includes/auth.php";
require_once "../includes/db.php";
require_once "../includes/functions.php";

require_login();
require_role(['管理員', '舍監']);

$error = '';
$success = '';

$id = $_GET['id'] ?? '';
if (!$id) {
    die('缺少違規紀錄 ID');
}

/* =======================
   1️⃣ 撈違規紀錄
======================= */
$sql = "
    SELECT 
        v.*,
        u.name AS student_name
    FROM violation v
    JOIN users u ON u.account = v.StID
    WHERE v.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$violation = $stmt->get_result()->fetch_assoc();

if (!$violation) {
    die('找不到該違規紀錄');
}

// 用於表單的 datetime-local 預設值
$violation_vtime = date('Y-m-d\TH:i', strtotime($violation['v_time']));

/* =======================
   2️⃣ 撈 rules 清單
======================= */
$rules = [];
$ruleSql = "SELECT id, content, points FROM rules ORDER BY id ASC";
$ruleRes = $conn->query($ruleSql);
while ($r = $ruleRes->fetch_assoc()) {
    $rules[] = $r;
}

/* =======================
   3️⃣ 更新（POST）
======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rule_id = $_POST['rule_id'] ?? '';

    if (!$rule_id) {
        $error = '請選擇違規規則';
    } else {
        // 找規則
        $stmt = $conn->prepare("SELECT content, points FROM rules WHERE id = ?");
        $stmt->bind_param("i", $rule_id);
        $stmt->execute();
        $rule = $stmt->get_result()->fetch_assoc();

        if (!$rule) {
            $error = '找不到違規規則';
        } else {
            $content = $rule['content'];
            $points  = $rule['points'];

            // 允許同時更新違規時間
            $v_time = $_POST['v_time'] ?? '';
            if ($v_time) {
                $v_time_sql = str_replace('T', ' ', $v_time) . ':00';
            } else {
                $v_time_sql = $violation['v_time'];
            }

            $sql = "
                UPDATE violation
                SET content = ?, points = ?, v_time = ?
                WHERE id = ?
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisi", $content, $points, $v_time_sql, $id);

            if ($stmt->execute()) {
                header("Location: violation.php?msg=updated");
                exit;
            } else {
                $error = '更新失敗，請稍後再試';
            }
        }
    }
}
?>

<?php include "../includes/header.php"; ?>
<?php include "../includes/navbar.php"; ?>

<main class="container my-4">
    <h2 class="mb-3">編輯違規紀錄</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>學號：</strong><?= h($violation['StID']) ?></p>
            <p><strong>姓名：</strong><?= h($violation['student_name']) ?></p>
            <p><strong>違規時間：</strong><?= h($violation['v_time']) ?></p>
        </div>
    </div>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">違規規則</label>
            <select name="rule_id" class="form-select" required>
                <option value="">請選擇</option>
                <?php foreach ($rules as $r): ?>
                    <option value="<?= $r['id'] ?>"
                        <?= ($r['content'] === $violation['content']) ? 'selected' : '' ?>>
                        <?= h($r['content']) ?>（<?= h($r['points']) ?> 點）
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">違規時間</label>
            <input type="datetime-local" name="v_time" class="form-control"
                   value="<?= h($violation_vtime) ?>">
        </div>

        <button class="btn btn-primary">儲存修改</button>
        <a href="violation.php" class="btn btn-secondary">取消</a>
    </form>
</main>

<?php include "../includes/footer.php"; ?>
