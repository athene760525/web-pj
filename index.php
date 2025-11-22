<?php
// index.php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5">
    
    <p class="lead">
        這是系統首頁，目前設定為「不用登入也可以看」的公開頁面。<br>
        之後要管理住民資料、簽到紀錄、違規紀錄時，可以透過上方導覽列的「登入」進入。
    </p>

    <div class="row g-3 mt-4">
        <div class="col-md-4">
            <div class="card h-100 border-primary">
                <div class="card-body">
                    <h5 class="card-title">住民資料 Household</h5>
                    <p class="card-text">管理學生各學期住宿紀錄。</p>
                    <button class="btn btn-sm btn-primary" disabled>功能開發中</button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-info">
                <div class="card-body">
                    <h5 class="card-title">簽到紀錄 Sign-in</h5>
                    <p class="card-text">紀錄學生返宿時間。</p>
                    <button class="btn btn-sm btn-info" disabled>功能開發中</button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-danger">
                <div class="card-body">
                    <h5 class="card-title">違規紀錄 Violation</h5>
                    <p class="card-text">登記與統計違規扣點。</p>
                    <button class="btn btn-sm btn-danger" disabled>功能開發中</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
