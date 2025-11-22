<?php

$me = current_user();

/* 身分轉換為中文 */
$roleText = '';
if ($me) {
    switch ($me['identity']) {
        case '管理員': $roleText = '管理員'; break;
        case '舍監': $roleText = '老師'; break;
        case '學生': default: $roleText = '學生';break;
        
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">

        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
            輔仁大學學生宿舍住房系統
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <!-- 左側選單 -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/rules.php">住宿規範</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/sing-in.php">簽到頁面</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/violation.php">違規紀錄</a></li>
            </ul>

            <!-- 右側登入區塊 -->
            <ul class="navbar-nav d-flex align-items-center">
                <?php if ($me): ?>
                    <li class="nav-item d-flex align-items-center">
                        <span class="navbar-text text-white me-3">
                            歡迎，<?= h($me['name']) ?>（<?= $roleText ?>）
                        </span>
                    </li>

                    
            <!-- 新增這個：個人資料 / 修改密碼 -->
            <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/profile.php">
                資料修改 </a>
             </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/logout.php">登出</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="<?= BASE_URL ?>/login.php">登入</a>
                    </li>
                <?php endif; ?>
            </ul>

        </div>
    </div>
</nav>
