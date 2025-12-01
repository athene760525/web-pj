<?php
//用於處理使用者認證相關的功能

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * 取得目前登入者（從 session）
 */
//function current_user() {
//    return $_SESSION['user'] ?? null;
//}

/**
 * 判斷是否為「管理員」或「舍監」
 */
function is_manager(): bool {
    $u = current_user();
    if (!$u) return false;

    return ($u['identity'] === '管理員' || $u['identity'] === '舍監');
}

/**
 * 確保已登入，否則導向 login.php
 */
function require_login(): void {
    if (!current_user()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * 登入邏輯
 * 帳密正確 → true
 * 錯誤 → false
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

    /* 帳號不存在 */
    if (!$user) {
        return false;
    }

    /* 密碼不符（你之後可以改 password_hash） */
    if ($password !== $user['password']) {
        return false;
    }

    /* 登入成功：寫入 session */
    $_SESSION['user'] = [
        'account'  => $user['account'],
        'name'     => $user['name'],
        'identity' => $user['identity'],  // 管理員/舍監/住戶
    ];

    return true;
}

/**
 * 登出
 */
function logout(): void {
    $_SESSION = [];
    session_destroy();
}
