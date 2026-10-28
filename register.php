<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('my-account.php');
}

$field_errors = [];
$form_values = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => ''
];
$form_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($form_values as $name => $value) {
        $form_values[$name] = trim($_POST[$name] ?? '');
    }

    $first_name = $form_values['first_name'];
    $last_name = $form_values['last_name'];
    $email = $form_values['email'];
    $phone = $form_values['phone'];
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($first_name === '') {
        $field_errors['first_name'] = 'Please enter your first name.';
    }
    if ($last_name === '') {
        $field_errors['last_name'] = 'Please enter your last name.';
    }
    if ($email === '') {
        $field_errors['email'] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $field_errors['email'] = 'Please enter a valid email address.';
    }
    $phone_digits = preg_replace('/\D+/', '', $phone);
    if ($phone === '') {
        $field_errors['phone'] = 'Please enter your phone number.';
    } elseif (!preg_match('/^[+0-9(). -]+$/', $phone) || strlen($phone_digits) < 7 || strlen($phone_digits) > 15) {
        $field_errors['phone'] = 'Please enter a valid phone number.';
    }
    if ($password === '') {
        $field_errors['password'] = 'Please enter a password.';
    } elseif (strlen($password) < 6) {
        $field_errors['password'] = 'Password must be at least 6 characters.';
    }
    if ($confirm_password === '') {
        $field_errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirm_password) {
        $field_errors['confirm_password'] = 'Passwords do not match.';
    }
    if (empty($_POST['terms'])) {
        $field_errors['terms'] = 'Please accept the Terms & Conditions.';
    }

    if (!$field_errors) {
        $check = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $check->bind_param('s', $email);
        $check->execute();
        $email_exists = $check->get_result()->num_rows > 0;
        $check->close();

        if ($email_exists) {
            $field_errors['email'] = 'This email is already registered.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                'INSERT INTO users (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->bind_param('sssss', $first_name, $last_name, $email, $phone, $hashed_password);

            if ($stmt->execute()) {
                $user_id = $conn->insert_id;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $first_name . ' ' . $last_name;
                $_SESSION['user_email'] = $email;
                $_SESSION['is_admin'] = 0;

                redirect('my-account.php');
            } else {
                $form_error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register - RoseClay</title>
    <?php include 'includes/head.php'; ?>
    <style>
        .register-field-error {
            display: block;
            min-height: 1.1em;
            margin-top: 4px;
            color: #e74c3c;
            font-size: 0.75rem;
        }

        .auth-form .form-group input.invalid,
        .register-terms.invalid {
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
                <!-- Register Form -->
                <div class="auth-card">
                    <h1>Create Account</h1>
                    <p>Join RoseClay today</p>

                    <?php if ($form_error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($form_error, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" class="auth-form" id="registerForm" novalidate>
                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="first-name">First Name</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="first-name" name="first_name" placeholder="John" value="<?php echo htmlspecialchars($form_values['first_name'], ENT_QUOTES, 'UTF-8'); ?>" required aria-invalid="<?php echo isset($field_errors['first_name']) ? 'true' : 'false'; ?>" class="<?php echo isset($field_errors['first_name']) ? 'invalid' : ''; ?>">
                                </div>
                                <small class="register-field-error" id="first_name-error"><?php echo htmlspecialchars($field_errors['first_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>
                            <div class="form-group">
                                <label for="last-name">Last Name</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="last-name" name="last_name" placeholder="Doe" value="<?php echo htmlspecialchars($form_values['last_name'], ENT_QUOTES, 'UTF-8'); ?>" required aria-invalid="<?php echo isset($field_errors['last_name']) ? 'true' : 'false'; ?>" class="<?php echo isset($field_errors['last_name']) ? 'invalid' : ''; ?>">
                                </div>
                                <small class="register-field-error" id="last_name-error"><?php echo htmlspecialchars($field_errors['last_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="register-email">Email Address</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="register-email" name="email" placeholder="your@email.com" value="<?php echo htmlspecialchars($form_values['email'], ENT_QUOTES, 'UTF-8'); ?>" required aria-invalid="<?php echo isset($field_errors['email']) ? 'true' : 'false'; ?>" class="<?php echo isset($field_errors['email']) ? 'invalid' : ''; ?>">
                            </div>
                            <small class="register-field-error" id="email-error"><?php echo htmlspecialchars($field_errors['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                        </div>

                        <div class="form-group">
                            <label for="register-phone">Phone Number</label>
                            <div class="input-with-icon">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="register-phone" name="phone" placeholder="+1 (555) 000-0000" value="<?php echo htmlspecialchars($form_values['phone'], ENT_QUOTES, 'UTF-8'); ?>" required aria-invalid="<?php echo isset($field_errors['phone']) ? 'true' : 'false'; ?>" class="<?php echo isset($field_errors['phone']) ? 'invalid' : ''; ?>">
                            </div>
                            <small class="register-field-error" id="phone-error"><?php echo htmlspecialchars($field_errors['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" id="password" placeholder="Minimum 6 characters" required aria-invalid="<?php echo isset($field_errors['password']) ? 'true' : 'false'; ?>" class="<?php echo isset($field_errors['password']) ? 'invalid' : ''; ?>">
                                <button type="button" class="toggle-password" onclick="togglePassword('password')">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <small class="register-field-error" id="password-error"><?php echo htmlspecialchars($field_errors['password'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="confirm_password" id="confirm_password" placeholder="Re-enter password" required aria-invalid="<?php echo isset($field_errors['confirm_password']) ? 'true' : 'false'; ?>" class="<?php echo isset($field_errors['confirm_password']) ? 'invalid' : ''; ?>">
                                <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <small class="register-field-error" id="confirm_password-error"><?php echo htmlspecialchars($field_errors['confirm_password'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                        </div>

                        <label class="checkbox-label">
                            <input type="checkbox" name="terms" class="register-terms" required aria-invalid="<?php echo isset($field_errors['terms']) ? 'true' : 'false'; ?>">
                            <span>I agree to the <a href="#">Terms & Conditions</a></span>
                        </label>
                        <small class="register-field-error" id="terms-error"><?php echo htmlspecialchars($field_errors['terms'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>

                        <button type="submit" class="btn btn-primary btn-large btn-block">
                            <i class="fas fa-user-plus"></i> Create Account
                        </button>



                        <p class="auth-footer">
                            Already have an account? <a href="login.php">Login</a>
                        </p>
                    </form>
                </div>

                <!-- Auth Image -->
                <div class="auth-image">
                    <div class="auth-image-content">
                        <h2>Join Our Community</h2>
                        <p>Experience the best in handcrafted clay products</p>
                        <div class="auth-features">
                            <div class="feature-item">
                                <i class="fas fa-gift"></i>
                                <span>Welcome Discount</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-star"></i>
                                <span>Exclusive Offers</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-shipping-fast"></i>
                                <span>Free Shipping</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-heart"></i>
                                <span>Save Favorites</span>
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
        const registerForm = document.getElementById('registerForm');
        const registerFields = registerForm.querySelectorAll('input[required]');

        function validateRegisterField(field) {
            const errorElement = document.getElementById(field.name + '-error');
            let message = '';

            if (field.type === 'checkbox') {
                if (!field.checked) {
                    message = 'Please accept the Terms & Conditions.';
                }
            } else {
                const value = field.value.trim();
                if (!value) {
                    const messages = {
                        first_name: 'Please enter your first name.',
                        last_name: 'Please enter your last name.',
                        email: 'Please enter your email address.',
                        phone: 'Please enter your phone number.',
                        password: 'Please enter a password.',
                        confirm_password: 'Please confirm your password.'
                    };
                    message = messages[field.name];
                } else if (field.name === 'email' && !field.validity.valid) {
                    message = 'Please enter a valid email address.';
                } else if (field.name === 'phone' &&
                    (!/^[+0-9(). -]+$/.test(value) || (value.match(/\d/g) || []).length < 7 || (value.match(/\d/g) || []).length > 15)) {
                    message = 'Please enter a valid phone number.';
                } else if (field.name === 'password' && value.length < 6) {
                    message = 'Password must be at least 6 characters.';
                } else if (field.name === 'confirm_password' && value !== document.getElementById('password').value) {
                    message = 'Passwords do not match.';
                }
            }

            field.classList.toggle('invalid', Boolean(message));
            field.setAttribute('aria-invalid', String(Boolean(message)));
            if (errorElement) errorElement.textContent = message;
            return message === '';
        }

        registerFields.forEach(field => {
            field.addEventListener(field.type === 'checkbox' ? 'change' : 'blur', () => validateRegisterField(field));
            field.addEventListener('input', () => {
                if (field.classList.contains('invalid')) validateRegisterField(field);
                if (field.name === 'password') {
                    const confirmField = document.getElementById('confirm_password');
                    if (confirmField.value) validateRegisterField(confirmField);
                }
            });
        });

        registerForm.addEventListener('submit', event => {
            let isValid = true;
            registerFields.forEach(field => {
                if (!validateRegisterField(field)) isValid = false;
            });
            if (!isValid) {
                event.preventDefault();
                registerForm.querySelector('.invalid')?.focus();
            }
        });

        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const button = field.parentElement.querySelector('.toggle-password');
            const icon = button.querySelector('i');
            
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
