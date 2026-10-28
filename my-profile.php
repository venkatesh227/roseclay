<?php
session_start();
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

$success = '';
$error = '';
$profile_errors = [];
$profile_values = [
    'first_name' => $user['first_name'] ?? '',
    'last_name' => $user['last_name'] ?? '',
    'email' => $user['email'] ?? '',
    'phone' => $user['phone'] ?? '',
    'date_of_birth' => $user['date_of_birth'] ?? ''
];
$password_errors = [];
$password_success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (($_POST['form_action'] ?? '') === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($current_password === '') {
            $password_errors['current_password'] = 'Please enter your current password.';
        } elseif (!password_verify($current_password, $user['password'])) {
            $password_errors['current_password'] = 'Current password is incorrect.';
        }

        if ($new_password === '') {
            $password_errors['new_password'] = 'Please enter a new password.';
        } elseif (strlen($new_password) < 8) {
            $password_errors['new_password'] = 'New password must be at least 8 characters.';
        } elseif (password_verify($new_password, $user['password'])) {
            $password_errors['new_password'] = 'New password must be different from your current password.';
        }

        if ($confirm_password === '') {
            $password_errors['confirm_password'] = 'Please confirm your new password.';
        } elseif ($new_password !== $confirm_password) {
            $password_errors['confirm_password'] = 'Passwords do not match.';
        }

        if (!$password_errors) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->bind_param('si', $password_hash, $user_id);

            if ($stmt->execute()) {
                $password_success = 'Password updated successfully.';
                $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
            } else {
                $error = 'Unable to update your password. Please try again.';
            }
        }
    } elseif (($_POST['form_action'] ?? '') === 'update_profile') {
        foreach ($profile_values as $name => $value) {
            $profile_values[$name] = trim($_POST[$name] ?? '');
        }

        $first_name = $profile_values['first_name'];
        $last_name = $profile_values['last_name'];
        $email = $profile_values['email'];
        $phone = $profile_values['phone'];
        $date_of_birth = $profile_values['date_of_birth'];

        if ($first_name === '') {
            $profile_errors['first_name'] = 'Please enter your first name.';
        }
        if ($last_name === '') {
            $profile_errors['last_name'] = 'Please enter your last name.';
        }
        if ($email === '') {
            $profile_errors['email'] = 'Please enter your email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $profile_errors['email'] = 'Please enter a valid email address.';
        } else {
            $check = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
            $check->bind_param('si', $email, $user_id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $profile_errors['email'] = 'This email address is already in use.';
            }
            $check->close();
        }

        $phone_digits = preg_replace('/\D+/', '', $phone);
        if ($phone === '') {
            $profile_errors['phone'] = 'Please enter your phone number.';
        } elseif (!preg_match('/^[+0-9(). -]+$/', $phone) || strlen($phone_digits) < 7 || strlen($phone_digits) > 15) {
            $profile_errors['phone'] = 'Please enter a valid phone number.';
        }

        if ($date_of_birth === '') {
            $profile_errors['date_of_birth'] = 'Please enter your date of birth.';
        } else {
            $date = DateTime::createFromFormat('!Y-m-d', $date_of_birth);
            if (!$date || $date->format('Y-m-d') !== $date_of_birth) {
                $profile_errors['date_of_birth'] = 'Please enter a valid date of birth.';
            } elseif ($date > new DateTime('today')) {
                $profile_errors['date_of_birth'] = 'Date of birth cannot be in the future.';
            }
        }

        if (!$profile_errors) {
            $sql = 'UPDATE users SET first_name=?, last_name=?, email=?, phone=?, date_of_birth=? WHERE id=?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssssi', $first_name, $last_name, $email, $phone, $date_of_birth, $user_id);

            if ($stmt->execute()) {
                $_SESSION['user_name'] = $first_name . ' ' . $last_name;
                $_SESSION['user_email'] = $email;
                $success = 'Personal information updated successfully!';
                $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
                $profile_values = [
                    'first_name' => $user['first_name'] ?? '',
                    'last_name' => $user['last_name'] ?? '',
                    'email' => $user['email'] ?? '',
                    'phone' => $user['phone'] ?? '',
                    'date_of_birth' => $user['date_of_birth'] ?? ''
                ];
            } else {
                $error = 'Unable to update your personal information. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - RoseClay</title>
    <?php include 'includes/head.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <a href="my-account.php">My Account</a>
            <i class="fas fa-chevron-right"></i>
            <span>Edit Profile</span>
        </div>
    </div>

    <!-- Account Section -->
    <section class="account-section">
        <div class="container">
            <div class="account-layout">
                <!-- Sidebar -->
                <aside class="account-sidebar">
                    <div class="account-user">
                        <div class="account-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h3>
                        <p><?php echo $user['email']; ?></p>
                    </div>
                    <nav class="account-nav">
                        <a href="my-account.php">
                            <i class="fas fa-th-large"></i> Dashboard
                        </a>
                        <a href="my-orders.php">
                            <i class="fas fa-shopping-bag"></i> My Orders
                        </a>
                        <a href="my-profile.php" class="active">
                            <i class="fas fa-user-edit"></i> Edit Profile
                        </a>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </nav>
                </aside>

                <!-- Main Content -->
                <div class="account-main">
                    <h1>Edit Profile</h1>
                    <p>Update your account information</p>

                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Profile Form -->
                    <div class="account-section-box">
                        <h2>Personal Information</h2>
                        <form method="POST" class="profile-form" id="personalInformationForm" novalidate>
                            <input type="hidden" name="form_action" value="update_profile">
                            <div class="form-row-2">
                                <div class="form-group">
                                    <label for="profile-first-name">First Name <span class="required">*</span></label>
                                    <input type="text" id="profile-first-name" name="first_name" value="<?php echo htmlspecialchars($profile_values['first_name'], ENT_QUOTES, 'UTF-8'); ?>" required aria-invalid="<?php echo isset($profile_errors['first_name']) ? 'true' : 'false'; ?>" class="<?php echo isset($profile_errors['first_name']) ? 'invalid' : ''; ?>">
                                    <small class="profile-field-error"><?php echo htmlspecialchars($profile_errors['first_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                                </div>
                                <div class="form-group">
                                    <label for="profile-last-name">Last Name <span class="required">*</span></label>
                                    <input type="text" id="profile-last-name" name="last_name" value="<?php echo htmlspecialchars($profile_values['last_name'], ENT_QUOTES, 'UTF-8'); ?>" required aria-invalid="<?php echo isset($profile_errors['last_name']) ? 'true' : 'false'; ?>" class="<?php echo isset($profile_errors['last_name']) ? 'invalid' : ''; ?>">
                                    <small class="profile-field-error"><?php echo htmlspecialchars($profile_errors['last_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="profile-email">Email Address <span class="required">*</span></label>
                                <input type="email" id="profile-email" name="email" value="<?php echo htmlspecialchars($profile_values['email'], ENT_QUOTES, 'UTF-8'); ?>" required aria-invalid="<?php echo isset($profile_errors['email']) ? 'true' : 'false'; ?>" class="<?php echo isset($profile_errors['email']) ? 'invalid' : ''; ?>">
                                <small class="profile-field-error"><?php echo htmlspecialchars($profile_errors['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>

                            <div class="form-group">
                                <label for="profile-phone">Phone Number <span class="required">*</span></label>
                                <input type="tel" id="profile-phone" name="phone" value="<?php echo htmlspecialchars($profile_values['phone'], ENT_QUOTES, 'UTF-8'); ?>" required aria-invalid="<?php echo isset($profile_errors['phone']) ? 'true' : 'false'; ?>" class="<?php echo isset($profile_errors['phone']) ? 'invalid' : ''; ?>">
                                <small class="profile-field-error"><?php echo htmlspecialchars($profile_errors['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>

                            <div class="form-group">
                                <label for="profile-date-of-birth">Date of Birth <span class="required">*</span></label>
                                <input type="date" id="profile-date-of-birth" name="date_of_birth" value="<?php echo htmlspecialchars($profile_values['date_of_birth'], ENT_QUOTES, 'UTF-8'); ?>" max="<?php echo date('Y-m-d'); ?>" required aria-invalid="<?php echo isset($profile_errors['date_of_birth']) ? 'true' : 'false'; ?>" class="<?php echo isset($profile_errors['date_of_birth']) ? 'invalid' : ''; ?>">
                                <small class="profile-field-error"><?php echo htmlspecialchars($profile_errors['date_of_birth'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </form>
                    </div>

                    <!-- Change Password -->
                    <div class="account-section-box">
                        <h2>Change Password</h2>
                        <?php if ($password_success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($password_success, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <?php endif; ?>

                        <form method="POST" class="profile-form" id="changePasswordForm" novalidate>
                            <input type="hidden" name="form_action" value="change_password">
                            <div class="form-group">
                                <label for="current-password">Current Password<span class="required">*</span></label>
                                <input type="password" id="current-password" name="current_password" required aria-invalid="<?php echo isset($password_errors['current_password']) ? 'true' : 'false'; ?>" class="<?php echo isset($password_errors['current_password']) ? 'invalid' : ''; ?>">
                                <small class="password-field-error"><?php echo htmlspecialchars($password_errors['current_password'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>

                            <div class="form-group">
                                <label for="new-password">New Password<span class="required">*</span></label>
                                <input type="password" id="new-password" name="new_password" minlength="8" required aria-invalid="<?php echo isset($password_errors['new_password']) ? 'true' : 'false'; ?>" class="<?php echo isset($password_errors['new_password']) ? 'invalid' : ''; ?>">
                                <small class="password-field-error"><?php echo htmlspecialchars($password_errors['new_password'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>

                            <div class="form-group">
                                <label for="confirm-new-password">Confirm New Password<span class="required">*</span></label>
                                <input type="password" id="confirm-new-password" name="confirm_password" required aria-invalid="<?php echo isset($password_errors['confirm_password']) ? 'true' : 'false'; ?>" class="<?php echo isset($password_errors['confirm_password']) ? 'invalid' : ''; ?>">
                                <small class="password-field-error"><?php echo htmlspecialchars($password_errors['confirm_password'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>

                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-key"></i> Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
    <style>
        .required {
            color: #e74c3c;
        }

        .profile-field-error,
        .password-field-error {
            display: block;
            min-height: 1.1em;
            margin-top: 4px;
            color: #e74c3c;
            font-size: 0.75rem;
        }

        #personalInformationForm input.invalid,
        #changePasswordForm input.invalid {
            border-color: #e74c3c;
        }
    </style>
    <script>
        const personalInformationForm = document.getElementById('personalInformationForm');
        const profileFields = Array.from(personalInformationForm.querySelectorAll('input:not([type="hidden"])'));

        function validateProfileField(field) {
            const value = field.value.trim();
            const errorElement = field.nextElementSibling;
            let message = '';

            if (!value) {
                const messages = {
                    first_name: 'Please enter your first name.',
                    last_name: 'Please enter your last name.',
                    email: 'Please enter your email address.',
                    phone: 'Please enter your phone number.',
                    date_of_birth: 'Please enter your date of birth.'
                };
                message = messages[field.name];
            } else if (field.name === 'email' && !field.validity.valid) {
                message = 'Please enter a valid email address.';
            } else if (field.name === 'phone') {
                const digits = value.replace(/\D/g, '');
                if (!/^[+0-9(). -]+$/.test(value) || digits.length < 7 || digits.length > 15) {
                    message = 'Please enter a valid phone number.';
                }
            } else if (field.name === 'date_of_birth' && field.validity.rangeOverflow) {
                message = 'Date of birth cannot be in the future.';
            }

            field.classList.toggle('invalid', Boolean(message));
            field.setAttribute('aria-invalid', String(Boolean(message)));
            errorElement.textContent = message;
            return message === '';
        }

        profileFields.forEach(field => {
            field.addEventListener('blur', () => validateProfileField(field));
            field.addEventListener('input', () => {
                if (field.classList.contains('invalid')) validateProfileField(field);
            });
            field.addEventListener('change', () => validateProfileField(field));
        });

        personalInformationForm.addEventListener('submit', event => {
            const valid = profileFields.map(validateProfileField).every(Boolean);
            if (!valid) {
                event.preventDefault();
                personalInformationForm.querySelector('.invalid')?.focus();
            }
        });

        const changePasswordForm = document.getElementById('changePasswordForm');
        const currentPasswordField = document.getElementById('current-password');
        const newPasswordField = document.getElementById('new-password');
        const confirmNewPasswordField = document.getElementById('confirm-new-password');

        function validatePasswordField(field) {
            const errorElement = field.nextElementSibling;
            const value = field.value;
            let message = '';

            if (!value) {
                const messages = {
                    current_password: 'Please enter your current password.',
                    new_password: 'Please enter a new password.',
                    confirm_password: 'Please confirm your new password.'
                };
                message = messages[field.name];
            } else if (field.name === 'new_password' && value.length < 8) {
                message = 'New password must be at least 8 characters.';
            } else if (field.name === 'new_password' && value === currentPasswordField.value) {
                message = 'New password must be different from your current password.';
            } else if (field.name === 'confirm_password' && value !== newPasswordField.value) {
                message = 'Passwords do not match.';
            }

            field.classList.toggle('invalid', Boolean(message));
            field.setAttribute('aria-invalid', String(Boolean(message)));
            errorElement.textContent = message;
            return message === '';
        }

        [currentPasswordField, newPasswordField, confirmNewPasswordField].forEach(field => {
            field.addEventListener('blur', () => validatePasswordField(field));
            field.addEventListener('input', () => {
                if (field.classList.contains('invalid')) validatePasswordField(field);
                if (field.name === 'new_password' && confirmNewPasswordField.value) {
                    validatePasswordField(confirmNewPasswordField);
                }
            });
        });

        changePasswordForm.addEventListener('submit', event => {
            const valid = [currentPasswordField, newPasswordField, confirmNewPasswordField]
                .map(validatePasswordField)
                .every(Boolean);

            if (!valid) {
                event.preventDefault();
                changePasswordForm.querySelector('.invalid')?.focus();
            }
        });
    </script>
</body>
</html>
