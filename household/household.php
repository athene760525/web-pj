<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";
require_once "../includes/db.php";

// 必須登入才能看
require_login();

// 只有舍監和管理員可以看住戶列表
if (!is_manager()) {
    echo "<h2 style='margin:20px;'>您沒有權限查看住宿名單。</h2>";
    exit;
}


// 撈全部 household 資料 + users (JOIN)
$sql = "
    SELECT h.*, u.name AS uname, u.identity
    FROM household h
    JOIN users u ON u.account = h.StID
    ORDER BY h.semester DESC, h.number ASC
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
?>

<?php include "../includes/header.php"; ?>
<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">
    <h2>住宿名單（household）</h2>

    <a href="household-insert.php" class="btn btn-success mb-3">十 新增住宿紀錄</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>學號</th>
            <th>姓名</th>
            <th>學期</th>
            <th>房號</th>
            <th>學生電話</th>
            <th>緊急聯絡人</th>
            <th>入住時間</th>
            <th>退宿</th>
            <th>操作</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($rows as $r) : ?>
            <tr>
                <td><?= htmlspecialchars($r["id"]) ?></td>
                <td><?= htmlspecialchars($r["StID"]) ?></td>
                <td><?= htmlspecialchars($r["name"]) ?></td>
                <td><?= htmlspecialchars($r["semester"]) ?></td>
                <td><?= htmlspecialchars($r["number"]) ?></td>
                <td><?= htmlspecialchars($r["stphone"]) ?></td>
                <td><?= htmlspecialchars($r["Contact"] . "（" . $r["relation"] . "）") ?></td>
                <td><?= htmlspecialchars($r["check_in_at"]) ?></td>
                <td><?= $r["check_out_at"] ? htmlspecialchars($r["check_out_at"]) : "尚未退宿" ?></td>

                <td>
                    <a class="btn btn-primary btn-sm" href="household-view.php?id=<?= $r["id"] ?>">查看</a>
                    <a class="btn btn-warning btn-sm" href="household-update.php?id=<?= $r["id"] ?>">編輯</a>
                    <a class="btn btn-danger btn-sm"
                       href="household-delete.php?id=<?= $r["id"] ?>"
                       onclick="return confirm('確定要刪除這筆住宿紀錄嗎？');">
                        刪除
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/footer.php"; ?>