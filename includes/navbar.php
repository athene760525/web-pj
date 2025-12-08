<?php
// includes/navbar.php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';      // ★ 一定要載入，裡面有 current_user()
require_once __DIR__ . '/functions.php'; // h() 等共用工具

$me       = current_user();              // 登入中的使用者（或 null）
$identity = user_identity();             // 管理員 / 舍監 / 住戶 / null
$roleText = role_text($identity);        // 管理員 / 舍監 / 住戶 / 訪客
?>

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
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/rules.php">住宿規範</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/checkin_self.php">簽到頁面</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/violation.php">違規紀錄</a>
                    </li>

                    <?php if ($identity === '管理員' || $identity === '舍監'): ?>
                        <!-- 只有 管理員 / 舍監 看得到的選單 -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/users.php">使用者資料</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/household.php">住戶資料</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- 右側登入區塊 -->
                <ul class="navbar-nav d-flex align-items-center">
                    <?php if ($me): ?>
                        <li class="nav-item d-flex align-items-center">
                            <span class="navbar-text text-dark me-3">
                                您好，<?= h($me['name']) ?>（<?= h($roleText) ?>）
                            </span>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/profile.php">資料修改</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/logout.php">登出</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-main" href="<?= BASE_URL ?>/login.php">登入系統</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
