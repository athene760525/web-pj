<?php
require_once __DIR__ . "/includes/config.php";
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/navbar.php";


// 1. 目前住宿中人數：check_out_at 為 NULL 表示尚未退宿

$sqlDorm = "
    SELECT COUNT(*) AS total_dorm
    FROM household
    WHERE check_out_at IS NULL
";
$resDorm   = $conn->query($sqlDorm);
$totalDorm = 0;
if ($resDorm && $row = $resDorm->fetch_assoc()) {
    $totalDorm = (int)$row['total_dorm'];
}

/**
 * 2. 今日簽到人數：依 sign_in.time
 *    計算今天有簽到過的不同學生數（避免同一人多次簽到重複計）
 */
$sqlSign = "
    SELECT COUNT(DISTINCT StID) AS total_signed_today
    FROM sign_in
    WHERE DATE(time) = CURDATE()
";
$resSign          = $conn->query($sqlSign);
$totalSignedToday = 0;
if ($resSign && $row2 = $resSign->fetch_assoc()) {
    $totalSignedToday = (int)$row2['total_signed_today'];
}

/**
 * 3. 本週新增違規：依 violation.v_time
 *    這裡用 YEARWEEK(v_time, 1) = YEARWEEK(CURDATE(), 1)
 *    表示「本週（週一到週日）」
 */
$sqlViolation = "
    SELECT COUNT(*) AS weekly_violation
    FROM violation
    WHERE YEARWEEK(v_time, 1) = YEARWEEK(CURDATE(), 1)
";
$resVio          = $conn->query($sqlViolation);
$weeklyViolation = 0;
if ($resVio && $row3 = $resVio->fetch_assoc()) {
    $weeklyViolation = (int)$row3['weekly_violation'];
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>宿舍管理系統首頁</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- 自訂樣式 -->
    <link href="assets/css/main.css" rel="stylesheet">

<main class="page">
    <!-- Hero 區塊 -->
    <section class="hero">
        <div class="container hero-inner">
            <div class="hero-text">
                <h1 class="hero-title">宿舍住民安全與紀律系統</h1>
                <p class="hero-subtitle">
                    提供違規記點、返宿簽到、住民資料管理，一站式查看與維護住民狀態。
                </p>
                <div class="hero-actions">
                </div>
            </div>
            <div class="hero-panel">
                <div class="hero-card">
                    <h2 class="hero-card-title">今日總覽</h2>
                    <ul class="hero-list">
                        <li class="hero-list-item">
                            <span class="hero-list-label">今日已簽到</span>
                            <span class="hero-list-value">
                                <?= htmlspecialchars($totalSignedToday, ENT_QUOTES, "UTF-8") ?> 人
                            </span>
                        </li>
                        <li class="hero-list-item">
                            <span class="hero-list-label">本週新增違規</span>
                            <span class="hero-list-value">
                                <?= htmlspecialchars($weeklyViolation, ENT_QUOTES, "UTF-8") ?> 筆
                            </span>
                        </li>
                        <li class="hero-list-item">
                            <span class="hero-list-label">目前住宿人數</span>
                            <span class="hero-list-value">
                                <?= htmlspecialchars($totalDorm, ENT_QUOTES, "UTF-8") ?> 人
                            </span>
                        </li>
                    </ul>
                    <p class="hero-card-note">＊由資料庫帶入真實數字</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 功能說明區塊 -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">系統主要功能</h2>
            <div class="row g-3 feature-list">
                <div class="col-md-4">
                    <div class="feature-card">
                        <h3 class="feature-title">住宿規範</h3>
                        <p class="feature-text">
                            公開給所有住民查詢，管理員可以在後台維護與更新內容。
                        </p>
                        <a href="rules.php" class="feature-link">前往規範頁面</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <h3 class="feature-title">返宿簽到</h3>
                        <p class="feature-text">
                            住民可以查看自己的簽到紀錄，舍監可協助補簽與查詢全部。
                        </p>
                        <a href="sing-in.php" class="feature-link">前往簽到紀錄</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <h3 class="feature-title">違規記點</h3>
                        <p class="feature-text">
                            管理員與舍監可新增、修改、刪除違規紀錄，系統自動加總點數。
                        </p>
                        <a href="violation.php" class="feature-link">前往違規列表</a>
                    </div>
                </div>
<?php include 'includes/footer.php'; ?>