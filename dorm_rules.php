<?php
require_once "includes/config.php";
require_once "includes/db.php";

// 取得顯示類型
$view = isset($_GET['view']) ? $_GET['view'] : 'agreement';

if ($view === 'penalty') {
    $title = "違規內容 (扣點標準)";
    $sql = "SELECT article_no, content, points FROM penalty ORDER BY article_no ASC";
    $is_penalty = true;
} else {
    $view = 'agreement';
    $title = "住宿規範";
    $sql = "SELECT article_no, content FROM dorm_agreement ORDER BY article_no ASC";
    $is_penalty = false;
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar-link.active { background-color: #007bff !important; color: white !important; }
        .page-header { border-left: 5px solid #007bff; padding-left: 15px; margin-bottom: 25px; }
    </style>
</head>
<body class="bg-light">

<?php include "includes/navbar.php"; ?>

<div class="container my-5">
    <div class="row">
        <!-- 左側選單 -->
        <div class="col-md-3">
            <div class="list-group shadow-sm mb-3">
                <div class="list-group-item bg-dark text-white fw-bold">規章目錄</div>
                <a href="?view=agreement" class="list-group-item list-group-item-action sidebar-link <?php echo ($view == 'agreement') ? 'active' : ''; ?>">
                    <i class="fas fa-file-contract me-2"></i> 住宿規範
                </a>
                <a href="?view=penalty" class="list-group-item list-group-item-action sidebar-link <?php echo ($view == 'penalty') ? 'active' : ''; ?>">
                    <i class="fas fa-exclamation-circle me-2"></i> 違規內容 (扣點)
                </a>
                <!-- 手動發信按鈕 -->
                <button type="button" class="list-group-item list-group-item-action text-primary" data-bs-toggle="modal" data-bs-target="#emailModal">
                    <i class="fas fa-envelope me-2"></i> 意見反映 (Email)
                </button>
            </div>
        </div>

        <!-- 右側表格 -->
        <div class="col-md-9">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="page-header"><h3 class="mb-0"><?php echo $title; ?></h3></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light text-center">
                                <tr>
                                    <th style="width: 15%;">條例編號</th>
                                    <th>條例內容</th>
                                    <?php if ($is_penalty): ?> <th style="width: 15%;">扣點分值</th> <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result && $result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td class='text-center fw-bold'>{$row['article_no']}</td>";
                                        echo "<td>" . nl2br(htmlspecialchars($row['content'])) . "</td>";
                                        if ($is_penalty) echo "<td class='text-center text-danger fw-bold'>{$row['points']}</td>";
                                        echo "</tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 發信彈窗 (Modal) -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="send_mail.php" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-paper-plane me-2"></i> 聯繫管理員</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">您的姓名</label>
                        <input type="text" name="user_name" class="form-control" placeholder="請輸入姓名" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">您的信箱</label>
                        <input type="email" name="user_email" class="form-control" placeholder="name@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">您的問題</label>
                        <textarea name="message" class="form-control" rows="4" placeholder="請描述您的問題..." required></textarea>
                    </div>
                    <!-- 隱藏欄位紀錄當前頁面 -->
                    <input type="hidden" name="from_view" value="<?php echo $view; ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">送出郵件</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>