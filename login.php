<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('my-account.php');
}

$field_errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '') {
        $field_errors['email'] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $field_errors['email'] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $field_errors['password'] = 'Please enter your password.';
    }

    if (!$field_errors) {
        $stmt = $conn->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            $field_errors['email'] = 'No account found with this email.';
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin'];

            if (isset($_SESSION['redirect_url'])) {
                $redirect_url = $_SESSION['redirect_url'];
                unset($_SESSION['redirect_url']);
                redirect($redirect_url);
            } elseif ($user['is_admin']) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['admin_email'] = $user['email'];
                redirect(ADMIN_URL . '/index.php');
            } else {
                redirect('index.php');
            }
        } else {
            $field_errors['password'] = 'Invalid password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - RoseClay</title>
    <?php include 'includes/head.php'; ?>
    <style>
        .login-field-error {
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
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Auth Section -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <!-- Login Form -->
                <div class="auth-card">
                    <h1>Welcome Back</h1>
                    <p>Login to your account</p>

                    <form method="POST" class="auth-form" id="loginForm" novalidate>
                        <div class="form-group">
                            <label for="login-email">Email Address</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input
                                    type="email"
                                    id="login-email"
                                    name="email"
                                    placeholder="your@email.com"
                                    value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                    aria-invalid="<?php echo isset($field_errors['email']) ? 'true' : 'false'; ?>"
                                    class="<?php echo isset($field_errors['email']) ? 'invalid' : ''; ?>"
                                >
                            </div>
                            <small class="login-field-error" id="email-error"><?php echo htmlspecialchars($field_errors['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    placeholder="Enter your password"
                                    required
                                    aria-invalid="<?php echo isset($field_errors['password']) ? 'true' : 'false'; ?>"
                                    class="<?php echo isset($field_errors['password']) ? 'invalid' : ''; ?>"
                                >
                                <button type="button" class="toggle-password" onclick="togglePassword()">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <small class="login-field-error" id="password-error"><?php echo htmlspecialchars($field_errors['password'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                        </div>

                        <div class="form-options">
                            <label class="checkbox-label">
                                <input type="checkbox" name="remember">
                                <span>Remember me</span>
                            </label>
                            <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary btn-large btn-block">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>



                        <p class="auth-footer">
                            Don't have an account? <a href="register.php">Sign up</a>
                        </p>
                    </form>
                </div>

                <!-- Auth Image -->
                <div class="auth-image">
                    <div class="auth-image-content">
                        <h2>Shop with Confidence</h2>
                        <p>Join thousands of happy customers</p>
                        <div class="auth-features">
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Exclusive Deals</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Order Tracking</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Wishlist</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Fast Checkout</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
    <script>
        const loginForm = document.getElementById('loginForm');
        const emailField = document.getElementById('login-email');
        const passwordField = document.getElementById('password');
        let loginSubmitted = false;

        function validateLoginField(field) {
            const errorElement = document.getElementById(field.name + '-error');
            const value = field.value.trim();
            let message = '';

            if (!value) {
                message = field.name === 'email'
                    ? 'Please enter your email address.'
                    : 'Please enter your password.';
            } else if (field.name === 'email' && !field.validity.valid) {
                message = 'Please enter a valid email address.';
            }

            field.classList.toggle('invalid', Boolean(message));
            field.setAttribute('aria-invalid', String(Boolean(message)));
            errorElement.textContent = message;
            return message === '';
        }

        [emailField, passwordField].forEach(field => {
            field.addEventListener('input', () => {
                if (loginSubmitted || field.classList.contains('invalid')) {
                    validateLoginField(field);
                }
            });
        });

        loginForm.addEventListener('submit', event => {
            loginSubmitted = true;
            const emailValid = validateLoginField(emailField);
            const passwordValid = validateLoginField(passwordField);

            if (!emailValid || !passwordValid) {
                event.preventDefault();
                (emailValid ? passwordField : emailField).focus();
            }
        });

        function togglePassword() {
            const field = document.getElementById('password');
            const icon = document.querySelector('.toggle-password i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
