<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Terms & Conditions - RoseClay</title>
    <?php include 'includes/head.php'; ?>
    <style>
        .legal-content {
            background: #fff;
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 3rem;
        }
        .legal-content h2 {
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }
        .legal-content p {
            margin-bottom: 1rem;
            color: var(--text-light);
        }
        .section-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-top: 2rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>Terms & Conditions</span>
        </div>
    </div>

    <section>
        <div class="container">
            <div class="section-header">
                <h1 class="section-title">Terms & Conditions</h1>
                <p>Last updated: <?php echo date('F d, Y'); ?></p>
            </div>

            <div class="legal-content">
                <h2>1. Introduction</h2>
                <p>Welcome to RoseClay. these Terms & Conditions govern your use of our website and the purchase of our products. By accessing or using our website, you agree to be bound by these terms.</p>

                <h2>2. Products and Services</h2>
                <p>We make every effort to display as accurately as possible the colors and images of our products that appear at the store. We cannot guarantee that your computer monitor's display of any color will be accurate. All descriptions of products or product pricing are subject to change at anytime without notice.</p>

                <h2>3. Pricing and Payment</h2>
                <p>Prices for our products are subject to change without notice. We reserve the right at any time to modify or discontinue the Service (or any part or content thereof) without notice at any time.</p>

                <h2>4. Shipping and Delivery</h2>
                <p>We reserve the right to refuse any order you place with us. We may, in our sole discretion, limit or cancel quantities purchased per person, per household or per order. In the event that we make a change to or cancel an order, we may attempt to notify you by contacting the e-mail and/or billing address/phone number provided at the time the order was made.</p>

                <h2>5. User Accounts</h2>
                <p>If you create an account on our website, you are responsible for maintaining the security of your account and you are fully responsible for all activities that occur under the account and any other actions taken in connection with it.</p>

                <h2>6. Changes to Terms</h2>
                <p>You can review the most current version of the Terms & Conditions at any time at this page. We reserve the right, at our sole discretion, to update, change or replace any part of these Terms & Conditions by posting updates and changes to our website.</p>
                
                <h2>7. Contact Information</h2>
                <p>Questions about the Terms & Conditions should be sent to us at info@roseclay.com.</p>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
