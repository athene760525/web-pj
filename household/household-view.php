<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";
require_once "../includes/db.php";
require_once "../includes/functions.php";

require_login();
require_role(['管理員', '舍監']);

$id = $_GET['id'] ?? null;
if (!$id) {
    die("缺少住宿 ID");
}

// 住宿資料
$sqlH = "
    SELECT h.*, u.name AS uname
    FROM household h
    JOIN users u ON u.account = h.StID
    WHERE h.id = ?
";
$stmt = $conn->prepare($sqlH);
$stmt->bind_param("i", $id);
$stmt->execute();
$house = $stmt->get_result()->fetch_assoc();

if (!$house) {
    die("找不到住宿資料");
}

// 違規資料
$sqlV = "
    SELECT *
    FROM violation
    WHERE household_id = ?
    ORDER BY v_time DESC
";
$stmtV = $conn->prepare($sqlV);
$stmtV->bind_param("i", $id);
$stmtV->execute();
$violations = $stmtV->get_result();
?>

<?php include "../includes/header.php"; ?>
<?php include "../includes/navbar.php"; ?>

<main class="container my-4">
    <h2 class="mb-3">住宿資料檢視</h2>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>學號：</strong><?= h($house['StID']) ?></p>
            <p><strong>姓名：</strong><?= h($house['uname']) ?></p>
            <p><strong>學期：</strong><?= h($house['semester']) ?></p>
            <p><strong>房號：</strong><?= h($house['number']) ?></p>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4>違規紀錄</h4>
        <a href="../violation/violation-insert.php?household_id=<?= $id ?>"
           class="btn btn-danger btn-sm">
            ＋ 新增違規
        </a>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
        <tr>
            <th>違規時間</th>
            <th>內容</th>
            <th>扣點</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($violations->num_rows === 0): ?>
            <tr><td colspan="4" class="text-center text-muted">尚無違規紀錄</td></tr>
        <?php else: ?>
            <?php while ($v = $violations->fetch_assoc()): ?>
                <tr>
                    <td><?= h($v['v_time']) ?></td>
                    <td><?= h($v['content']) ?></td>
                    <td><?= h($v['points']) ?></td>
                    <td>
                        <a href="../violation/violation-delete.php?id=<?= $v['id'] ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('確定刪除此違規？');">
                            刪除
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <a href="household.php" class="btn btn-secondary mt-3">返回住宿名單</a>
</main>

<?php include "../includes/footer.php"; ?>
