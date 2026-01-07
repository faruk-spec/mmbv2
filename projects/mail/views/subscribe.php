<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribe to Mail Hosting</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        
        .page-header {
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .pricing-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            margin-bottom: 30px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
        }
        
        .pricing-card.featured {
            border: 3px solid #667eea;
            position: relative;
        }
        
        .featured-badge {
            position: absolute;
            top: -15px;
            right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 20px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .plan-name {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #333;
        }
        
        .plan-price {
            font-size: 3rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .plan-price span {
            font-size: 1.25rem;
            color: #666;
        }
        
        .plan-features {
            list-style: none;
            padding: 0;
            margin: 30px 0;
        }
        
        .plan-features li {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            color: #666;
        }
        
        .plan-features li:last-child {
            border-bottom: none;
        }
        
        .plan-features i {
            color: #667eea;
            margin-right: 10px;
        }
        
        .btn-subscribe {
            width: 100%;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            border: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-subscribe:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .btn-subscribe.featured {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-crown"></i> Choose Your Plan</h1>
            <p class="lead">Select the perfect plan for your email hosting needs</p>
        </div>
        
        <!-- Pricing Cards -->
        <div class="row">
            <?php if (!empty($plans)): ?>
                <?php foreach ($plans as $plan): ?>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="pricing-card <?= $plan['sort_order'] == 2 ? 'featured' : '' ?>">
                        <?php if ($plan['sort_order'] == 2): ?>
                        <span class="featured-badge">POPULAR</span>
                        <?php endif; ?>
                        
                        <div class="plan-name"><?= htmlspecialchars($plan['plan_name']) ?></div>
                        
                        <div class="plan-price">
                            $<?= number_format($plan['price_monthly'], 2) ?>
                            <span>/month</span>
                        </div>
                        
                        <?php if ($plan['description']): ?>
                        <p class="text-muted small"><?= htmlspecialchars($plan['description']) ?></p>
                        <?php endif; ?>
                        
                        <ul class="plan-features">
                            <li><i class="fas fa-check"></i> <?= $plan['max_users'] ?> Mailbox<?= $plan['max_users'] > 1 ? 'es' : '' ?></li>
                            <li><i class="fas fa-check"></i> <?= $plan['storage_per_user_gb'] ?>GB Storage per user</li>
                            <li><i class="fas fa-check"></i> <?= $plan['max_domains'] ?> Custom Domain<?= $plan['max_domains'] > 1 ? 's' : '' ?></li>
                            <li><i class="fas fa-check"></i> <?= $plan['max_aliases'] ?> Email Alias<?= $plan['max_aliases'] > 1 ? 'es' : '' ?></li>
                            <li><i class="fas fa-check"></i> <?= number_format($plan['daily_send_limit']) ?> Emails/Day</li>
                            <li><i class="fas fa-check"></i> <?= $plan['max_attachment_size_mb'] ?>MB Max Attachment</li>
                            <li><i class="fas fa-check"></i> Spam Filtering</li>
                            <li><i class="fas fa-check"></i> Webmail Access</li>
                            <li><i class="fas fa-check"></i> IMAP/SMTP Support</li>
                        </ul>
                        
                        <form method="POST" action="/projects/mail/subscribe">
                            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                            <input type="hidden" name="billing_cycle" value="monthly">
                            <button type="submit" class="btn btn-subscribe <?= $plan['sort_order'] == 2 ? 'featured' : '' ?>">
                                <i class="fas fa-rocket"></i> Get Started
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> No subscription plans are currently available. Please check back later.
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Navigation -->
        <div class="text-center mt-4">
            <a href="/projects/mail" class="text-white text-decoration-none">
                <i class="fas fa-arrow-left"></i> Back to Mail Dashboard
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
