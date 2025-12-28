<?php
require_once "includes/config.php";
require_once "includes/auth.php";
require_once "includes/db.php";

require_login();
require_role(['管理員', '舍監']);

$message = "";
$error = "";

// 1) 讀取目前在住住戶清單（供下拉選單）
$sqlResidents = "
    SELECT id, StID, name, number, semester
    FROM household
    WHERE check_out_at IS NULL
    ORDER BY number ASC, id DESC
";
$resResidents = $conn->query($sqlResidents);
$residents = [];
if ($resResidents) {
    while ($row = $resResidents->fetch_assoc()) {
        $residents[] = $row;
    }
}

// 2) 表單送出：新增簽到
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $household_id = (int)($_POST['household_id'] ?? 0);
    $checkin_time = trim($_POST['checkin_time'] ?? ""); // 可空，空就用 DB 的 default current_timestamp

    if ($household_id <= 0) {
        $error = "請先選擇住戶。";
    } else {
        // 查出住戶資料（避免亂填 household_id）
        $stmt = $conn->prepare("SELECT id, StID, name, number FROM household WHERE id = ? AND check_out_at IS NULL LIMIT 1");
        $stmt->bind_param("i", $household_id);
        $stmt->execute();
        $household = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$household) {
            $error = "住戶不存在或已退宿。";
        } else {
            // 插入簽到紀錄
            if ($checkin_time === "") {
                // 不指定時間 -> 用 sign_in.time 的 DEFAULT current_timestamp()
                $stmt = $conn->prepare("INSERT INTO sign_in (household_id, StID, method) VALUES (?, ?, '舍監登記')");
                $stmt->bind_param("is", $household_id, $household['StID']);
            } else {
                // 指定時間 -> 寫入 time 欄位
                $stmt = $conn->prepare("INSERT INTO sign_in (household_id, StID, time, method) VALUES (?, ?, ?, '舍監登記')");
                $stmt->bind_param("iss", $household_id, $household['StID'], $checkin_time);
            }

            if ($stmt->execute()) {
                $message = "簽到成功：{$household['name']}（房號 {$household['number']}）";
            } else {
                $error = "簽到失敗：" . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// 3) 讀取最近 20 筆簽到（方便你確認有寫進 DB）
$sqlRecent = "
    SELECT s.time, s.method, h.name, h.StID, h.number
    FROM sign_in s
    JOIN household h ON h.id = s.household_id
    ORDER BY s.time DESC
    LIMIT 20
";
$resRecent = $conn->query($sqlRecent);
$recentRows = [];
if ($resRecent) {
    while ($row = $resRecent->fetch_assoc()) {
        $recentRows[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>管理員簽到</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include "includes/navbar.php"; ?>

<main class="container py-4">
    <h1 class="mb-3">管理員簽到（舍監登記）</h1>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">新增簽到</div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">選擇住戶</label>
                    <select name="household_id" class="form-select" required>
                        <option value="">-- 請選擇 --</option>
                        <?php foreach ($residents as $r): ?>
                            <option value="<?= (int)$r['id'] ?>">
                                <?= htmlspecialchars($r['number']) ?>｜<?= htmlspecialchars($r['name']) ?>｜<?= htmlspecialchars($r['StID']) ?>｜<?= htmlspecialchars($r['semester']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">簽到時間</label>
                    <input type="datetime-local" name="checkin_time" class="form-control">
                </div>

                <div class="col-12">
                    <button class="btn btn-primary btn-lg w-100" type="submit">新增簽到（舍監登記）</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">最近 20 筆簽到紀錄</div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>時間</th>
                    <th>方式</th>
                    <th>房號</th>
                    <th>姓名</th>
                    <th>學號</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$recentRows): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">尚無資料</td></tr>
                <?php else: ?>
                    <?php foreach ($recentRows as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['time']) ?></td>
                            <td><?= htmlspecialchars($r['method']) ?></td>
                            <td><?= htmlspecialchars($r['number']) ?></td>
                            <td><?= htmlspecialchars($r['name']) ?></td>
                            <td><?= htmlspecialchars($r['StID']) ?></td>
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
