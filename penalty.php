<?php
require_once "includes/config.php";
require_once "includes/db.php";


// 1. 取得當前要顯示的類型 (預設為 'agreement')
$view = isset($_GET['view']) ? $_GET['view'] : 'agreement';

// 2. 根據點選的項目設定 SQL 語法與標題
if ($view === 'penalty') {
    $title = "違規內容 (扣點標準)";
    $sql = "SELECT article_no, content, points FROM penalty ORDER BY article_no ASC";
    $is_penalty = true; // 用來判斷是否要顯示「扣點」這一欄
} else {
    $view = 'agreement'; // 強制校正
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
    <title><?php echo $title; ?> - 宿舍管理系統</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar-link {
            transition: all 0.3s;
        }
        .sidebar-link.active {
            background-color: #007bff !important;
            color: white !important;
            font-weight: bold;
        }
        .table thead {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        /* 水平置中標題 */
        .table th {
            text-align: center;
            vertical-align: middle;
        }
        /* 垂直置中內容 */
        .table td {
            vertical-align: middle;
        }
        .page-header {
            border-left: 5px solid #007bff;
            padding-left: 15px;
            margin-bottom: 25px;
        }
    </style>
</head>
<body class="bg-light">

<?php include "includes/navbar.php"; ?>

<div class="container my-5">
    <div class="row">
        <!-- 左側選單 -->
        <div class="col-md-3">
            <div class="list-group shadow-sm mb-4">
                <div class="list-group-item bg-dark text-white fw-bold">
                    <i class="fas fa-list me-2"></i> 規章目錄
                </div>
                <a href="?view=agreement" class="list-group-item list-group-item-action sidebar-link <?php echo ($view == 'agreement') ? 'active' : ''; ?>">
                    <i class="fas fa-file-contract me-2"></i> 住宿規範
                </a>
                <a href="?view=penalty" class="list-group-item list-group-item-action sidebar-link <?php echo ($view == 'penalty') ? 'active' : ''; ?>">
                    <i class="fas fa-exclamation-circle me-2"></i> 違規內容 (扣點)
                </a>
            </div>
            
            
        </div>

        <!-- 右側內容區 -->
        <div class="col-md-9">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="page-header">
                        <h3 class="mb-0"><?php echo $title; ?></h3>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">條例編號</th>
                                    <th>條例內容</th>
                                    <?php if ($is_penalty): ?>
                                        <th style="width: 15%;">扣點分值</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result && $result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td class='text-center fw-bold'>" . htmlspecialchars($row["article_no"]) . "</td>";
                                        echo "<td>" . nl2br(htmlspecialchars($row["content"])) . "</td>";
                                        
                                        if ($is_penalty) {
                                            echo "<td class='text-center text-danger fw-bold'>" . htmlspecialchars($row["points"]) . "</td>";
                                        }
                                        echo "</tr>";
                                    }
                                } else {
                                    $col_span = $is_penalty ? 3 : 2;
                                    echo "<tr><td colspan='$col_span' class='text-center text-muted py-4'>暫無資料</td></tr>";
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

<?php 
if (isset($conn)) { $conn->close(); }
include "includes/footer.php"; 
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>