<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$me = current_user();
$roleText = $me['identity'] ?? '';
?>

<!-- includes/navbar.php -->
<header class="site-header">
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom site-nav">
        <div class="container">
            <a class="navbar-brand nav-brand-text" href="index.php">
                DormSystem
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#mainNav" aria-controls="mainNav"
                    aria-expanded="false" aria-label="切換導覽">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <!-- 左側選單 -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/rules.php">住宿規範</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/checkin_self.php">簽到頁面</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/violation.php">違規紀錄</a></li>
                </ul>

                <!-- 右側登入區塊 -->
                <ul class="navbar-nav d-flex align-items-center">
                    <?php if ($me): ?>
                        <li class="nav-item d-flex align-items-center">
                            <span class="navbar-text text-dark me-3">
                                歡迎，<?= h($me['name']) ?>（<?= h($roleText) ?>）
                            </span>
                        </li>

                        <!-- 個人資料 / 修改密碼 -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/profile.php">資料修改</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/logout.php">登出</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/login.php">登入</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
