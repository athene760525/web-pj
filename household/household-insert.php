<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";
require_once "../includes/db.php";

require_login();

if (!is_manager()) {
    echo "<h2 class='mt-4 ms-4'>您沒有權限新增住宿紀錄。</h2>";
    exit;
}

$search_result = null;
$last_house_record = null;
$error = "";
$success = "";

$keyword = $_GET["keyword"] ?? "";   // 搜尋欄位自動保留輸入值

/* ===============================
   處理搜尋學生
   =============================== */
if ($_SERVER["REQUEST_METHOD"] === "GET" && $keyword !== "") {

    // 從 users 找住戶
    $sql = "
        SELECT account AS StID, name
        FROM users
        WHERE identity = '住戶'
        AND (account LIKE ? OR name LIKE ?)
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $like = "%{$keyword}%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();

    $search_result = $result->fetch_assoc();

    if (!$search_result) {
        $error = "找不到該學生（姓名或學號均可搜尋）";
    } else {
        /* 查詢該學生最後一筆住宿紀錄，用於自動帶入性別與聯絡資料 */
        $sql_last = "
            SELECT gender, stphone, Contact, relation, rephone
            FROM household
            WHERE StID = ?
            ORDER BY id DESC
            LIMIT 1
        ";
        $stmt2 = $conn->prepare($sql_last);
        $stmt2->bind_param("s", $search_result["StID"]);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        $last_house_record = $res2->fetch_assoc();
    }
}

/* ===============================
   處理新增住宿 POST
   =============================== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $StID     = $_POST["StID"];
    $semester = $_POST["semester"];
    $name     = $_POST["name"];
    $gender   = $_POST["gender"];
    $number   = $_POST["number"];
    $stphone  = $_POST["stphone"];
    $contact  = $_POST["Contact"];
    $relation = $_POST["relation"];
    $rephone  = $_POST["rephone"];

    // 欄位錯誤紅框
    $invalid = [];

    if ($StID === "") $invalid["StID"] = true;
    if ($semester === "") $invalid["semester"] = true;
    if ($number === "") $invalid["number"] = true;

    if (!empty($invalid)) {
        $error = "學號、學期、房號為必填欄位。";
    } else {
        try {
            $sql = "
                INSERT INTO household (
                    StID, semester, name, gender, number,
                    stphone, Contact, relation, rephone, check_in_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssissss",
                $StID, $semester, $name, $gender, $number,
                $stphone, $contact, $relation, $rephone
            );
            $stmt->execute();

            $success = "✔ 新增住宿紀錄成功！";
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                $error = "該學生在同一學期已有住宿紀錄！";
            } else {
                $error = "資料庫錯誤：" . $e->getMessage();
            }
        }
    }
}
?>

<?php include "../includes/header.php"; ?>
<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">

    <h2>搜尋學生</h2>

    <!-- 搜尋欄 -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="keyword" class="form-control"
                   placeholder="輸入學號 或 姓名 查詢"
                   value="<?= htmlspecialchars($keyword) ?>">
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary">搜尋</button>
        </div>
    </form>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>


    <?php if ($search_result): ?>

        <hr>
        <h3 class="mt-4">學生基本資料</h3>

        <p><strong>學號：</strong> <?= $search_result["StID"] ?></p>
        <p><strong>姓名：</strong> <?= $search_result["name"] ?></p>

        <?php if ($last_house_record): ?>
            <p><strong>性別：</strong> <?= $last_house_record["gender"] ?></p>
        <?php else: ?>
            <p><strong>性別：</strong> 尚無住宿紀錄（需填寫）</p>
        <?php endif; ?>

        <hr>

        <h3>十 新增住宿紀錄</h3>

        <form method="POST" class="mt-3">

            <input type="hidden" name="StID" value="<?= $search_result["StID"] ?>">
            <input type="hidden" name="name" value="<?= $search_result["name"] ?>">

            <!-- 性別（自動帶入/不給選） -->
            <input type="hidden" name="gender" value="<?= $last_house_record["gender"] ?? "" ?>">

            <div class="mb-3">
                <label class="form-label">學期</label>
                <input type="text"
                       name="semester"
                       class="form-control <?= isset($invalid["semester"]) ? "is-invalid" : "" ?>"
                       placeholder="例如：114-1"
                       value="<?= htmlspecialchars($_POST["semester"] ?? '') ?>"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">房號</label>
                <input type="number"
                       name="number"
                       class="form-control <?= isset($invalid["number"]) ? "is-invalid" : "" ?>"
                       value="<?= htmlspecialchars($_POST["number"] ?? '') ?>"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">學生電話</label>
                <input type="text" name="stphone" class="form-control"
                       value="<?= htmlspecialchars($_POST["stphone"] ?? ($last_house_record["stphone"] ?? '')) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">緊急聯絡人</label>
                <input type="text" name="Contact" class="form-control"
                       value="<?= htmlspecialchars($_POST["Contact"] ?? ($last_house_record["Contact"] ?? '')) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">關係</label>
                <input type="text" name="relation" class="form-control"
                       value="<?= htmlspecialchars($_POST["relation"] ?? ($last_house_record["relation"] ?? '')) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">聯絡人電話</label>
                <input type="text" name="rephone" class="form-control"
                       value="<?= htmlspecialchars($_POST["rephone"] ?? ($last_house_record["rephone"] ?? '')) ?>">
            </div>

            <button class="btn btn-success">新增住宿</button>
            <a href="household.php" class="btn btn-secondary">返回列表</a>
        </form>

    <?php endif; ?>

</div>

<?php include "../includes/footer.php"; ?>
