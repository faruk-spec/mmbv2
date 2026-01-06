<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

body {
    font-family: 'Inter', sans-serif !important;
}

.mail-dashboard-container {
    display: flex;
    min-height: 100vh;
    background: #f8f9fa;
}

/* Fixed Sidebar */
.mail-sidebar {
    width: 260px;
    background: white;
    border-right: 1px solid #e2e8f0;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.sidebar-logo {
    font-size: 1.5rem;
    font-weight: 700;
}

.sidebar-menu {
    padding: 20px 0;
}

.menu-section-title {
    padding: 10px 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #a0aec0;
}

.menu-item {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #4a5568;
    text-decoration: none;
    transition: all 0.2s;
    border-left: 3px solid transparent;
}

.menu-item:hover {
    background: #f7fafc;
    color: #667eea;
    text-decoration: none;
    border-left-color: #667eea;
}

.menu-item.active {
    background: #edf2f7;
    color: #667eea;
    border-left-color: #667eea;
    font-weight: 600;
}

.menu-item i {
    width: 20px;
    margin-right: 12px;
}

.menu-badge {
    margin-left: auto;
    background: #667eea;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
}

/* Main Content Area */
.mail-content {
    margin-left: 260px;
    flex: 1;
    padding: 30px;
}

.dashboard-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 30px;
}

/* Theme Toggle */
.theme-toggle {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1001;
}

.theme-btn {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 25px;
    padding: 8px 20px;
    cursor: pointer;
}

/* Dark Theme */
body.dark-theme {
    background: #1a202c;
    color: #e2e8f0;
}

body.dark-theme .mail-dashboard-container {
    background: #1a202c;
}

body.dark-theme .mail-sidebar {
    background: #2d3748;
    border-right-color: #4a5568;
}

body.dark-theme .menu-item {
    color: #cbd5e0;
}

body.dark-theme .menu-item:hover,
body.dark-theme .menu-item.active {
    background: #374151;
    color: #90cdf4;
}

body.dark-theme .theme-btn {
    background: #2d3748;
    border-color: #4a5568;
    color: #e2e8f0;
}
</style>

<div class="theme-toggle">
    <button class="theme-btn" onclick="toggleTheme()">
        <i class="fas fa-moon"></i> <span class="theme-text">Dark Mode</span>
    </button>
</div>

<div class="mail-dashboard-container">
    <!-- Sidebar -->
    <div class="mail-sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo"><i class="fas fa-envelope"></i> Mail Server</div>
        </div>
        
        <nav class="sidebar-menu">
            <div class="menu-section-title">Overview</div>
            <a href="/projects/mail/subscriber/dashboard" class="menu-item active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            
            <div class="menu-section-title">Management</div>
            <a href="/projects/mail/subscriber/users" class="menu-item">
                <i class="fas fa-users"></i> Users
                <span class="menu-badge"><?= $stats['users_count'] ?></span>
            </a>
            <a href="/projects/mail/subscriber/domains" class="menu-item">
                <i class="fas fa-globe"></i> Domains
                <span class="menu-badge"><?= $stats['domains_count'] ?></span>
            </a>
            <a href="/projects/mail/subscriber/aliases" class="menu-item">
                <i class="fas fa-at"></i> Aliases
                <span class="menu-badge"><?= $stats['aliases_count'] ?></span>
            </a>
            
            <div class="menu-section-title">Email</div>
            <a href="/projects/mail/webmail" class="menu-item">
                <i class="fas fa-inbox"></i> Webmail
            </a>
            
            <div class="menu-section-title">Settings</div>
            <a href="/projects/mail/subscriber/billing" class="menu-item">
                <i class="fas fa-credit-card"></i> Billing
            </a>
            <a href="/dashboard" class="menu-item">
                <i class="fas fa-arrow-left"></i> Back to Main
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="mail-content">
        <h1 class="dashboard-title">Mail Dashboard</h1>
        <p>Welcome! Your mail hosting dashboard with universal sidebar.</p>
        <!-- Dashboard content here -->
    </div>
</div>

<script>
function toggleTheme() {
    const body = document.body;
    const icon = document.querySelector('.theme-btn i');
    const text = document.querySelector('.theme-text');
    
    if (body.classList.contains('dark-theme')) {
        body.classList.remove('dark-theme');
        icon.className = 'fas fa-moon';
        text.textContent = 'Dark Mode';
        localStorage.setItem('mail-theme', 'light');
    } else {
        body.classList.add('dark-theme');
        icon.className = 'fas fa-sun';
        text.textContent = 'Light Mode';
        localStorage.setItem('mail-theme', 'dark');
    }
}

// Load saved theme
document.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('mail-theme') === 'dark') {
        document.body.classList.add('dark-theme');
        document.querySelector('.theme-btn i').className = 'fas fa-sun';
        document.querySelector('.theme-text').textContent = 'Light Mode';
    }
});
</script>

<?php View::endSection(); ?>
