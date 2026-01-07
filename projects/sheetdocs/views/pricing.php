<?php use Core\View; use Core\Security; use Core\Helpers; ?>
<?php View::extend('sheetdocs:app'); ?>

<?php View::section('content'); ?>
<style>
    .pricing-header {
        text-align: center;
        margin-bottom: 60px;
    }
    
    .pricing-header h1 {
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
        font-weight: 400;
    }
    
    .plan-features {
        list-style: none;
        margin: 30px 0;
    }
    
    .plan-features li {
        padding: 12px 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .feature-icon {
        color: var(--cyan);
        flex-shrink: 0;
    }
    
    .btn-primary {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, var(--cyan), #00a88a);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 212, 170, 0.3);
    }
    
    .btn-secondary {
        width: 100%;
        padding: 16px;
        background: var(--bg-secondary);
        color: var(--text-primary);
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .usage-stats {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 40px;
    }
    
    .usage-bar {
        height: 8px;
        background: var(--bg-secondary);
        border-radius: 4px;
        overflow: hidden;
        margin-top: 8px;
    }
    
    .usage-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--cyan), #00a88a);
        transition: width 0.3s;
    }
</style>

<div class="pricing-header">
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
        
        <?php if (($subscription['plan'] ?? 'free') === 'free'): ?>
            <button class="btn-secondary" disabled>Current Plan</button>
        <?php else: ?>
            <form method="POST" action="/projects/sheetdocs/subscription/cancel">
                <?= Security::csrfField() ?>
                <button type="submit" class="btn-secondary">Downgrade</button>
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
                <strong>Unlimited</strong> Storage
            </li>
            <li>
                <i class="fas fa-check feature-icon"></i>
                All Premium Templates
            </li>
            <li>
                <i class="fas fa-check feature-icon"></i>
                Advanced Export Options
            </li>
            <li>
                <i class="fas fa-check feature-icon"></i>
                Priority Support
            </li>
            <li>
                <i class="fas fa-check feature-icon"></i>
                Real-time Collaboration
            </li>
        </ul>
        
        <?php if (($subscription['plan'] ?? 'free') === 'paid'): ?>
            <button class="btn-secondary" disabled>Current Plan</button>
        <?php else: ?>
            <form method="POST" action="/projects/sheetdocs/subscription/upgrade">
                <?= Security::csrfField() ?>
                <button type="submit" class="btn-primary">
                    <?php if (($subscription['status'] ?? null) === 'trial'): ?>
                        Continue with Premium
                    <?php else: ?>
                        Start Free Trial
                    <?php endif; ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if (($subscription['plan'] ?? 'free') === 'free'): ?>
<div class="usage-stats">
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
<?php View::endSection(); ?>
