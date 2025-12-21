<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";
require_once "../includes/db.php";

require_login();

// 僅管理員 / 舍監 可編輯
require_role(['管理員', '舍監']);

// 取得要刪除的 id
$id = $_GET["id"] ?? null;

if (!$id) {
    header("Location: household.php?msg=missing_id");
    exit;
}

// 確認資料是否存在
$sql_check = "SELECT * FROM household WHERE id = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$house = $result->fetch_assoc();

if (!$house) {
    header("Location: household.php?msg=not_found");
    exit;
}

// 執行刪除
try {
    // $conn->query("DELETE FROM violation WHERE household_id=$id");
    // $conn->query("DELETE FROM sign_in WHERE household_id=$id");

    $sql_del = "DELETE FROM household WHERE id = ?";
    $stmt2 = $conn->prepare($sql_del);
    $stmt2->bind_param("i", $id);
    $stmt2->execute();

    header("Location: household.php?msg=deleted");
    exit;

} catch (mysqli_sql_exception $e) {
    header("Location: household.php?msg=error");
    exit;
}