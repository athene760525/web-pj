<?php
require_once "includes/config.php";
require_once "includes/auth.php";
require_once "includes/db.php";

require_login();


// 1) 讀取篩選條件（GET）
$date = $_GET['date'] ?? date('Y-m-d');     // 預設今天
$q    = trim($_GET['q'] ?? "");             // 搜尋：姓名/學號/房號
$method = trim($_GET['method'] ?? "");      // 舍監登記 / 住戶登記 / 全部


// 2) 今日統計（已簽到/總在住/未簽到）
// 在住人數：check_out_at IS NULL
$sqlTotalDorm = "SELECT COUNT(*) AS c FROM household WHERE check_out_at IS NULL";
$totalDorm = 0;
$res = $conn->query($sqlTotalDorm);
if ($res && $row = $res->fetch_assoc()) $totalDorm = (int)$row['c'];

// 今日已簽到（以 household_id 去重：同一人今天可能多次簽到）
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT household_id) AS c
    FROM sign_in
    WHERE DATE(time) = ?
");
$stmt->bind_param("s", $date);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$totalSigned = (int)($row['c'] ?? 0);
$stmt->close();

$totalUnsigned = max(0, $totalDorm - $totalSigned);


// 3) 今日簽到清單（可搜尋/可篩選 method）
$where = " WHERE DATE(s.time) = ? ";
$params = [$date];
$types  = "s";

if ($method !== "" && in_array($method, ["舍監登記","住戶登記"], true)) {
    $where .= " AND s.method = ? ";
    $params[] = $method;
    $types .= "s";
}

if ($q !== "") {
    // q 可以搜：姓名 name、學號 StID、房號 number
    $where .= " AND (h.name LIKE ? OR h.StID LIKE ? OR CAST(h.number AS CHAR) LIKE ?) ";
    $like = "%{$q}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

$sqlSigned = "
    SELECT
        s.time, s.method,
        h.name, h.StID, h.number, h.semester
    FROM sign_in s
    JOIN household h ON h.id = s.household_id
    $where
    ORDER BY s.time DESC
";

$stmt = $conn->prepare($sqlSigned);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$signedRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// 4) 今日未簽到清單（在住但今天沒有任何簽到）
$sqlUnsigned = "
    SELECT h.name, h.StID, h.number, h.semester
    FROM household h
    LEFT JOIN sign_in s
      ON s.household_id = h.id
     AND DATE(s.time) = ?
    WHERE h.check_out_at IS NULL
      AND s.id IS NULL
    ORDER BY h.number ASC
";
$stmt = $conn->prepare($sqlUnsigned);
$stmt->bind_param("s", $date);
$stmt->execute();
$unsignedRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>簽到清單</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include "includes/navbar.php"; ?>

<main class="container py-4">

    <h1 class="mb-3">簽到清單</h1>

    <!-- 篩選器 -->
    <form class="row g-2 mb-3" method="get">
        <div class="col-md-3">
            <label class="form-label">日期</label>
            <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">簽到方式</label>
            <select name="method" class="form-select">
                <option value="" <?= $method==="" ? "selected" : "" ?>>全部</option>
                <option value="住戶登記" <?= $method==="住戶登記" ? "selected" : "" ?>>住戶登記</option>
                <option value="舍監登記" <?= $method==="舍監登記" ? "selected" : "" ?>>舍監登記</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">搜尋（姓名/學號/房號）</label>
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="例如：小雯 / 4125845 / 601">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100" type="submit">查詢</button>
        </div>
    </form>

    <!-- 今日統計 -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <div class="text-muted">在住宿總人數</div>
                <div class="fs-3"><?= $totalDorm ?> 人</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <div class="text-muted">當日已簽到（去重）</div>
                <div class="fs-3"><?= $totalSigned ?> 人</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <div class="text-muted">當日未簽到</div>
                <div class="fs-3"><?= $totalUnsigned ?> 人</div>
            </div></div>
        </div>
    </div>

    <!-- 簽到紀錄 -->
    <div class="card mb-4">
        <div class="card-header">
            <?= htmlspecialchars($date) ?> 簽到紀錄（<?= count($signedRows) ?> 筆）
        </div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>時間</th>
                    <th>方式</th>
                    <th>房號</th>
                    <th>姓名</th>
                    <th>學號</th>
                    <th>學期</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$signedRows): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">無資料</td></tr>
                <?php else: ?>
                    <?php foreach ($signedRows as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['time']) ?></td>
                            <td><?= htmlspecialchars($r['method']) ?></td>
                            <td><?= htmlspecialchars($r['number']) ?></td>
                            <td><?= htmlspecialchars($r['name']) ?></td>
                            <td><?= htmlspecialchars($r['StID']) ?></td>
                            <td><?= htmlspecialchars($r['semester']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 未簽到清單 -->
    <div class="card">
        <div class="card-header">
            <?= htmlspecialchars($date) ?> 未簽到（<?= count($unsignedRows) ?> 人）
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                <tr>
                    <th>房號</th>
                    <th>姓名</th>
                    <th>學號</th>
                    <th>學期</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$unsignedRows): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">全部已簽到</td></tr>
                <?php else: ?>
                    <?php foreach ($unsignedRows as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['number']) ?></td>
                            <td><?= htmlspecialchars($r['name']) ?></td>
                            <td><?= htmlspecialchars($r['StID']) ?></td>
                            <td><?= htmlspecialchars($r['semester']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<?php include "includes/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
