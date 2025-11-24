<?php
// includes/db.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$DB_HOST = 'localhost';   
$DB_NAME = 'room';
$DB_USER = 'root';
$DB_PASS = '';            // XAMPP 預設空字串；若你有設密碼就改掉

try {
  $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
  $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
  http_response_code(500);
  echo "<h3>資料庫連線失敗</h3>";
  echo "<pre>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</pre>";
  exit;
}

function db_close(mysqli $conn) {
  if ($conn && $conn->ping()) $conn->close();
}

