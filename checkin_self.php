<?php
require_once "includes/config.php";
require_once "includes/auth.php";
require_once "includes/db.php";

require_login(); // 一定要登入

// 取得目前登入者學號
$StID = $_SESSION['account'] ?? ($_SESSION['user']['account'] ?? null);
if (!$StID) {
    echo "<h2 style='margin:20px;'>找不到登入者帳號。</h2>";
    exit;
}

$message = "";
$error = "";
$last_checkin_time = null;

// 找出目前在住的 household
$sql = "SELECT * FROM household
        WHERE StID = ?
          AND (check_out_at IS NULL OR check_out_at = '0000-00-00 00:00:00')
        ORDER BY id DESC
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $StID);
$stmt->execute();
$household = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$household) {
    $error = "找不到你的住宿資料（可能尚未入住或已退宿）。";
}

// 使用者送出簽到表單
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $household) {

    // ===== 圖片上傳 =====
    $photoPath = null;

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // 避免檔名衝突
        $photoName = date('Ymd_His') . "_" . basename($_FILES['photo']['name']);
        $photoPath = $uploadDir . $photoName;

        move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath);
    }

    // ===== 寫入簽到紀錄 =====
    $sql = "INSERT INTO sign_in (household_id, StID, method, photo)
            VALUES (?, ?, '住戶登記', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $household['id'], $StID, $photoPath);

    if ($stmt->execute()) {
        $message = "簽到成功！";
    } else {
        $error = "簽到失敗：" . $stmt->error;
    }
    $stmt->close();
}

// 讀取最近一次簽到時間
if ($household) {
    $sql = "SELECT time FROM sign_in
            WHERE household_id = ?
            ORDER BY time DESC
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $household['id']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $last_checkin_time = $row['time'];
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
</head>
<body class="bg-light">

<?php include "includes/navbar.php"; ?>

<div class="container py-4">
    <h1 class="mb-4">宿舍自我簽到</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($household): ?>
        <!-- 住宿資訊 -->
        <div class="card mb-3">
            <div class="card-body">
                <p class="mb-1">姓名：<?= htmlspecialchars($household['name']) ?></p>
                <p class="mb-1">學號：<?= htmlspecialchars($household['StID']) ?></p>
                <p class="mb-1">房號：<?= htmlspecialchars($household['number']) ?></p>
                <p class="mb-0">學期：<?= htmlspecialchars($household['semester']) ?></p>
            </div>
        </div>

        <!-- 簽到表單 -->
        <form method="post" enctype="multipart/form-data" class="mb-3">
            <div class="mb-3">
                <label class="form-label">回宿舍照片（選填）</label>
                <input type="file" name="photo" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100">
                我已回宿舍
            </button>
        </form>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- 最近一次簽到 -->
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">最近一次簽到時間</h6>
            <p class="mb-0">
                <?= $last_checkin_time ? htmlspecialchars($last_checkin_time) : "尚未有簽到紀錄" ?>
            </p>
        </div>
    </div>
</div>

</body>
</html>
