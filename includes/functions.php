<?php
// includes/functions.php
//declare(strict_types=1);

require_once __DIR__ . '/config.php';

/* 安全輸出 */
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
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

/* ====== 搜尋功能 ====== */
function build_user_search(string $q): array {
    if ($q === '') {
        return ['', [], ''];  // where, params, types
    }

    $where  = "WHERE account LIKE ? OR name LIKE ?";
    $params = ["%{$q}%", "%{$q}%"];
    $types  = "ss";

    return [$where, $params, $types];
}

/* ====== 排序功能 ====== */
function build_user_sort(array $allowedSort): array {
    $sort = $_GET['sort'] ?? $allowedSort[0];
    $dir  = strtolower($_GET['dir'] ?? 'asc');

    if (!in_array($sort, $allowedSort, true)) {
        $sort = $allowedSort[0];
    }

    if (!in_array($dir, ['asc', 'desc'], true)) {
        $dir = 'asc';
    }

    return ["ORDER BY {$sort} " . strtoupper($dir), $sort, $dir];
}