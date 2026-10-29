<?php
session_start();
require_once 'config.php';

if (isLoggedIn()) {
    redirect('my-account.php');
}

$error = '';
$message = '';
$reset = null;
$field_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $otp = trim($_POST['otp'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($email === '') {
        $field_errors['email'] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $field_errors['email'] = 'Please enter a valid email address.';
    }

    if ($otp === '') {
        $field_errors['otp'] = 'Please enter the 6-digit OTP from your email.';
    } elseif (!preg_match('/^[0-9]{6}$/', $otp)) {
        $field_errors['otp'] = 'OTP must contain exactly 6 digits.';
    }

    if ($password === '') {
        $field_errors['password'] = 'Please enter a new password.';
    } elseif (strlen($password) < 8) {
        $field_errors['password'] = 'Password must be at least 8 characters long.';
    }

    if ($confirm_password === '') {
        $field_errors['confirm_password'] = 'Please confirm your new password.';
    } elseif ($password !== $confirm_password) {
        $field_errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!$field_errors) {
       
$user_stmt = $conn->prepare(
    'SELECT id FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1'
);
$user_stmt->bind_param('s', $email);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

if (!$user) {
    $error = 'Invalid OTP or expired. Please request a new OTP.';
} else {
    $reset_stmt = $conn->prepare(
        'SELECT id, user_id, token_hash, attempts, expires_at
         FROM password_resets
         WHERE user_id = ?
         ORDER BY id DESC
         LIMIT 1'
    );
    $reset_stmt->bind_param('i', $user['id']);
    $reset_stmt->execute();
    $reset = $reset_stmt->get_result()->fetch_assoc();
    $reset_stmt->close();

    if (!$reset || strtotime($reset['expires_at']) <= time()) {
        $error = 'The OTP is invalid or expired. Please request a new one.';
    } elseif ((int)$reset['attempts'] >= 5) {
        $delete = $conn->prepare(
            'DELETE FROM password_resets WHERE id = ?'
        );
        $delete->bind_param('i', $reset['id']);
        $delete->execute();
        $delete->close();

        $error = 'Too many incorrect attempts. Please request a new OTP.';
    } elseif (!hash_equals(
        $reset['token_hash'],
        hash('sha256', $otp)
    )) {
        $attempts = (int)$reset['attempts'] + 1;

        if ($attempts >= 5) {
            $delete = $conn->prepare(
                'DELETE FROM password_resets WHERE id = ?'
            );
            $delete->bind_param('i', $reset['id']);
            $delete->execute();
            $delete->close();

            $error = 'Too many incorrect attempts. Please request a new OTP.';
        } else {
            $update_attempts = $conn->prepare(
                'UPDATE password_resets SET attempts = ? WHERE id = ?'
            );
            $update_attempts->bind_param(
                'ii',
                $attempts,
                $reset['id']
            );
            $update_attempts->execute();
            $update_attempts->close();

            $error = 'The OTP is incorrect. Please check your email.';
        }
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $update = $conn->prepare(
            'UPDATE users SET password = ? WHERE id = ?'
        );
        $update->bind_param(
            'si',
            $password_hash,
            $reset['user_id']
        );

        if ($update->execute() && $update->affected_rows === 1) {
            $update->close();

            $delete = $conn->prepare(
                'DELETE FROM password_resets WHERE user_id = ?'
            );
            $delete->bind_param('i', $reset['user_id']);
            $delete->execute();
            $delete->close();

            $message = 'Your password has been reset successfully. You can now log in.';
            $reset = null;
        } else {
            $update->close();
            $error = 'Unable to update your password. Please try again.';
        }
    }
}
}
}

$page_title = 'Reset Password';
include 'includes/head.php';
?>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="auth-section">
        <div class="container">
            <div class="auth-container auth-single-column">
                <div class="auth-card">
                    <h1>Verify OTP &amp; Reset Password</h1>

                    <?php if ($message): ?>
                        <div class="password-reset-notice success"><?php echo htmlspecialchars($message); ?></div>
                        <p class="auth-footer"><a href="login.php">Go to Login</a></p>
                    <?php else: ?>
                        <?php if ($error): ?>
                            <div class="password-reset-notice error"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <form method="POST" class="auth-form" id="resetPasswordForm" novalidate>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required aria-describedby="email-error" aria-invalid="<?php echo isset($field_errors['email']) ? 'true' : 'false'; ?>" class="<?php echo isset($field_errors['email']) ? 'invalid' : ''; ?>">
                                <small class="password-reset-field-error" id="email-error"><?php echo htmlspecialchars($field_errors['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>
                            <div class="form-group">
                                <label for="otp">6-digit OTP</label>
                                <input id="otp" type="text" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required aria-describedby="otp-error" aria-invalid="<?php echo isset($field_errors['otp']) ? 'true' : 'false'; ?>" class="<?php echo isset($field_errors['otp']) ? 'invalid' : ''; ?>">
                                <small class="password-reset-field-error" id="otp-error"><?php echo htmlspecialchars($field_errors['otp'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>
                            <div class="form-group">
                                <label for="password">New Password</label>
                                <input id="password" type="password" name="password" minlength="8" autocomplete="new-password" required aria-describedby="password-error" aria-invalid="<?php echo isset($field_errors['password']) ? 'true' : 'false'; ?>" class="<?php echo isset($field_errors['password']) ? 'invalid' : ''; ?>">
                                <small class="password-reset-field-error" id="password-error"><?php echo htmlspecialchars($field_errors['password'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>
                            <div class="form-group">
                                <label for="confirm-password">Confirm New Password</label>
                                <input id="confirm-password" type="password" name="confirm_password" minlength="8" autocomplete="new-password" required aria-describedby="confirm-password-error" aria-invalid="<?php echo isset($field_errors['confirm_password']) ? 'true' : 'false'; ?>" class="<?php echo isset($field_errors['confirm_password']) ? 'invalid' : ''; ?>">
                                <small class="password-reset-field-error" id="confirm-password-error"><?php echo htmlspecialchars($field_errors['confirm_password'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>
                            <button type="submit" class="btn btn-primary btn-large btn-block">Reset Password</button>
                        </form>
                        <p class="auth-footer"><a href="forgot-password.php">Request a new OTP</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
    <script>
        const resetPasswordForm = document.getElementById('resetPasswordForm');

        if (resetPasswordForm) {
            const resetFields = Array.from(resetPasswordForm.querySelectorAll('input[required]'));
            let resetSubmitted = false;

            function validateResetField(field) {
                const errorElement = document.getElementById(field.id + '-error');
                const value = field.value.trim();
                let message = '';

                if (!value) {
                    message = field.name === 'email'
                        ? 'Please enter your email address.'
                        : field.name === 'otp'
                            ? 'Please enter the 6-digit OTP from your email.'
                            : field.name === 'password'
                                ? 'Please enter a new password.'
                                : 'Please confirm your new password.';
                } else if (field.name === 'email' && !field.validity.valid) {
                    message = 'Please enter a valid email address.';
                } else if (field.name === 'otp' && !/^[0-9]{6}$/.test(value)) {
                    message = 'OTP must contain exactly 6 digits.';
                } else if (field.name === 'password' && field.value.length < 8) {
                    message = 'Password must be at least 8 characters long.';
                } else if (field.name === 'confirm_password' &&
                    field.value !== document.getElementById('password').value) {
                    message = 'Passwords do not match.';
                }

                field.classList.toggle('invalid', Boolean(message));
                field.setAttribute('aria-invalid', String(Boolean(message)));
                if (errorElement) errorElement.textContent = message;
                return message === '';
            }

            resetFields.forEach(field => {
                field.addEventListener('input', () => {
                    if (resetSubmitted || field.classList.contains('invalid')) {
                        validateResetField(field);
                    }
                    if (field.name === 'password') {
                        const confirmField = document.getElementById('confirm-password');
                        if (resetSubmitted || confirmField.classList.contains('invalid')) {
                            validateResetField(confirmField);
                        }
                    }
                });
            });

            resetPasswordForm.addEventListener('submit', event => {
                resetSubmitted = true;
                let isValid = true;
                resetFields.forEach(field => {
                    if (!validateResetField(field)) isValid = false;
                });

                if (!isValid) {
                    event.preventDefault();
                    resetPasswordForm.querySelector('.invalid')?.focus();
                }
            });
        }
    </script>
</body>
</html>