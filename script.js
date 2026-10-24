// ========================================
// Mobile Menu Toggle
// ========================================
const mobileMenuToggle = document.getElementById('mobileMenuBtn');
const navMenu = document.getElementById('navMenu');

if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        const icon = mobileMenuToggle.querySelector('i');

        if (navMenu.classList.contains('active')) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
        } else {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!navMenu.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
            navMenu.classList.remove('active');
            const icon = mobileMenuToggle.querySelector('i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    });

    // Close menu when clicking on a link
    const navLinks = navMenu.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            const icon = mobileMenuToggle.querySelector('i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        });
    });
}

// ========================================
// Sticky Header on Scroll
// ========================================
const header = document.querySelector('.header');
let lastScroll = 0;

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;

    if (currentScroll > 100) {
        header.style.boxShadow = '0 4px 16px rgba(0, 0, 0, 0.1)';
    } else {
        header.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.08)';
    }

    lastScroll = currentScroll;
});

// ========================================
// Countdown Timer
// ========================================
function updateCountdown() {
    // Set target date (45 days from now)
    const targetDate = new Date();
    targetDate.setDate(targetDate.getDate() + 45);

    function update() {
        const now = new Date().getTime();
        const distance = targetDate - now;

        if (distance < 0) {
            // Reset to 45 days if countdown ends
            targetDate.setDate(targetDate.getDate() + 45);
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        const daysEl = document.getElementById('days');
        const hoursEl = document.getElementById('hours');
        const minutesEl = document.getElementById('minutes');
        const secondsEl = document.getElementById('seconds');

        if (daysEl) daysEl.textContent = days.toString().padStart(2, '0');
        if (hoursEl) hoursEl.textContent = hours.toString().padStart(2, '0');
        if (minutesEl) minutesEl.textContent = minutes.toString().padStart(2, '0');
        if (secondsEl) secondsEl.textContent = seconds.toString().padStart(2, '0');
    }

    update();
    setInterval(update, 1000);
}

updateCountdown();

// ========================================
// Hero Slideshow
// ========================================
function initHeroSlideshow() {
    const slides = document.querySelectorAll('.hero-slide');
    if (slides.length === 0) return;

    let currentSlide = 0;
    const slideInterval = 4000; // 4 seconds

    setInterval(() => {
        // Remove active class from current slide
        slides[currentSlide].classList.remove('active');

        // Calculate next slide index
        currentSlide = (currentSlide + 1) % slides.length;

        // Add active class to next slide
        slides[currentSlide].classList.add('active');
    }, slideInterval);
}

// Inialize slideshow when DOM content is loaded
document.addEventListener('DOMContentLoaded', () => {
    initHeroSlideshow();
});

// ========================================
// Currency Conversion
// ========================================
// ========================================
// Currency Conversion
// ========================================
const currencyRates = {
    INR: 1,      // Base currency
    USD: 0.012   // 1 INR = ~0.012 USD (1/83)
};

let currentCurrency = localStorage.getItem('preferredCurrency') || 'INR'; // Default to INR or saved preference

// Format price based on currency
function formatPrice(amount, currency) {
    const symbols = {
        USD: '$',
        INR: '₹'
    };

    const formattedAmount = currency === 'INR'
        ? Math.round(amount).toLocaleString('en-IN')
        : amount.toFixed(2);

    return `${symbols[currency]}${formattedAmount}`;
}

// Convert price from INR (Base) to target currency
function convertPrice(inrPrice, targetCurrency) {
    return inrPrice * currencyRates[targetCurrency];
}

// Extract numeric value from price string
function extractPrice(priceString) {
    return parseFloat(priceString.replace(/[^0-9.]/g, ''));
}

// Update all prices on the page
function updateAllPrices(currency) {
    // Update product prices
    document.querySelectorAll('.price-new, .price-new-large, .variation-price').forEach(priceElement => {
        // Get base price from data attribute or extract
        let basePrice = parseFloat(priceElement.dataset.basePrice);

        if (isNaN(basePrice)) {
            // First run: extract and store base price
            basePrice = extractPrice(priceElement.textContent);
            // If current display is USD (has $), convert back to INR approx if needed, 
            // but ideally we rely on server rendering INR first now.
            // Since we just switched to INR base, existing HTML might be in INR.
            priceElement.dataset.basePrice = basePrice;
        }

        const convertedPrice = convertPrice(basePrice, currency);
        priceElement.textContent = formatPrice(convertedPrice, currency);
    });

    // Update old prices (strikethrough prices)
    document.querySelectorAll('.price-old, .price-old-large').forEach(priceElement => {
        let basePrice = parseFloat(priceElement.dataset.basePrice);

        if (isNaN(basePrice)) {
            basePrice = extractPrice(priceElement.textContent);
            priceElement.dataset.basePrice = basePrice;
        }

        const convertedPrice = convertPrice(basePrice, currency);
        priceElement.textContent = formatPrice(convertedPrice, currency);
    });

    // Update feature section prices (e.g., "On orders over $50")
    document.querySelectorAll('.feature-content p').forEach(element => {
        const text = element.textContent;
        if (text.includes('$50') || text.includes('₹4,150')) {
            const baseAmount = 50; // USD
            const convertedAmount = convertPrice(baseAmount, currency);
            const formattedPrice = formatPrice(convertedAmount, currency);
            element.textContent = `On orders over ${formattedPrice}`;
        }
    });

    // Update newsletter section
    const newsletterHeading = document.querySelector('.newsletter-text h2');
    if (newsletterHeading) {
        const baseAmount = 20; // USD
        const convertedAmount = convertPrice(baseAmount, currency);
        const formattedPrice = formatPrice(convertedAmount, currency);
        newsletterHeading.textContent = `Join Our Newsletter & Get ${formattedPrice} for Your First Order`;
    }
}

// Currency selector change handler
const currencySelect = document.getElementById('currencySelect');
if (currencySelect) {
    currencySelect.addEventListener('change', function () {
        currentCurrency = this.value;
        updateAllPrices(currentCurrency);

        // Store preference in localStorage
        localStorage.setItem('preferredCurrency', currentCurrency);

        // Show toast notification
        const currencyNames = {
            USD: 'US Dollar',
            INR: 'Indian Rupee'
        };
        showToast(`Currency changed to ${currencyNames[currentCurrency]}`, 'success');
    });

    // Load saved currency preference
    const savedCurrency = localStorage.getItem('preferredCurrency');
    if (savedCurrency && currencyRates[savedCurrency]) {
        currentCurrency = savedCurrency;
        currencySelect.value = savedCurrency;
        updateAllPrices(savedCurrency);
    }
}

// ========================================
// Cart & Wishlist Functionality
// ========================================
let cart = [];
let wishlist = []; // Stores product IDs

// Update badge counts
function updateBadges() {
    const cartBadge = document.querySelector('.cart-btn .badge');
    const wishlistBadge = document.querySelector('.wishlist-btn .badge');

    // Cart count is updated by cart.js logic mostly
    if (wishlistBadge) wishlistBadge.textContent = wishlist.length;
}

// Add to Cart
const addToCartButtons = document.querySelectorAll('.btn-cart');
addToCartButtons.forEach(button => {
    button.addEventListener('click', function (e) {
        e.preventDefault();

        const productCard = this.closest('.product-card');
        const productName = productCard.querySelector('h3').textContent;
        const productPrice = productCard.querySelector('.price-new').textContent;

        cart.push({
            name: productName,
            price: productPrice
        });

        updateBadges();
        showToast('Product added to cart!', 'success');

        // Add animation
        this.innerHTML = '<i class="fas fa-check"></i> Added';
        this.style.background = '#27ae60';

        setTimeout(() => {
            this.innerHTML = 'Add to Cart';
            this.style.background = '';
        }, 2000);
    });
});

// Add to Wishlist (Global Function)
window.addToWishlist = function (productId) {
    fetch('api/wishlist-toggle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update local wishlist state
                const id = parseInt(productId);
                const index = wishlist.indexOf(id);

                if (data.action === 'added') {
                    if (index === -1) wishlist.push(id);
                    showToast('Added to wishlist!', 'success');
                } else {
                    if (index > -1) wishlist.splice(index, 1);
                    showToast('Removed from wishlist!', 'info');
                }

                updateBadges();
                updateWishlistIcons();
            } else if (data.code === 'LOGIN_REQUIRED') {
                // Redirect to login or show toast
                if (confirm(data.message + ". Go to login page?")) {
                    window.location.href = 'login.php';
                }
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
};

function updateWishlistIcons() {
    // Select all heart icons inside buttons that call addToWishlist or similar
    // We assume buttons have the onclick calling addToWishlist with an ID
    // A more robust way is to select all potential wishlist buttons
    // For now, we iterate all product cards or details page

    // Logic: Look for elements calling addToWishlist(ID) - hard to select by onclick.
    // Better: Select .btn-icon or .wishlist-icon and check their onclick or data attribute.
    // Assuming standard structure:

    // For Product Cards
    document.querySelectorAll('.product-card').forEach(card => {
        // Find the button (usually first btn-icon in overlay)
        // Or we can rely on the onclick attribute if we can parse it, but that's messy.
        // Let's look for the button that has the onclick with the product ID.
        // Alternatively, we can assume the button is inside .product-overlay
        const btn = card.querySelector('.product-overlay .btn-icon:first-child');

        // This is fragile. Let's try to match by HREF in buttons if they were links, but they are buttons.
        // Best approach: Add data-product-id to the buttons in PHP.
        // But we can't change all PHP right now easily.

        // Let's rely on the onclick attribute containing the ID.
        if (btn) {
            const onclick = btn.getAttribute('onclick');
            if (onclick && onclick.includes('addToWishlist')) {
                // Extract ID
                const match = onclick.match(/addToWishlist\((\d+)\)/);
                if (match) {
                    const pid = parseInt(match[1]);
                    const icon = btn.querySelector('i');
                    if (wishlist.includes(pid)) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        btn.style.background = '#e74c3c';
                        btn.style.color = '#fff';
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        btn.style.background = '';
                        btn.style.color = '';
                    }
                }
            }
        }
    });

    // For Product Details Page
    const detailsBtn = document.querySelector('.product-actions .btn-secondary');
    if (detailsBtn) {
        const onclick = detailsBtn.getAttribute('onclick');
        if (onclick && onclick.includes('addToWishlist')) {
            const match = onclick.match(/addToWishlist\((\d+)\)/);
            if (match) {
                const pid = parseInt(match[1]);
                const icon = detailsBtn.querySelector('i');
                if (wishlist.includes(pid)) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    // detailsBtn usually doesn't change bg color the same way, but let's see design.
                    // Usually just the icon fills.
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }
            }
        }
    }
}

// Fetch wishlist on load
function fetchWishlist() {
    fetch('api/get-wishlist.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && Array.isArray(data.wishlist)) {
                wishlist = data.wishlist;
                updateBadges();
                updateWishlistIcons();
            }
        })
        .catch(error => console.error('Error loading wishlist:', error));
}

// Initial fetch is done in DOMContentLoaded


// ========================================
// Toast Notification
// ========================================
function showToast(message, type = 'info') {
    // Remove existing toast if any
    const existingToast = document.querySelector('.toast');
    if (existingToast) {
        existingToast.remove();
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;

    // Add styles
    toast.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: ${type === 'success' ? '#27ae60' : '#3498db'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
        z-index: 10000;
        animation: slideInRight 0.3s ease;
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ========================================
// Newsletter Form
// ========================================
const newsletterForm = document.querySelector('.newsletter-form');
if (newsletterForm) {
    const newsletterInput = newsletterForm.querySelector('input');
    const newsletterButton = newsletterForm.querySelector('button');

    newsletterButton.addEventListener('click', (e) => {
        e.preventDefault();

        const email = newsletterInput.value.trim();

        if (!email) {
            showToast('Please enter your email address', 'info');
            return;
        }

        if (!isValidEmail(email)) {
            showToast('Please enter a valid email address', 'info');
            return;
        }

        // Simulate subscription
        newsletterButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
        newsletterButton.disabled = true;

        setTimeout(() => {
            showToast('Successfully subscribed to newsletter!', 'success');
            newsletterInput.value = '';
            newsletterButton.innerHTML = 'Subscribe';
            newsletterButton.disabled = false;
        }, 1500);
    });
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// ========================================
// Search Functionality
// ========================================
const searchInput = document.querySelector('.search-box input');
const searchButton = document.querySelector('.search-box button');

if (searchButton) {
    searchButton.addEventListener('click', (e) => {
        e.preventDefault();
        const query = searchInput.value.trim();

        if (query) {
            // showToast(`Searching for "${query}"...`, 'info');
            window.location.href = `products.php?search=${encodeURIComponent(query)}`;
        }
    });
}

if (searchInput) {
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchButton.click();
        }
    });
}

// ========================================
// Smooth Scroll for Navigation Links
// ========================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');

        if (href !== '#' && href.length > 1) {
            e.preventDefault();
            const target = document.querySelector(href);

            if (target) {
                const headerHeight = header.offsetHeight;
                const targetPosition = target.offsetTop - headerHeight - 20;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        }
    });
});

// ========================================
// Product Quick View (placeholder)
// ========================================
const quickViewButtons = document.querySelectorAll('.product-overlay .btn-icon:last-child');
quickViewButtons.forEach(button => {
    button.addEventListener('click', function (e) {
        e.preventDefault();
        const productCard = this.closest('.product-card');
        const productName = productCard.querySelector('h3').textContent;

        showToast(`Quick view for "${productName}" - Feature coming soon!`, 'info');
    });
});

// ========================================
// Intersection Observer for Animations
// ========================================
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe elements for animation
document.querySelectorAll('.product-card, .category-card, .testimonial-card, .feature-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(el);
});

// ========================================
// Category Card Click Handler
// ========================================
const categoryCards = document.querySelectorAll('.category-card');
categoryCards.forEach(card => {
    card.addEventListener('click', function () {
        const categoryName = this.querySelector('h3').textContent;
        showToast(`Browsing ${categoryName}...`, 'info');
        // Here you would implement category filtering
    });
});

// ========================================
// Initialize on Page Load
// ========================================
document.addEventListener('DOMContentLoaded', () => {
    updateBadges();
    fetchWishlist(); // Load wishlist data

    // Add loading animation
    document.body.style.opacity = '0';
    setTimeout(() => {
        document.body.style.transition = 'opacity 0.5s ease';
        document.body.style.opacity = '1';
    }, 100);
});

// ========================================
// Performance: Lazy Loading Images
// ========================================
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            }
        });
    });

    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}

console.log('🎨 RoseClay - Handcrafted Excellence');
console.log('✨ Website loaded successfully!');
