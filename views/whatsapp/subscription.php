<?php use Core\View; ?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::e($pageTitle ?? 'My Subscription') ?> - <?= APP_NAME ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root[data-theme="dark"] {
            --bg-primary: #06060a;
            --bg-secondary: #0c0c12;
            --bg-card: #0f0f18;
            --text-primary: #e8eefc;
            --text-secondary: #8892a6;
            --border-color: rgba(255, 255, 255, 0.1);
            --whatsapp-green: #25D366;
            --success: #28c76f;
            --danger: #ea5455;
            --warning: #ff9f43;
            --info: #00cfe8;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 12px;
            background: linear-gradient(135deg, var(--whatsapp-green), #128C7E);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .page-header p {
            color: var(--text-secondary);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-primary);
            text-decoration: none;
            margin-bottom: 24px;
            padding: 8px 16px;
            border-radius: 8px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            border-color: var(--whatsapp-green);
            transform: translateX(-5px);
        }
        
        .subscription-card {
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 32px;
        }
        
        .subscription-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .plan-info h2 {
            font-size: 2rem;
            margin-bottom: 8px;
        }
        
        .plan-type {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .plan-type.free {
            background: rgba(130, 134, 139, 0.2);
            color: #82868b;
        }
        
        .plan-type.basic {
            background: rgba(0, 123, 255, 0.2);
            color: #007bff;
        }
        
        .plan-type.premium {
            background: rgba(255, 159, 67, 0.2);
            color: #ff9f43;
        }
        
        .plan-type.enterprise {
            background: rgba(153, 69, 255, 0.2);
            color: #9945ff;
        }
        
        .status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .status-badge.active {
            background: rgba(40, 199, 111, 0.2);
            color: var(--success);
        }
        
        .status-badge.expired {
            background: rgba(234, 84, 85, 0.2);
            color: var(--danger);
        }
        
        .status-badge.cancelled {
            background: rgba(255, 159, 67, 0.2);
            color: var(--warning);
        }
        
        .subscription-dates {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .date-box {
            background: var(--bg-card);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        
        .date-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        
        .date-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .usage-section {
            margin-bottom: 32px;
        }
        
        .usage-section h3 {
            font-size: 1.5rem;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .usage-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }
        
        .usage-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
        }
        
        .usage-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .usage-label {
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .usage-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--whatsapp-green);
        }
        
        .usage-limit {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 12px;
        }
        
        .progress-bar {
            height: 12px;
            background: var(--bg-secondary);
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--whatsapp-green), #128C7E);
            border-radius: 6px;
            transition: width 0.5s ease;
        }
        
        .progress-fill.warning {
            background: linear-gradient(90deg, var(--warning), #ff8800);
        }
        
        .progress-fill.danger {
            background: linear-gradient(90deg, var(--danger), #c82333);
        }
        
        .progress-text {
            font-size: 0.875rem;
            color: var(--text-secondary);
            text-align: right;
        }
        
        .plans-section {
            margin-top: 48px;
        }
        
        .plans-section h3 {
            font-size: 1.5rem;
            margin-bottom: 24px;
            text-align: center;
        }
        
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
        }
        
        .plan-card {
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .plan-card:hover {
            border-color: var(--whatsapp-green);
            transform: translateY(-5px);
        }
        
        .plan-card.current {
            border-color: var(--whatsapp-green);
            box-shadow: 0 0 20px rgba(37, 211, 102, 0.3);
        }
        
        .plan-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--whatsapp-green);
            margin: 16px 0;
        }
        
        .plan-features {
            list-style: none;
            padding: 20px 0;
            margin: 20px 0;
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }
        
        .plan-features li {
            padding: 8px 0;
            font-size: 0.9rem;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 32px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: var(--whatsapp-green);
            color: white;
        }
        
        .btn-primary:hover {
            background: #1da851;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-warning {
            background: rgba(255, 159, 67, 0.1);
            border: 1px solid var(--warning);
            color: var(--warning);
        }
        
        .alert-danger {
            background: rgba(234, 84, 85, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
        }
        
        .alert-info {
            background: rgba(0, 207, 232, 0.1);
            border: 1px solid var(--info);
            color: var(--info);
        }
        
        @media (max-width: 768px) {
            .subscription-header {
                flex-direction: column;
                gap: 16px;
            }
            
            .usage-grid, .plans-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/whatsapp/dashboard" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>
        
        <div class="page-header">
            <h1><i class="fas fa-crown"></i> My Subscription</h1>
            <p>Manage your WhatsApp API subscription and view usage statistics</p>
        </div>
        
        <?php if ($subscription): ?>
            <!-- Current Subscription -->
            <div class="subscription-card">
                <div class="subscription-header">
                    <div class="plan-info">
                        <h2>Current Plan</h2>
                        <span class="plan-type <?= strtolower($subscription['plan_type']) ?>">
                            <?= View::e(ucfirst($subscription['plan_type'])) ?>
                        </span>
                    </div>
                    <div>
                        <span class="status-badge <?= strtolower($subscription['status']) ?>">
                            <?= View::e(ucfirst($subscription['status'])) ?>
                        </span>
                    </div>
                </div>
                
                <!-- Subscription Dates -->
                <div class="subscription-dates">
                    <div class="date-box">
                        <div class="date-label"><i class="fas fa-calendar-check"></i> Start Date</div>
                        <div class="date-value"><?= date('M d, Y', strtotime($subscription['start_date'])) ?></div>
                    </div>
                    <div class="date-box">
                        <div class="date-label"><i class="fas fa-calendar-times"></i> End Date</div>
                        <div class="date-value"><?= date('M d, Y', strtotime($subscription['end_date'])) ?></div>
                    </div>
                    <div class="date-box">
                        <div class="date-label"><i class="fas fa-clock"></i> Days Remaining</div>
                        <div class="date-value" style="color: <?= $subscription['days_remaining'] < 7 ? 'var(--danger)' : 'var(--success)' ?>">
                            <?= $subscription['days_remaining'] ?? 'N/A' ?> days
                        </div>
                    </div>
                </div>
                
                <!-- Warnings -->
                <?php if ($subscription['days_remaining'] !== null && $subscription['days_remaining'] < 7): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Your subscription is expiring soon!</strong> Please contact admin to renew.
                    </div>
                <?php endif; ?>
                
                <?php if ($subscription['status'] === 'expired'): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i>
                        <strong>Your subscription has expired!</strong> Please contact admin to renew your plan.
                    </div>
                <?php endif; ?>
                
                <!-- Usage Statistics -->
                <div class="usage-section">
                    <h3><i class="fas fa-chart-bar"></i> Usage Statistics</h3>
                    
                    <div class="usage-grid">
                        <!-- Messages Usage -->
                        <div class="usage-card">
                            <div class="usage-header">
                                <div class="usage-label">
                                    <i class="fas fa-comment"></i>
                                    Messages
                                </div>
                                <div class="usage-value"><?= number_format($subscription['messages_used']) ?></div>
                            </div>
                            <div class="usage-limit">
                                Limit: <?= $subscription['messages_limit'] == 0 ? 'Unlimited' : number_format($subscription['messages_limit']) ?>
                            </div>
                            <?php if ($subscription['messages_limit'] > 0): ?>
                                <div class="progress-bar">
                                    <div class="progress-fill <?= $subscription['messages_percent'] > 90 ? 'danger' : ($subscription['messages_percent'] > 75 ? 'warning' : '') ?>" 
                                         style="width: <?= min($subscription['messages_percent'], 100) ?>%"></div>
                                </div>
                                <div class="progress-text"><?= number_format($subscription['messages_percent'], 1) ?>% used</div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Sessions Usage -->
                        <div class="usage-card">
                            <div class="usage-header">
                                <div class="usage-label">
                                    <i class="fas fa-mobile-alt"></i>
                                    Sessions
                                </div>
                                <div class="usage-value"><?= $subscription['sessions_used'] ?></div>
                            </div>
                            <div class="usage-limit">
                                Limit: <?= $subscription['sessions_limit'] == 0 ? 'Unlimited' : $subscription['sessions_limit'] ?>
                            </div>
                            <?php if ($subscription['sessions_limit'] > 0): ?>
                                <div class="progress-bar">
                                    <div class="progress-fill <?= $subscription['sessions_percent'] > 90 ? 'danger' : ($subscription['sessions_percent'] > 75 ? 'warning' : '') ?>" 
                                         style="width: <?= min($subscription['sessions_percent'], 100) ?>%"></div>
                                </div>
                                <div class="progress-text"><?= number_format($subscription['sessions_percent'], 1) ?>% used</div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- API Calls Usage -->
                        <div class="usage-card">
                            <div class="usage-header">
                                <div class="usage-label">
                                    <i class="fas fa-plug"></i>
                                    API Calls
                                </div>
                                <div class="usage-value"><?= number_format($subscription['api_calls_used']) ?></div>
                            </div>
                            <div class="usage-limit">
                                Limit: <?= $subscription['api_calls_limit'] == 0 ? 'Unlimited' : number_format($subscription['api_calls_limit']) ?>
                            </div>
                            <?php if ($subscription['api_calls_limit'] > 0): ?>
                                <div class="progress-bar">
                                    <div class="progress-fill <?= $subscription['api_calls_percent'] > 90 ? 'danger' : ($subscription['api_calls_percent'] > 75 ? 'warning' : '') ?>" 
                                         style="width: <?= min($subscription['api_calls_percent'], 100) ?>%"></div>
                                </div>
                                <div class="progress-text"><?= number_format($subscription['api_calls_percent'], 1) ?>% used</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>No active subscription found.</strong> Please contact the administrator to get a subscription plan.
            </div>
        <?php endif; ?>
        
        <!-- Available Plans -->
        <?php if (!empty($plans)): ?>
            <div class="plans-section">
                <h3>Available Plans</h3>
                <div class="plans-grid">
                    <?php foreach ($plans as $plan): ?>
                        <div class="plan-card <?= $subscription && strtolower(str_replace(' Plan', '', $plan['name'])) === $subscription['plan_type'] ? 'current' : '' ?>">
                            <h4><?= View::e($plan['name']) ?></h4>
                            <?php if ($subscription && strtolower(str_replace(' Plan', '', $plan['name'])) === $subscription['plan_type']): ?>
                                <div style="margin: 8px 0;">
                                    <span class="plan-type active" style="background: rgba(40, 199, 111, 0.2); color: var(--success);">
                                        CURRENT PLAN
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="plan-price">
                                <?= $plan['currency'] ?> <?= number_format($plan['price'], 2) ?>
                                <small style="font-size: 1rem; color: var(--text-secondary);">/ <?= $plan['duration_days'] ?> days</small>
                            </div>
                            <p style="color: var(--text-secondary); margin-bottom: 20px;"><?= View::e($plan['description']) ?></p>
                            <ul class="plan-features">
                                <li><i class="fas fa-check" style="color: var(--success);"></i> 
                                    <?= $plan['messages_limit'] == 0 ? 'Unlimited' : number_format($plan['messages_limit']) ?> Messages
                                </li>
                                <li><i class="fas fa-check" style="color: var(--success);"></i> 
                                    <?= $plan['sessions_limit'] == 0 ? 'Unlimited' : $plan['sessions_limit'] ?> Sessions
                                </li>
                                <li><i class="fas fa-check" style="color: var(--success);"></i> 
                                    <?= $plan['api_calls_limit'] == 0 ? 'Unlimited' : number_format($plan['api_calls_limit']) ?> API Calls
                                </li>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="text-align: center; margin-top: 32px;">
                    <p style="color: var(--text-secondary);">
                        <i class="fas fa-info-circle"></i>
                        Want to upgrade or change your plan? Please contact the administrator.
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
