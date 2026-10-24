<?php
session_start();
require_once 'config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    $sql = "INSERT INTO contact_messages (first_name, last_name, email, phone, subject, message, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'new')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $first_name, $last_name, $email, $phone, $subject, $message);
    
    if ($stmt->execute()) {
        $success = 'Thank you for contacting us! We will get back to you soon.';
    } else {
        $error = 'Failed to send message. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Contact Us - RoseClay</title>
    <?php include 'includes/head.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>Contact Us</span>
        </div>
    </div>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="section-header">
                <h1>Get In Touch</h1>
                <p>We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
            </div>

            <div class="contact-layout">
                <!-- Contact Form -->
                <div class="contact-form-container">
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

                    <form method="POST" class="contact-form">
                        <div class="form-row-2">
                            <div class="form-group">
                                <label>First Name *</label>
                                <input type="text" name="first_name" required>
                            </div>
                            <div class="form-group">
                                <label>Last Name *</label>
                                <input type="text" name="last_name" required>
                            </div>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label>Email Address *</label>
                                <input type="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Subject *</label>
                            <input type="text" name="subject" required>
                        </div>

                        <div class="form-group">
                            <label>Message *</label>
                            <textarea name="message" rows="6" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>

                <!-- Contact Info -->
                <div class="contact-info-container">
                    <div class="contact-info-card">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-card-details">
                            <h3>Visit Us</h3>
                            <p>3-499, KOTHAPALLE VILLAGE, GAMPALAGUDEM MANDAL, NTR DIST
 ANDHRA PRADESH 521 403
</p>
                        </div>
                    </div>

                    <div class="contact-info-card">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-card-details">
                            <h3>Call Us</h3>
                            <p>+1 (555) 123-4567<br>Mon - Sat: 9AM - 6PM</p>
                        </div>
                    </div>

                    <div class="contact-info-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-card-details">
                            <h3>Email Us</h3>
                            <p>info@roseclay.com<br>support@roseclay.com</p>
                        </div>
                    </div>

                    <div class="contact-info-card">
                        <div class="contact-icon">
                            <i class="fas fa-share-alt"></i>
                        </div>
                        <div class="contact-card-details">
                            <h3>Follow Us</h3>
                            <div class="social-links" style="margin-top: 0.5rem;">
                                <a href="https://www.facebook.com/RoseclayIndia" target="_blank"><i class="fab fa-facebook-f"></i></a>
                                <a href="https://www.instagram.com/roseclayindia/" target="_blank"><i class="fab fa-instagram"></i></a>
                                <a href="https://in.pinterest.com/pin/308567011978693859/" target="_blank"><i class="fab fa-pinterest"></i></a>
                              
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <div class="map-container">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3305.733619364129!2d-118.24368368478658!3d34.05223908060709!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80c2c75ddc27da13%3A0xe22fdf6f254608f4!2sLos%20Angeles%2C%20CA%2C%20USA!5e0!3m2!1sen!2s!4v1234567890123!5m2!1sen!2s" 
                    width="100%" 
                    height="450" 
                    style="border:0; border-radius: 12px;" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
