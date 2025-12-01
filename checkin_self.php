<?php

require_once "includes/config.php";
require_once "includes/auth.php";
require_once "includes/db.php";

require_login();

$StID = $_SESSION['account'] ?? ($_SESSION['user']['account'] ?? null);

$message = "";
$last_checkin_time = null;

// 1. 先抓目前這位同學「在住中」的 household 記錄
$sql = "SELECT * FROM household
        WHERE StID = ?
          AND check_out_at IS NULL
        ORDER BY id DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $StID);
$stmt->execute();
$result = $stmt->get_result();
$household = $result->fetch_assoc();
$stmt->close();

if (!$household) {
    $message = "找不到你的住宿資料（可能尚未入住或已退宿）。";
} else {

    // 2. 如果有送出簽到表單，就寫入 sign_in
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $insert = "INSERT INTO sign_in (household_id, StID, method)
                   VALUES (?, ?, '住戶登記')";
        $stmt = $conn->prepare($insert);
        $stmt->bind_param("is", $household['id'], $StID);

        try {
            $stmt->execute();
            $message = "簽到成功！";
        } catch (mysqli_sql_exception $e) {
            $message = "簽到失敗：" . $e->getMessage();
        }

        $stmt->close();
    }

    // 3. 查詢最近一次簽到時間（不管是舍監登記或住戶登記）
    $sql_last = "SELECT time
                 FROM sign_in
                 WHERE household_id = ?
                 ORDER BY time DESC
                 LIMIT 1";
    $stmt = $conn->prepare($sql_last);
    $stmt->bind_param("i", $household['id']);
    $stmt->execute();
    $result_last = $stmt->get_result();
    if ($row_last = $result_last->fetch_assoc()) {
        $last_checkin_time = $row_last['time'];
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>宿舍自我簽到</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="assets/css/main.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include "includes/navbar.php"; ?>

<main class="container py-4">

    <h1 class="mb-4">宿舍自我簽到</h1>

    <?php if (!$household): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php else: ?>

        <!-- 住戶基本資訊卡片 -->
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title mb-3">目前住宿資料</h5>
                <p class="mb-1">姓名：<?= htmlspecialchars($household['name']) ?></p>
                <p class="mb-1">學號：<?= htmlspecialchars($household['StID']) ?></p>
                <p class="mb-1">房號：<?= htmlspecialchars($household['number']) ?></p>
                <p class="mb-0">學期：<?= htmlspecialchars($household['semester']) ?></p>
            </div>
        </div>

        <!-- 簽到按鈕 -->
        <form method="post" class="mb-3">
            <button type="submit" class="btn btn-primary btn-lg w-100">
                我已回宿舍
            </button>
        </form>

        <!-- 顯示訊息（例如：簽到成功！） -->
        <?php if ($message): ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- 最近一次簽到時間 -->
        <div class="card">
            <div class="card-body">
                <h6 class="card-title mb-2">最近一次簽到時間</h6>
                <p class="mb-0">
                    <?php if ($last_checkin_time): ?>
                        <?= htmlspecialchars($last_checkin_time) ?>
                    <?php else: ?>
                        尚未有簽到紀錄
                    <?php endif; ?>
                </p>
            </div>
        </div>

    <?php endif; ?>

</main>

<?php include "includes/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
