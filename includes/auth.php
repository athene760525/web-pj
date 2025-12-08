<?php
// 用於處理使用者認證相關的功能

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * 取得目前登入的使用者（如果沒有登入，回傳 null）
 */
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * 回傳目前使用者身份（管理員 / 舍監 / 住戶），未登入回傳 null
 */
function user_identity(): ?string {
    $u = current_user();
    return $u['identity'] ?? null;
}

/**
 * 將身分代碼轉成顯示文字
 * - 未登入 / 空字串 → 訪客
 * - 管理員 / 舍監 / 住戶 → 對應中文
 * - 其他值 → 直接顯示原始值（不顯示「未知身份」）
 */
function role_text(?string $id): string {
    if ($id === null || $id === '') {
        return '訪客';
    }

    $map = [
        '管理員' => '管理員',
        '舍監'   => '舍監',
        '住戶'   => '住戶',
    ];

    return $map[$id] ?? $id;
}

/**
 * 要求登入
 * 未登入 → 直接回首頁 index.php
 */
function require_login(): void {
    if (!current_user()) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

/**
 * 限制身份才能使用
 * 使用方式：require_role(['管理員', '舍監']);
 */
function require_role(array $roles): void {
    $id = user_identity();

    if ($id === null || !in_array($id, $roles, true)) {
        echo "<h2 style='margin:20px;'>您沒有權限執行此操作。</h2>";
        exit;
    }
}

/**
 * 登入邏輯
 * 帳密正確 → true，寫入 $_SESSION['user']
 * 帳密錯誤 / 帳號不存在 → false
 */
function login(string $account, string $password): bool {
    global $conn;

    $sql = "
        SELECT account, name, password, identity
        FROM users
        WHERE account = ?
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('s', $account);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // 帳號不存在
    if (!$user) {
        return false;
    }

    // 密碼不符（之後可改成 password_verify）
    if ($password !== $user['password']) {
        return false;
    }

    // 登入成功：寫入 session
    $_SESSION['user'] = [
        'account'  => $user['account'],
        'name'     => $user['name'],
        'identity' => $user['identity'],  // 管理員 / 舍監 / 住戶
    ];

    return true;
}

/**
 * 登出
 */
function logout(): void {
    // 清除 session 內容
    $_SESSION = [];

    // 一併清除 session cookie（如果有啟用）
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    // 銷毀 session
    session_destroy();
}
