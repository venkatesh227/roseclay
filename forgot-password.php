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
$email_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $email_error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = 'Please enter a valid email address.';
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

    <style>
        .forgot-password-field-error {
            display: block;
            min-height: 1.1em;
            margin-top: 4px;
            color: #e74c3c;
            font-size: 0.75rem;
        }

        .auth-form .form-group input.invalid {
            border-color: #e74c3c;
        }
    </style>

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

                    <form method="POST" class="auth-form" id="forgotPasswordForm" novalidate>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    placeholder="your@email.com"
                                    value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                    aria-invalid="<?php echo $email_error ? 'true' : 'false'; ?>"
                                    class="<?php echo $email_error ? 'invalid' : ''; ?>"
                                >
                            </div>
                            <small class="forgot-password-field-error" id="email-error"><?php echo htmlspecialchars($email_error, ENT_QUOTES, 'UTF-8'); ?></small>
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
    <script>
        const forgotPasswordForm = document.getElementById('forgotPasswordForm');
        const emailField = document.getElementById('email');
        let forgotPasswordSubmitted = false;

        function validateEmailField() {
            const value = emailField.value.trim();
            let message = '';

            if (!value) {
                message = 'Please enter your email address.';
            } else if (!emailField.validity.valid) {
                message = 'Please enter a valid email address.';
            }

            emailField.classList.toggle('invalid', Boolean(message));
            emailField.setAttribute('aria-invalid', String(Boolean(message)));
            document.getElementById('email-error').textContent = message;
            return message === '';
        }

        emailField.addEventListener('input', () => {
            if (forgotPasswordSubmitted || emailField.classList.contains('invalid')) {
                validateEmailField();
            }
        });

        forgotPasswordForm.addEventListener('submit', event => {
            forgotPasswordSubmitted = true;
            if (!validateEmailField()) {
                event.preventDefault();
                emailField.focus();
            }
        });
    </script>
</body>
</html>
