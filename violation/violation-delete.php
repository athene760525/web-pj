<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";
require_once "../includes/db.php";

require_login();
require_role(['管理員', '舍監']);

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: violation.php");
    exit;
}

$stmt = $conn->prepare("DELETE FROM violation WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: violation.php");
exit;
