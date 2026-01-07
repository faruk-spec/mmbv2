<?php use Core\View; use Core\Security; use Core\Helpers; ?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SheetDocs - Pricing</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-primary: #06060a;
            --bg-card: #0f0f18;
            --cyan: #00d4aa;
            --text-primary: #e8eefc;
            --text-secondary: #8892a6;
            --border-color: rgba(255, 255, 255, 0.1);
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            padding: 40px 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .header h1 {
            font-size: 48px;
            margin-bottom: 16px;
        }
        
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .plan-card {
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 40px;
            position: relative;
        }
        
        .plan-card.featured {
            border-color: var(--cyan);
            box-shadow: 0 0 30px rgba(0, 212, 170, 0.2);
        }
        
        .plan-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--cyan);
            color: var(--bg-primary);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .plan-name {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        
        .plan-price {
            font-size: 48px;
            font-weight: 700;
            color: var(--cyan);
            margin-bottom: 8px;
        }
        
        .plan-price span {
            font-size: 18px;
            color: var(--text-secondary);
        }
        
        .plan-features {
            list-style: none;
            margin: 30px 0;
        }
        
        .plan-features li {
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .plan-features li:last-child {
            border-bottom: none;
        }
        
        .feature-icon {
            color: var(--cyan);
        }
        
        .btn {
            width: 100%;
            padding: 16px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--cyan), #00a88a);
            color: white;
        }
        
        .btn-secondary {
            background: transparent;
            border: 2px solid var(--border-color);
            color: var(--text-primary);
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .usage-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 30px;
            margin-top: 40px;
        }
        
        .usage-bar {
            height: 8px;
            background: var(--border-color);
            border-radius: 4px;
            overflow: hidden;
            margin: 12px 0;
        }
        
        .usage-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--cyan), #00a88a);
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Choose Your Plan</h1>
            <p style="color: var(--text-secondary); font-size: 18px;">
                Start free, upgrade when you need more power
            </p>
        </div>
        
        <div class="pricing-grid">
            <div class="plan-card">
                <div class="plan-name">Free</div>
                <div class="plan-price">$0<span>/month</span></div>
                <p style="color: var(--text-secondary); margin-bottom: 20px;">Perfect for getting started</p>
                
                <ul class="plan-features">
                    <li>
                        <i class="fas fa-check feature-icon"></i>
                        <?= $freeFeatures['max_documents'] ?> Documents
                    </li>
                    <li>
                        <i class="fas fa-check feature-icon"></i>
                        <?= $freeFeatures['max_sheets'] ?> Spreadsheets
                    </li>
                    <li>
                        <i class="fas fa-check feature-icon"></i>
                        <?= $freeFeatures['max_collaborators'] ?> Collaborators
                    </li>
                    <li>
                        <i class="fas fa-check feature-icon"></i>
                        <?= round($freeFeatures['storage_limit'] / 1024 / 1024) ?>MB Storage
                    </li>
                    <li>
                        <i class="fas fa-check feature-icon"></i>
                        Basic Templates
                    </li>
                    <li>
                        <i class="fas fa-check feature-icon"></i>
                        PDF Export
                    </li>
                </ul>
                
                <?php if ($subscription['plan'] === 'free'): ?>
                    <button class="btn btn-secondary" disabled>Current Plan</button>
                <?php else: ?>
                    <form method="POST" action="/projects/sheetdocs/subscription/cancel">
                        <?= Security::csrfField() ?>
                        <button type="submit" class="btn btn-secondary">Downgrade</button>
                    </form>
                <?php endif; ?>
            </div>
            
            <div class="plan-card featured">
                <div class="plan-badge">BEST VALUE</div>
                <div class="plan-name">Premium</div>
                <div class="plan-price">$<?= $pricing['monthly_price'] ?><span>/month</span></div>
                <p style="color: var(--text-secondary); margin-bottom: 20px;">
                    <?= $pricing['trial_days'] ?>-day free trial • Cancel anytime
                </p>
                
                <ul class="plan-features">
                    <li>
                        <i class="fas fa-check feature-icon"></i>
                        <strong>Unlimited</strong> Documents
                    </li>
                    <li>
                        <i class="fas fa-check feature-icon"></i>
                        <strong>Unlimited</strong> Spreadsheets
                    </li>
                    <li>
                        <i class="fas fa-check feature-icon"></i>
                        <strong>Unlimited</strong> Collaborators
                    </li>
                    <li>
                        <i class="fas fa-check feature-icon"></i>
                        1GB Storage
                    </li>
                    <li>
                        <i class="fas fa-check feature-icon"></i>
                        All Templates
                    </li>
                    <li>
                        <i class="fas fa-check feature-icon"></i>
                        Advanced Formulas
                    </li>
                    <li>
                        <i class="fas fa-check feature-icon"></i>
                        Version History
                    </li>
                    <li>
                        <i class="fas fa-check feature-icon"></i>
                        Export to DOCX, XLSX, CSV
                    </li>
                    <li>
                        <i class="fas fa-check feature-icon"></i>
                        Priority Support
                    </li>
                    <li>
                        <i class="fas fa-check feature-icon"></i>
                        API Access
                    </li>
                </ul>
                
                <?php if ($subscription['plan'] === 'paid'): ?>
                    <button class="btn btn-primary" disabled>
                        <?= $subscription['status'] === 'trial' ? 'Trial Active' : 'Current Plan' ?>
                    </button>
                <?php else: ?>
                    <form method="POST" action="/projects/sheetdocs/subscription/upgrade">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="billing_cycle" value="monthly">
                        <button type="submit" class="btn btn-primary">
                            Start <?= $pricing['trial_days'] ?>-Day Free Trial
                        </button>
                    </form>
                <?php endif; ?>
                
                <p style="text-align: center; margin-top: 16px; font-size: 14px; color: var(--text-secondary);">
                    Annual plan: $<?= $pricing['annual_price'] ?>/year (Save <?= round((1 - $pricing['annual_price'] / ($pricing['monthly_price'] * 12)) * 100) ?>%)
                </p>
            </div>
        </div>
        
        <?php if ($subscription['plan'] === 'free'): ?>
        <div class="usage-card">
            <h3 style="margin-bottom: 20px;">Your Current Usage</h3>
            
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Documents</span>
                    <span><?= $usageStats['document_count'] ?> / <?= $freeFeatures['max_documents'] ?></span>
                </div>
                <div class="usage-bar">
                    <div class="usage-fill" style="width: <?= min(100, ($usageStats['document_count'] / $freeFeatures['max_documents']) * 100) ?>%"></div>
                </div>
            </div>
            
            <div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Spreadsheets</span>
                    <span><?= $usageStats['sheet_count'] ?> / <?= $freeFeatures['max_sheets'] ?></span>
                </div>
                <div class="usage-bar">
                    <div class="usage-fill" style="width: <?= min(100, ($usageStats['sheet_count'] / $freeFeatures['max_sheets']) * 100) ?>%"></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="/projects/sheetdocs/dashboard" style="color: var(--cyan); text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
