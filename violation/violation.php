<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";
require_once "../includes/db.php";
require_once "../includes/functions.php";

require_login();

$me       = current_user();
$identity = $me['identity'] ?? '';
$account  = $me['account'] ?? '';
$myName   = $me['name'] ?? '';

// 管理員 / 舍監
$isStaff = in_array($identity, ['管理員', '舍監']);

// 搜尋關鍵字
$q = $_GET['q'] ?? '';

// 指定學生（從 household / users 連過來）
$targetStID = $_GET['stid'] ?? '';

// =======================
// SQL 組裝
// =======================
$params = [];
$types  = '';
$where  = [];

if ($isStaff) {

    // ① 若指定 stid（點學生過來）
    if ($targetStID !== '') {
        $where[]  = 'v.StID = ?';
        $params[] = $targetStID;
        $types   .= 's';
    }

    // ② 搜尋（可與 stid 並存）
    if ($q !== '') {
        $where[]  = '(v.StID LIKE ? OR u.name LIKE ? OR v.content LIKE ?)';
        $params[] = "%$q%";
        $params[] = "%$q%";
        $params[] = "%$q%";
        $types   .= 'sss';
    }

    $whereSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $sql = "
        SELECT 
            v.*,
            u.name AS student_name,
            h.semester
        FROM violation v
        JOIN users u ON u.account = v.StID
        LEFT JOIN household h ON h.id = v.household_id
        $whereSQL
        ORDER BY v.v_time DESC
    ";

    $stmt = $conn->prepare($sql);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }

} else {
    // =======================
    // 學生：只能看自己
    // =======================
    $sql = "
        SELECT *
        FROM violation
        WHERE StID = ?
        ORDER BY v_time DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $account);
}

$stmt->execute();
$result = $stmt->get_result();

// =======================
// 累積扣點（學生 OR 管理員檢視單一學生）
// =======================
$sumTarget = (!$isStaff) ? $account : $targetStID;
$totalPoints = null;

if ($sumTarget !== '') {
    $sumStmt = $conn->prepare("
        SELECT COALESCE(SUM(points),0) AS total
        FROM violation
        WHERE StID = ?
    ");
    $sumStmt->bind_param("s", $sumTarget);
    $sumStmt->execute();
    $totalPoints = (int)$sumStmt->get_result()->fetch_assoc()['total'];
}

// 若為管理員且檢視單一學生，取得該學生姓名以便顯示
$targetName = '';
if ($isStaff && $targetStID !== '') {
    $nstmt = $conn->prepare("SELECT name FROM users WHERE account = ?");
    if ($nstmt) {
        $nstmt->bind_param("s", $targetStID);
        $nstmt->execute();
        $nrow = $nstmt->get_result()->fetch_assoc();
        $targetName = $nrow['name'] ?? '';
    }
}

$WARNING_LIMIT = 10; // ⚠ 扣點警戒值
?>

<?php include "../includes/header.php"; ?>
<?php include "../includes/navbar.php"; ?>

<main class="container my-4">

    <?php if (isset($_GET['msg'])): ?>
        <div class="mb-3">
            <?php if ($_GET['msg'] === 'updated'): ?>
                <div class="alert alert-success mb-0">✅ 更新成功！</div>
            <?php elseif ($_GET['msg'] === 'deleted'): ?>
                <div class="alert alert-success mb-0">✅ 刪除成功！</div>
            <?php elseif ($_GET['msg'] === 'not_found'): ?>
                <div class="alert alert-danger mb-0">❌ 找不到該違規紀錄。</div>
            <?php elseif ($_GET['msg'] === 'forbidden'): ?>
                <div class="alert alert-danger mb-0">❌ 您沒有權限執行此操作。</div>
            <?php elseif ($_GET['msg'] === 'error'): ?>
                <div class="alert alert-danger mb-0">❌ 操作失敗，請稍後再試。</div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">違規紀錄</h2>

        <?php if ($isStaff): ?>
            <a href="violation-create.php" class="btn btn-danger">
                ＋ 新增違規紀錄
            </a>
        <?php endif; ?>
    </div>

    <!-- 管理員：檢視單一學生 -->
    <?php if ($isStaff && $targetStID !== ''): ?>
        <div class="alert alert-info">
            目前檢視學號：
            <strong><?= h($targetStID) ?></strong>
            ／ 姓名：
            <strong><?= h($targetName) ?></strong>
            ，累積扣點：
            <strong class="<?= $totalPoints >= $WARNING_LIMIT ? 'text-danger' : '' ?>">
                <?= (int)$totalPoints ?>
            </strong>
            <a href="../household/household.php" class="ms-2">（返回全部）</a>
        </div>
    <?php endif; ?>

    <!-- 學生自己的警告 -->
    <?php if (!$isStaff): ?>
        <div class="alert <?= $totalPoints >= $WARNING_LIMIT ? 'alert-danger' : 'alert-info' ?>">
            學號：<strong><?= h($account) ?></strong>
            &nbsp;／&nbsp;
            姓名：<strong><?= h($myName) ?></strong>
            &nbsp;／&nbsp;
            累積扣點：
            <strong><?= (int)$totalPoints ?></strong>
            <?php if ($totalPoints >= $WARNING_LIMIT): ?>
                ⚠ 已達警戒值，請注意行為紀律
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- 管理員搜尋 -->
    <?php if ($isStaff): ?>
        <form class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text"
                       name="q"
                       class="form-control"
                       placeholder="搜尋學號、姓名或違規內容"
                       value="<?= h($q) ?>">
            </div>
            <?php if ($targetStID !== ''): ?>
                <input type="hidden" name="stid" value="<?= h($targetStID) ?>">
            <?php endif; ?>
            <div class="col-auto">
                <button class="btn btn-outline-primary">搜尋</button>
            </div>
        </form>
    <?php endif; ?>

    <!-- 違規表 -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
        <tr>
            <?php if ($isStaff): ?>
                <th>學號</th>
                <th>姓名</th>
            <?php endif; ?>
            <th>違規時間</th>
            <th>違規內容</th>
            <th>扣點</th>
            <?php if ($isStaff): ?>
                <th style="width:160px;">操作</th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>

        <?php if ($result->num_rows === 0): ?>
            <tr>
                <td colspan="<?= $isStaff ? 5 : 3 ?>" class="text-center text-muted">
                    尚無違規紀錄
                </td>
            </tr>
        <?php endif; ?>

        <?php while ($r = $result->fetch_assoc()): ?>
            <tr>
                <?php if ($isStaff): ?>
                    <td><?= h($r['StID']) ?></td>
                    <td><?= h($r['student_name']) ?></td>
                <?php endif; ?>

                <td><?= h($r['v_time']) ?></td>
                <td><?= h($r['content']) ?></td>

                <td class="<?= $r['points'] >= 5 ? 'text-danger fw-bold' : '' ?>">
                    <?= h($r['points']) ?>
                </td>

                <?php if ($isStaff): ?>
                    <td>
                        <a href="violation-update.php?id=<?= (int)$r['id'] ?>"
                        class="btn btn-sm btn-outline-warning">
                            編輯
                        </a>

                        <a href="violation-delete.php?id=<?= (int)$r['id'] ?>"
                        class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('確定要刪除這筆違規紀錄嗎？');">
                            刪除
                        </a>
                    </td>
                <?php endif; ?>
            </tr>
            <?php endwhile; ?>


        </tbody>
    </table>

</main>

<?php include "../includes/footer.php"; ?>
