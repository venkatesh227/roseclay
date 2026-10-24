<?php
session_start();
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $date_of_birth = sanitize($_POST['date_of_birth']);
    
    // Update profile
    $sql = "UPDATE users SET first_name=?, last_name=?, email=?, phone=?, date_of_birth=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $date_of_birth, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['user_name'] = $first_name . ' ' . $last_name;
        $_SESSION['user_email'] = $email;
        $success = 'Profile updated successfully!';
        $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
    } else {
        $error = 'Failed to update profile';
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
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Profile Form -->
                    <div class="account-section-box">
                        <h2>Personal Information</h2>
                        <form method="POST" class="profile-form">
                            <div class="form-row-2">
                                <div class="form-group">
                                    <label>First Name *</label>
                                    <input type="text" name="first_name" value="<?php echo $user['first_name']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Last Name *</label>
                                    <input type="text" name="last_name" value="<?php echo $user['last_name']; ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Email Address *</label>
                                <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" value="<?php echo $user['phone'] ?? ''; ?>">
                            </div>

                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" name="date_of_birth" value="<?php echo $user['date_of_birth'] ?? ''; ?>">
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </form>
                    </div>

                    <!-- Change Password -->
                    <div class="account-section-box">
                        <h2>Change Password</h2>
                        <form class="profile-form">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password">
                            </div>

                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password">
                            </div>

                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password">
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
</body>
</html>
