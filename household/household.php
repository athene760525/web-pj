<?php
// household.php - 住宿名單列表（僅管理員 / 舍監）

require_once "../includes/config.php";
require_once "../includes/auth.php";
require_once "../includes/db.php";

// 必須登入 & 身份檢查：只有「管理員 / 舍監」可以看
require_login();
require_role(['管理員', '舍監']);

$me      = current_user();
$myRole  = $me['identity'] ?? '';
$isAdmin = ($myRole === '管理員');

// 撈全部 household 資料 + users (JOIN)
$sql = "
    SELECT h.*, u.name AS uname, u.identity
    FROM household h
    JOIN users u ON u.account = h.StID
    ORDER BY h.semester DESC, h.number ASC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("資料查詢失敗：" . $conn->error);
}

$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
?>

<?php include "../includes/header.php"; ?>
<?php include "../includes/navbar.php"; ?>

<main class="container my-4 household-page">

    <?php if (isset($_GET["msg"])): ?>
        <div class="household-alert-wrapper mb-3">
            <?php if ($_GET["msg"] === "deleted"): ?>
                <div class="alert alert-success mb-0">刪除成功！</div>

            <?php elseif ($_GET["msg"] === "not_found"): ?>
                <div class="alert alert-danger mb-0">找不到該住宿資料。</div>

            <?php elseif ($_GET["msg"] === "missing_id"): ?>
                <div class="alert alert-warning mb-0">缺少住宿紀錄 ID。</div>

            <?php elseif ($_GET["msg"] === "error"): ?>
                <div class="alert alert-danger mb-0">刪除失敗（可能有關聯資料）。</div>

            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- 頁首標題區 -->
    <div class="household-header d-flex justify-content-between align-items-center mb-3">
        <div class="household-title-group">
            <h2 class="page-title mb-1">住宿名單</h2>
            <p class="page-subtitle text-muted mb-0">
                檢視與管理目前所有住宿學生資料。
            </p>
        </div>

        <?php if ($isAdmin): ?>
            <a href="household-insert.php" class="btn btn-success household-add-btn">
                ＋ 新增住宿紀錄
            </a>
        <?php endif; ?>
    </div>

    <!-- 主要內容卡片 -->
    <div class="card household-card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive household-table-wrapper">
                <table class="table table-bordered table-striped mb-0 table-hover table-household">
                    <thead class="table-dark">
                        <tr>
                            <th class="col-id">ID</th>
                            <th class="col-stid">學號</th>
                            <th class="col-name">姓名</th>
                            <th class="col-semester">學期</th>
                            <th class="col-room">房號</th>
                            <th class="col-phone">學生電話</th>
                            <th class="col-contact">緊急聯絡人</th>
                            <th class="col-checkin">入住時間</th>
                            <th class="col-checkout">退宿</th>
                            <th class="col-actions">操作</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                目前尚無住宿紀錄。
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r["id"]) ?></td>
                                <td><?= htmlspecialchars($r["StID"]) ?></td>
                                <!-- 注意：這裡用的是 JOIN 出來的 u.name AS uname -->
                                <td><?= htmlspecialchars($r["uname"]) ?></td>
                                <td><?= htmlspecialchars($r["semester"]) ?></td>
                                <td><?= htmlspecialchars($r["number"]) ?></td>
                                <td><?= htmlspecialchars($r["stphone"]) ?></td>
                                <td>
                                    <?= htmlspecialchars($r["Contact"]) ?>
                                    （<?= htmlspecialchars($r["relation"]) ?>）
                                </td>
                                <td><?= htmlspecialchars($r["check_in_at"]) ?></td>
                                <td>
                                    <?php if ($r["check_out_at"]): ?>
                                        <span class="badge status-badge status-out">
                                            <?= htmlspecialchars($r["check_out_at"]) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge status-badge status-in">
                                            尚未退宿
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="household-actions d-flex flex-wrap">
                                        <a class="btn btn-outline-primary btn-sm me-1 mb-1 btn-action-view"
                                           href="household-view.php?id=<?= $r["id"] ?>">
                                            查看
                                        </a>

                                        <?php if ($isAdmin): ?>
                                            <a class="btn btn-outline-warning btn-sm me-1 mb-1 btn-action-edit"
                                               href="household-update.php?id=<?= $r["id"] ?>">
                                                編輯
                                            </a>

                                            <a class="btn btn-outline-danger btn-sm mb-1 btn-action-delete"
                                               href="household-delete.php?id=<?= $r["id"] ?>"
                                               onclick="return confirm('確定要刪除這筆住宿紀錄嗎？');">
                                                刪除
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</main>

<?php include "../includes/footer.php"; ?>

