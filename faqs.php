<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>FAQ's - RoseClay</title>
    <?php include 'includes/head.php'; ?>
    <style>
        .faq-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .faq-category {
            margin-bottom: 3rem;
        }

        .faq-category h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: #2c2c2c;
        }

        .faq-item {
            background: white;
            border-radius: 12px;
            margin-bottom: 1rem;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .faq-question {
            width: 100%;
            padding: 1.5rem;
            background: white;
            border: none;
            text-align: left;
            font-size: 1.125rem;
            font-weight: 600;
            color: #2c2c2c;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: 0.3s;
        }

        .faq-question:hover {
            background: #FFF5F8;
        }

        .faq-question i {
            color: #F790B2;
            transition: transform 0.3s;
        }

        .faq-question.active i {
            transform: rotate(180deg);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .faq-answer-content {
            padding: 0 1.5rem 1.5rem;
            color: #666;
            line-height: 1.8;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>FAQ's</span>
        </div>
    </div>

    <!-- FAQ Section -->
    <section class="auth-section">
        <div class="container">
            <div class="section-header">
                <h1>Frequently Asked Questions</h1>
                <p>Find answers to common questions about our products and services</p>
            </div>

            <div class="faq-container">
                <!-- Orders & Shipping -->
                <div class="faq-category">
                    <h2><i class="fas fa-shipping-fast" style="color: #F790B2; margin-right: 0.5rem;"></i> Orders &
                        Shipping</h2>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>How long does shipping take?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                Standard shipping typically takes 5-7 business days. Express shipping (2-3 days) and
                                overnight shipping options are also available at checkout. International orders may take
                                10-15 business days depending on the destination.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>Do you offer free shipping?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                Yes! We offer free standard shipping on all orders over $50 within the United States.
                                International shipping rates vary by location.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>Can I track my order?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                Absolutely! Once your order ships, you'll receive a tracking number via email. You can
                                also track your order by logging into your account and visiting the "My Orders" page.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <div class="faq-category">
                    <h2><i class="fas fa-box" style="color: #F790B2; margin-right: 0.5rem;"></i> Products</h2>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>Are your products handmade?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                Yes! All our clay products are handcrafted by skilled artisans. Each piece is unique and
                                may have slight variations, which adds to their charm and authenticity.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>Are the products food-safe?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                Our dinnerware, bowls, mugs, and cookware are made with food-safe glazes and materials.
                                Each product description specifies whether it's food-safe, dishwasher-safe, and
                                microwave-safe.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>How do I care for my clay products?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                Most of our products are dishwasher-safe, but we recommend hand washing to preserve
                                their beauty. Avoid sudden temperature changes and don't use abrasive cleaners. Detailed
                                care instructions come with each product.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Returns & Refunds -->
                <div class="faq-category">
                    <h2><i class="fas fa-undo" style="color: #F790B2; margin-right: 0.5rem;"></i> Returns & Refunds</h2>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>What is your return policy?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                We accept returns within 30 days of delivery. Items must be unused and in their original
                                packaging. Handmade items may have slight variations and are not considered defects.
                                Contact our support team to initiate a return.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>How long do refunds take?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                Once we receive your return, we'll process your refund within 5-7 business days. The
                                refund will be credited to your original payment method. Please allow an additional 3-5
                                business days for the credit to appear in your account.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>What if my item arrives damaged?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                We take great care in packaging our products, but if your item arrives damaged, please
                                contact us within 48 hours with photos. We'll arrange for a replacement or full refund
                                at no cost to you.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account & Payment -->
                <div class="faq-category">
                    <h2><i class="fas fa-user" style="color: #F790B2; margin-right: 0.5rem;"></i> Account & Payment</h2>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>Do I need an account to place an order?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                No, you can checkout as a guest. However, creating an account allows you to track
                                orders, save addresses, maintain a wishlist, and earn reward points.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span>What payment methods do you accept?</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and Cash
                                on Delivery (COD) for select locations. All transactions are secured with SSL
                                encryption.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Still have questions? -->
            <div
                style="text-align: center; margin-top: 4rem; padding: 3rem; background: linear-gradient(135deg, #F790B2 0%, #F8B0C8 100%); border-radius: 16px; color: white;">
                <h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 1rem;">Still Have
                    Questions?</h2>
                <p style="font-size: 1.125rem; margin-bottom: 2rem; opacity: 0.95;">Our customer support team is here to
                    help!</p>
                <a href="contact.php" class="btn"
                    style="background: white; color: #F790B2; padding: 1rem 2rem; border-radius: 12px; text-decoration: none; font-weight: 600; display: inline-block;">Contact
                    Us</a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
    <script>
        function toggleFaq(button) {
            const answer = button.nextElementSibling;
            const isActive = button.classList.contains('active');

            // Close all FAQs
            document.querySelectorAll('.faq-question').forEach(q => {
                q.classList.remove('active');
                q.nextElementSibling.style.maxHeight = null;
            });

            // Open clicked FAQ if it wasn't active
            if (!isActive) {
                button.classList.add('active');
                answer.style.maxHeight = answer.scrollHeight + 'px';
            }
        }
    </script>
</body>

</html>
