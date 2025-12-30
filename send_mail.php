<?php
// 引入路徑
require 'includes/src/Exception.php';
require 'includes/src/PHPMailer.php';
require 'includes/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = new PHPMailer(true);

    try {
        // --- 伺服器設定 ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '你的帳號@gmail.com';  // 修改為你的 Gmail
        $mail->Password   = 'xxxx xxxx xxxx xxxx'; // 修改為 16 位應用程式密碼
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // --- 寄收件人 ---
        $mail->setFrom('你的帳號@gmail.com', '宿舍系統');
        $mail->addAddress('管理員信箱@example.com'); // 你真正想收信的位址
        $mail->addReplyTo($_POST['user_email'], $_POST['user_name']);

        // --- 內容 ---
        $mail->isHTML(true);
        $category = ($_POST['from_view'] == 'penalty') ? "違規扣點相關" : "住宿規範相關";
        $mail->Subject = "規章系統反映: " . $category;
        $mail->Body    = "<h3>收到規章諮詢信件</h3>" .
                         "<p><b>姓名：</b>" . htmlspecialchars($_POST['user_name']) . "</p>" .
                         "<p><b>回傳信箱：</b>" . htmlspecialchars($_POST['user_email']) . "</p>" .
                         "<p><b>訊息內容：</b><br>" . nl2br(htmlspecialchars($_POST['message'])) . "</p>";

        $mail->send();
        echo "<script>alert('郵件已送出！'); location.href='dorm_rules.php?view=" . $_POST['from_view'] . "';</script>";
        
    } catch (Exception $e) {
        echo "<script>alert('發送失敗: {$mail->ErrorInfo}'); history.back();</script>";
    }
}
?>