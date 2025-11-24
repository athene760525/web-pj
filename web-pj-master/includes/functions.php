<?php
// includes/functions.php
//declare(strict_types=1);

require_once __DIR__ . '/config.php';

/* 安全輸出 */
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/* 取得目前登入者（如果沒有登入回傳 null） */
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

/* 簡單的重新導向 */
function redirect(string $path): void {
    header("Location: " . BASE_URL . $path);
    exit;
}

/* debug 用 */
function dd($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    exit;
}
