<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

.mail-landing {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 0;
    margin: 0;
}

.hero-section {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%);
    padding: 100px 0 80px;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

.hero-section::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
    border-radius: 50%;
    animation: float 8s ease-in-out infinite reverse;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-30px); }
}

.hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: white;
}

.hero-title {
    font-size: 4rem;
    font-weight: 800;
    margin-bottom: 24px;
    background: linear-gradient(45deg, #fff, #e0e7ff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: glow 2s ease-in-out infinite alternate;
}

@keyframes glow {
    from { filter: drop-shadow(0 0 10px rgba(255,255,255,0.3)); }
    to { filter: drop-shadow(0 0 20px rgba(255,255,255,0.6)); }
}

.hero-subtitle {
    font-size: 1.5rem;
    font-weight: 300;
    margin-bottom: 48px;
    color: rgba(255,255,255,0.9);
}

.plans-section {
    background: #f8f9fa;
    padding: 80px 0;
}

.glass-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 24px;
    padding: 40px;
    margin-bottom: 30px;
    border: 1px solid rgba(255,255,255,0.3);
    box-shadow: 0 20px 60px rgba(0,0,0,0.1);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
}

.glass-card::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    transform: rotate(45deg);
    transition: all 0.6s;
}

.glass-card:hover::before {
    left: 100%;
}

.glass-card:hover {
    transform: translateY(-15px) scale(1.02);
    box-shadow: 0 30px 80px rgba(102, 126, 234, 0.3);
}

.glass-card.popular {
    border: 3px solid #ffc107;
    box-shadow: 0 25px 70px rgba(255, 193, 7, 0.4);
}

.popular-badge {
    position: absolute;
    top: 20px;
    right: -35px;
    background: linear-gradient(45deg, #ffc107, #ff9800);
    color: #000;
    padding: 8px 50px;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    transform: rotate(45deg);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
}

.plan-header {
    text-align: center;
    margin-bottom: 32px;
}

.plan-name {
    font-size: 2rem;
    font-weight: 700;
    color: #667eea;
    margin-bottom: 16px;
}

.plan-price {
    font-size: 3.5rem;
    font-weight: 800;
    color: #2d3748;
    margin-bottom: 8px;
}

.plan-price .currency {
    font-size: 2rem;
    vertical-align: super;
}

.plan-period {
    color: #718096;
    font-size: 1rem;
}

.plan-features {
    list-style: none;
    padding: 0;
    margin: 32px 0;
}

.plan-features li {
    padding: 12px 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    font-size: 1.05rem;
    color: #4a5568;
}

.plan-features li:last-child {
    border-bottom: none;
}

.plan-features li i {
    color: #48bb78;
    margin-right: 12px;
    font-size: 1.2rem;
}

.btn-subscribe {
    width: 100%;
    padding: 16px;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    border: none;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transition: all 0.3s;
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

.btn-subscribe:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.6);
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
}

.features-grid {
    background: white;
    padding: 80px 0;
}

.feature-item {
    text-align: center;
    padding: 40px 20px;
    border-radius: 16px;
    transition: all 0.3s;
}

.feature-item:hover {
    background: rgba(102, 126, 234, 0.05);
    transform: translateY(-8px);
}

.feature-icon {
    font-size: 3.5rem;
    margin-bottom: 24px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.feature-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 16px;
    color: #2d3748;
}

.feature-desc {
    color: #718096;
    line-height: 1.6;
}

.testimonials-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 80px 0;
    color: white;
}

.testimonial-card {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 40px;
    margin: 20px 0;
    border: 1px solid rgba(255,255,255,0.2);
}

.faq-section {
    background: #f8f9fa;
    padding: 80px 0;
}

.faq-item {
    background: white;
    border-radius: 12px;
    margin-bottom: 16px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    transition: all 0.3s;
}

.faq-item:hover {
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.faq-question {
    padding: 24px;
    font-weight: 600;
    font-size: 1.1rem;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.faq-answer {
    padding: 0 24px 24px;
    color: #718096;
    line-height: 1.8;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 16px;
    color: #2d3748;
}

.section-subtitle {
    text-align: center;
    color: #718096;
    font-size: 1.2rem;
    margin-bottom: 60px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    .hero-subtitle {
        font-size: 1.2rem;
    }
    .plan-price {
        font-size: 2.5rem;
    }
}
</style>

<div class="mail-landing">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    <i class="fas fa-envelope-open-text"></i>
                    <br>Professional Email Hosting
                </h1>
                <p class="hero-subtitle">
                    Secure, reliable, and powerful email solutions for your business.<br>
                    Get started in minutes with custom domains and unlimited possibilities.
                </p>
            </div>
        </div>
    </div>

    <!-- Plans Section -->
    <div class="plans-section">
        <div class="container">
            <h2 class="section-title">Choose Your Perfect Plan</h2>
            <p class="section-subtitle">
                Select the plan that fits your needs. Upgrade or downgrade anytime.
            </p>

            <?php if (isset($plans) && count($plans) > 0): ?>
            <div class="row">
                <?php foreach ($plans as $index => $plan): ?>
                <?php 
                $isPopular = stripos($plan['plan_name'], 'business') !== false || 
                            stripos($plan['plan_name'], 'professional') !== false;
                ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="glass-card <?= $isPopular ? 'popular' : '' ?>">
                        <?php if ($isPopular): ?>
                        <div class="popular-badge">Most Popular</div>
                        <?php endif; ?>
                        
                        <div class="plan-header">
                            <h3 class="plan-name"><?= htmlspecialchars($plan['plan_name']) ?></h3>
                            <div class="plan-price">
                                <?php if ($plan['price_monthly'] == 0): ?>
                                <span style="color: #48bb78;">Free</span>
                                <?php else: ?>
                                <span class="currency">$</span><?= number_format($plan['price_monthly'], 0) ?>
                                <?php endif; ?>
                            </div>
                            <div class="plan-period">per month</div>
                        </div>

                        <ul class="plan-features">
                            <?php 
                            // Get features for this plan
                            $stmt = $db->prepare("SELECT feature_name, feature_value FROM mail_plan_features WHERE plan_id = ? ORDER BY id ASC");
                            $stmt->execute([$plan['id']]);
                            $features = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (count($features) > 0):
                                foreach ($features as $feature):
                            ?>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <?= htmlspecialchars($feature['feature_name']) ?>
                                <?php if ($feature['feature_value']): ?>
                                <strong>(<?= htmlspecialchars($feature['feature_value']) ?>)</strong>
                                <?php endif; ?>
                            </li>
                            <?php 
                                endforeach;
                            else:
                            ?>
                            <li><i class="fas fa-check-circle"></i> Custom Domain Support</li>
                            <li><i class="fas fa-check-circle"></i> Webmail Interface</li>
                            <li><i class="fas fa-check-circle"></i> IMAP/POP3 Access</li>
                            <li><i class="fas fa-check-circle"></i> Spam Protection</li>
                            <li><i class="fas fa-check-circle"></i> SSL/TLS Security</li>
                            <?php endif; ?>
                        </ul>

                        <a href="/projects/mail/subscribe?plan=<?= $plan['id'] ?>" class="btn btn-subscribe">
                            Get Started
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i>
                No plans are currently available. Please contact support for more information.
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Features Grid -->
    <div class="features-grid">
        <div class="container">
            <h2 class="section-title">Why Choose Us?</h2>
            <p class="section-subtitle">
                Everything you need for professional email hosting
            </p>

            <div class="row">
                <div class="col-md-4">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="feature-title">Enterprise Security</h3>
                        <p class="feature-desc">
                            Bank-grade encryption, spam filtering, and virus protection keep your inbox safe 24/7.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h3 class="feature-title">Lightning Fast</h3>
                        <p class="feature-desc">
                            High-performance servers ensure instant email delivery and rapid webmail access.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3 class="feature-title">24/7 Support</h3>
                        <p class="feature-desc">
                            Our expert team is always available to help you with any questions or issues.
                        </p>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-md-4">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h3 class="feature-title">Custom Domains</h3>
                        <p class="feature-desc">
                            Use your own domain for a professional image that builds trust with clients.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3 class="feature-title">Mobile Ready</h3>
                        <p class="feature-desc">
                            Access your email anywhere with our responsive webmail and mobile app support.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h3 class="feature-title">Auto Backup</h3>
                        <p class="feature-desc">
                            Never lose important emails with our automated daily backup system.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonials -->
    <div class="testimonials-section">
        <div class="container">
            <h2 class="section-title text-white">What Our Clients Say</h2>
            <div class="row mt-5">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 24px;">
                            "Switched to this email hosting and haven't looked back. The interface is intuitive and the support is phenomenal!"
                        </p>
                        <div>
                            <strong>Sarah Johnson</strong><br>
                            <small>CEO, TechStart Inc.</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 24px;">
                            "Best email service we've ever used. Reliable, secure, and the custom domain feature is perfect for our brand."
                        </p>
                        <div>
                            <strong>Michael Chen</strong><br>
                            <small>Marketing Director</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 24px;">
                            "The migration was seamless and the performance is outstanding. Highly recommend for any business!"
                        </p>
                        <div>
                            <strong>Emily Rodriguez</strong><br>
                            <small>Founder, Digital Agency</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="faq-section">
        <div class="container">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Can I use my own domain?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Yes! All our plans support custom domains. You can use your existing domain or purchase a new one.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Is my data secure?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Absolutely. We use enterprise-grade encryption, regular backups, and advanced spam/virus protection to keep your data safe.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Can I upgrade my plan later?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Yes, you can upgrade or downgrade your plan at any time from your dashboard.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Do you offer technical support?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Our support team is available 24/7 via email, chat, and phone to assist you with any questions or issues.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Simple FAQ accordion
document.querySelectorAll('.faq-question').forEach(question => {
    question.addEventListener('click', () => {
        const answer = question.nextElementSibling;
        const icon = question.querySelector('i');
        
        if (answer.style.display === 'block') {
            answer.style.display = 'none';
            icon.style.transform = 'rotate(0deg)';
        } else {
            // Close all other answers
            document.querySelectorAll('.faq-answer').forEach(a => {
                a.style.display = 'none';
            });
            document.querySelectorAll('.faq-question i').forEach(i => {
                i.style.transform = 'rotate(0deg)';
            });
            
            answer.style.display = 'block';
            icon.style.transform = 'rotate(180deg)';
        }
    });
});

// Hide all answers initially
document.querySelectorAll('.faq-answer').forEach(answer => {
    answer.style.display = 'none';
});
</script>

<?php View::endSection(); ?>
