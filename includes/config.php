<?php
// includes/config.php
declare(strict_types=1);
date_default_timezone_set('Asia/Taipei');

/* 啟用 Session */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* 專案根目錄 URL（依你的實際路徑設定） */
define('BASE_URL', '/web-pj');

/* 資料庫連線需要時再引入 db.php */
