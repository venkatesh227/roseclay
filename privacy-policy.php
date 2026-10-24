<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Privacy Policy - RoseClay</title>
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
            <span>Privacy Policy</span>
        </div>
    </div>

    <section>
        <div class="container">
            <div class="section-header">
                <h1 class="section-title">Privacy Policy</h1>
                <p>Last updated: <?php echo date('F d, Y'); ?></p>
            </div>

            <div class="legal-content">
                <h2>1. Information We Collect</h2>
                <p>When you visit our site, we automatically collect certain information about your device, including information about your web browser, IP address, time zone, and some of the cookies that are installed on your device. Additionally, as you browse the Site, we collect information about the individual web pages or products that you view, what websites or search terms referred you to the Site, and information about how you interact with the Site.</p>

                <h2>2. How We Use Your Information</h2>
                <p>We use the Order Information that we collect generally to fulfill any orders placed through the Site (including processing your payment information, arranging for shipping, and providing you with invoices and/or order confirmations). Additionally, we use this Order Information to:
Communicate with you;
Screen our orders for potential risk or fraud; and
When in line with the preferences you have shared with us, provide you with information or advertising relating to our products or services.</p>

                <h2>3. Sharing Your Personal Information</h2>
                <p>We share your Personal Information with third parties to help us use your Personal Information, as described above. For example, we use Google Analytics to help us understand how our customers use the Site.</p>

                <h2>4. Data Retention</h2>
                <p>When you place an order through the Site, we will maintain your Order Information for our records unless and until you ask us to delete this information.</p>

                <h2>5. Changes</h2>
                <p>We may update this privacy policy from time to time in order to reflect, for example, changes to our practices or for other operational, legal or regulatory reasons.</p>
                
                <h2>6. Contact Us</h2>
                <p>For more information about our privacy practices, if you have questions, or if you would like to make a complaint, please contact us by e-mail at info@roseclay.com.</p>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
