<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";
require_once "../includes/db.php";

require_login();

// 僅管理員/舍監可用
if (!is_manager()) {
    echo "<h2 style='margin:20px;'>您沒有權限編輯住宿紀錄。</h2>";
    exit;
}

// 取得 id
$id = $_GET["id"] ?? null;

if (!$id) {
    echo "<h2 style='margin:20px;'>錯誤：缺少住宿紀錄 ID。</h2>";
    exit;
}

// 撈這筆住宿資料
$sql = "SELECT * FROM household WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$house = $result->fetch_assoc();

if (!$house) {
    echo "<h2 style='margin:20px;'>找不到這筆住宿紀錄。</h2>";
    exit;
}

$error = "";
$success = "";

// ==============================
// 處理 POST 修改資料
// ==============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $semester = $_POST["semester"];
    $number   = $_POST["number"];
    $stphone  = $_POST["stphone"];
    $contact  = $_POST["Contact"];
    $relation = $_POST["relation"];
    $rephone  = $_POST["rephone"];
    $check_out = $_POST["check_out_at"] ?: null;

    if ($semester === "" || $number === "") {
        $error = "學期與房號為必填。";
    } else {
        try {
            $sql_update = "
                UPDATE household
                SET semester = ?, number = ?, stphone = ?, Contact = ?, relation = ?, rephone = ?, check_out_at = ?
                WHERE id = ?
            ";
            $stmt2 = $conn->prepare($sql_update);
            $stmt2->bind_param(
                "sisssssi",
                $semester, $number, $stphone, $contact, $relation, $rephone, $check_out, $id
            );

            $stmt2->execute();
            $success = "✔ 住宿資料更新成功！";

            // 更新 $house 方便更新成功後刷新畫面
            $house["semester"] = $semester;
            $house["number"] = $number;
            $house["stphone"] = $stphone;
            $house["Contact"] = $contact;
            $house["relation"] = $relation;
            $house["rephone"] = $rephone;
            $house["check_out_at"] = $check_out;

        } catch (mysqli_sql_exception $e) {
            $error = "更新失敗：" . $e->getMessage();
        }
    }
}

?>

<?php include "../includes/header.php"; ?>
<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">
    <h2>編輯住宿紀錄</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-3">

        <div class="mb-3">
            <label class="form-label">學號（不可修改）</label>
            <input type="text" class="form-control" value="<?= $house["StID"] ?>" disabled>
        </div>

        <div class="mb-3">
            <label class="form-label">學生姓名（不可修改）</label>
            <input type="text" class="form-control" value="<?= $house["name"] ?>" disabled>
        </div>

        <div class="mb-3">
            <label class="form-label">性別（不可修改）</label>
            <input type="text" class="form-control" value="<?= $house["gender"] ?>" disabled>
        </div>

        <div class="mb-3">
            <label class="form-label">學期</label>
            <input type="text" name="semester" class="form-control"
                value="<?= htmlspecialchars($house["semester"]) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">房號</label>
            <input type="number" name="number" class="form-control"
                value="<?= htmlspecialchars($house["number"]) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">學生電話</label>
            <input type="text" name="stphone" class="form-control"
                value="<?= htmlspecialchars($house["stphone"]) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">緊急聯絡人</label>
            <input type="text" name="Contact" class="form-control"
                value="<?= htmlspecialchars($house["Contact"]) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">關係</label>
            <input type="text" name="relation" class="form-control"
                value="<?= htmlspecialchars($house["relation"]) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">聯絡人電話</label>
            <input type="text" name="rephone" class="form-control"
                value="<?= htmlspecialchars($house["rephone"]) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">退宿時間（選填）</label>
            <input type="datetime-local" name="check_out_at" class="form-control"
                value="<?= $house["check_out_at"] ? date('Y-m-d\TH:i', strtotime($house["check_out_at"])) : '' ?>">
        </div>

        <button class="btn btn-primary">更新資料</button>
        <a href="household.php" class="btn btn-secondary">返回住宿列表</a>
    </form>

</div>

<?php include "../includes/footer.php"; ?>