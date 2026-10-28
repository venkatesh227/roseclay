<?php
session_start();
require_once 'config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isLoggedIn()) {
    redirect('my-account.php');
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user) {
            $otp = (string) random_int(100000, 999999);
            $otp_hash = hash('sha256', $otp);
            $expires_at = date('Y-m-d H:i:s', time() + 600);

            $delete = $conn->prepare('DELETE FROM password_resets WHERE user_id = ?');
            $delete->bind_param('i', $user['id']);
            $delete->execute();
            $delete->close();

            $insert = $conn->prepare(
                'INSERT INTO password_resets (user_id, token_hash, attempts, expires_at) VALUES (?, ?, 0, ?)'
            );
            $insert->bind_param('iss', $user['id'], $otp_hash, $expires_at);
            $insert->execute();
            $insert->close();

            $subject = 'Your RoseClay password reset OTP';
            $body = "Your RoseClay password reset OTP is: " . $otp . "\n\n"
                . "This code expires in 10 minutes and can be used only once.\n"
                . "If you did not request this, you can ignore this email.";
            // $sender_email = trim((string) ini_get('sendmail_from'));

            // if (!filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
            //     error_log('RoseClay password reset email has no valid configured sender address.');
            //     $mail_sent = false;
            // } else {
            //     $headers = "From: RoseClay <" . $sender_email . ">\r\n"
            //         . "Content-Type: text/plain; charset=UTF-8";
            //     $mail_sent = mail($email, $subject, $body, $headers);
            // }

           
$mail_sent = false;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;

    // Replace with your Gmail address
    $mail->Username   = 'rohiniammisetty1@gmail.com';

    // Enter your NEW Google App Password (no spaces)
    $mail->Password   = 'flcb gsqh vrcc yhqy';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->Timeout    = 20;

    $mail->CharSet = 'UTF-8';
    $mail->setFrom($mail->Username, 'RoseClay');
    $mail->addAddress($email);

    $mail->Subject = $subject;
    $mail->Body    = $body;
    $mail->isHTML(false);

    $mail->send();
    $mail_sent = true;

} catch (\Throwable $e) {
    error_log('RoseClay SMTP error: ' . $e->getMessage());
}


            if (!$mail_sent) {
                error_log('RoseClay password reset email could not be sent.');
                $delete = $conn->prepare('DELETE FROM password_resets WHERE user_id = ?');
                $delete->bind_param('i', $user['id']);
                $delete->execute();
                $error = 'OTP email could not be sent. Please check the mail server configuration or contact support.';
            } else {
                $message = 'If an account exists for that email, a 6-digit OTP has been sent. It expires in 10 minutes.';
            }
        } else {
            $message = 'If an account exists for that email, a 6-digit OTP has been sent. It expires in 10 minutes.';
        }
    }
}

$page_title = 'Forgot Password';
include 'includes/head.php';
?>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="auth-section">
        <div class="container">
            <div class="auth-container auth-single-column">
                <div class="auth-card">
                    <h1>Forgot Password?</h1>
                    <p>Enter your account email and we will send you a password reset OTP.</p>

                    <?php if ($message): ?>
                        <div class="password-reset-notice success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="password-reset-notice error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" class="auth-form">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input id="email" type="email" name="email" placeholder="your@email.com" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-large btn-block">Send OTP</button>
                    </form>

                    <?php if ($message): ?>
                        <p class="auth-footer"><a href="reset-password.php">Enter OTP and reset password</a></p>
                    <?php endif; ?>
                    <p class="auth-footer"><a href="login.php">Back to Login</a></p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
