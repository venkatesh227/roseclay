<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Return Policy - RoseClay</title>
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
            <span>Return Policy</span>
        </div>
    </div>

    <section>
        <div class="container">
            <div class="section-header">
                <h1 class="section-title">Return Policy</h1>
                <p>Last updated: <?php echo date('F d, Y'); ?></p>
            </div>

            <div class="legal-content">
                <h2>1. Returns</h2>
                <p>Our policy lasts 30 days. If 30 days have gone by since your purchase, unfortunately we can’t offer you a refund or exchange.</p>
                <p>To be eligible for a return, your item must be unused and in the same condition that you received it. It must also be in the original packaging.</p>

                <h2>2. Refunds (if applicable)</h2>
                <p>Once your return is received and inspected, we will send you an email to notify you that we have received your returned item. We will also notify you of the approval or rejection of your refund.</p>
                <p>If you are approved, then your refund will be processed, and a credit will automatically be applied to your credit card or original method of payment, within a certain amount of days.</p>

                <h2>3. Sale items (if applicable)</h2>
                <p>Only regular priced items may be refunded, unfortunately sale items cannot be refunded.</p>

                <h2>4. Exchanges (if applicable)</h2>
                <p>We only replace items if they are defective or damaged. If you need to exchange it for the same item, send us an email at info@roseclay.com.</p>

                <h2>5. Shipping</h2>
                <p>To return your product, you should mail your product to: Autonagar, Mangalagiri.</p>
                <p>You will be responsible for paying for your own shipping costs for returning your item. Shipping costs are non-refundable. If you receive a refund, the cost of return shipping will be deducted from your refund.</p>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
