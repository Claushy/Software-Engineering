<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/PHPMailer-master/src/SMTP.php';

function sendVerificationEmail($recipientEmail, $recipientName, $token) {
    $mail = new PHPMailer(true);

    $gmailAddress = 'jcalbiar19@gmail.com';
    $gmailAppPassword = 'kcut azbs jgjc yttz';
    $verificationLink = 'http://localhost/Software-Engineering/verify_email.php?token=' . urlencode($token);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $gmailAddress;
        $mail->Password   = $gmailAppPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($gmailAddress, 'CEA Portal');
        $mail->addAddress($recipientEmail, $recipientName);

        $mail->isHTML(true);
        $mail->Subject = 'Verify your CEA Portal account';
        $mail->Body = "
            <h2>Welcome to CEA Portal</h2>
            <p>Hello <b>" . htmlspecialchars($recipientName) . "</b>,</p>
            <p>Please click the button below to verify your email address:</p>
            <p>
                <a href='{$verificationLink}' style='
                    background:#006a4e;
                    color:#fff;
                    padding:12px 20px;
                    text-decoration:none;
                    border-radius:6px;
                    display:inline-block;
                '>Verify Email</a>
            </p>
            <p>Or copy this link into your browser:</p>
            <p>{$verificationLink}</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
?>
