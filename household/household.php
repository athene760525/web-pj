<?php
// household.php - 住宿名單列表（Bootstrap Collapse 展開詳細資料，無 AJAX）

require_once "../includes/config.php";
require_once "../includes/auth.php";
require_once "../includes/db.php";
require_once "../includes/functions.php"; // h()

require_login();
require_role(['管理員', '舍監']);

$me      = current_user();
$myRole  = $me['identity'] ?? '';
$isAdmin = ($myRole === '管理員');

$ALLOWED_DETAIL = [
    'StID'         => '學號',
    'uname'        => '姓名',
    'uidentity'    => '身份',
    'semester'     => '學期',
    'number'       => '房號',
    'stphone'      => '學生電話',
    'Contact'      => '緊急聯絡人',
    'relation'     => '關係',
    'check_in_at'  => '入住時間',
    'check_out_at' => '退宿時間',
];

$sql = "
    SELECT 
        h.*,
        u.name AS uname,
        u.identity AS uidentity
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
        <div class="mb-3">
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

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1">住宿名單</h2>
            <p class="text-muted mb-0">檢視與管理目前所有住宿學生資料。</p>
        </div>

        <?php if ($isAdmin): ?>
            <a href="household-insert.php" class="btn btn-success">＋ 新增住宿紀錄</a>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0 table-household">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:6%;">ID</th>
                            <th style="width:10%;">學號</th>
                            <th style="width:10%;">姓名</th>
                            <th style="width:10%;">學期</th>
                            <th style="width:8%;">房號</th>
                            <th style="width:12%;">學生電話</th>
                            <th style="width:18%;">緊急聯絡人</th>
                            <th style="width:12%;">入住時間</th>
                            <th style="width:10%;">退宿</th>
                            <th style="width:14%;">操作</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">目前尚無住宿紀錄。</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                            <?php
                                $id = (int)$r['id'];
                                $collapseId = "hhDetail{$id}";
                            ?>

                            <!-- 主列 -->
                            <tr>
                                <td><?= h($r["id"]) ?></td>
                                <td><?= h($r["StID"]) ?></td>
                                <td><?= h($r["uname"]) ?></td>
                                <td><?= h($r["semester"]) ?></td>
                                <td><?= h($r["number"]) ?></td>
                                <td><?= h($r["stphone"]) ?></td>
                                <td><?= h($r["Contact"]) ?>（<?= h($r["relation"]) ?>）</td>
                                <td><?= h($r["check_in_at"]) ?></td>
                                <td>
                                    <?php if (!empty($r["check_out_at"])): ?>
                                        <span class="text-danger"><?= h($r["check_out_at"]) ?></span>
                                    <?php else: ?>
                                        <span class="text-primary">未退宿</span>
                                    <?php endif; ?>
                                </td>

                                <td class="col-actions">
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#<?= h($collapseId) ?>"
                                                aria-controls="<?= h($collapseId) ?>"
                                                aria-expanded="false">
                                            查看資料
                                        </button>

                                        <?php if ($isAdmin): ?>
                                            <a class="btn btn-outline-warning"
                                               href="household-update.php?id=<?= $id ?>">
                                                編輯
                                            </a>

                                            <a class="btn btn-outline-danger"
                                               href="household-delete.php?id=<?= $id ?>"
                                               onclick="return confirm('確定要刪除這筆住宿紀錄嗎？');">
                                                刪除
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>

                            <!-- 詳細列（Collapse 內容） -->
                            <tr>
                                <td colspan="10" class="p-0 bg-light">
                                    <div id="<?= h($collapseId) ?>" class="collapse">
                                        <div class="p-3">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <div class="fw-semibold">住宿詳細資料</div>
                                                <small class="text-muted">ID：<?= h($r['id']) ?></small>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-sm mb-0 align-middle">
                                                    <tbody>
                                                    <?php foreach ($ALLOWED_DETAIL as $col => $label): ?>
                                                        <?php
                                                            if (!array_key_exists($col, $r)) continue;
                                                            $val = $r[$col];

                                                            if ($col === 'check_out_at' && empty($val)) {
                                                                $val = '尚未退宿';
                                                            }
                                                        ?>
                                                        <tr>
                                                            <th class="text-muted" style="width:25%;"><?= h($label) ?></th>
                                                            <td><?= h($val === null ? '' : $val) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
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