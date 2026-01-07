<?php use Core\View; use Core\Security; use Core\Auth; use Core\Helpers; ?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SheetDocs - Dashboard</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-primary: #06060a;
            --bg-secondary: #0c0c12;
            --bg-card: #0f0f18;
            --cyan: #00d4aa;
            --text-primary: #e8eefc;
            --text-secondary: #8892a6;
            --border-color: rgba(255, 255, 255, 0.1);
            --sidebar-width: 280px;
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
            min-height: 100vh;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background: rgba(12, 12, 18, 0.95);
            border-right: 1px solid var(--border-color);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            padding: 20px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--cyan);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-item {
            padding: 12px 20px;
            margin-bottom: 8px;
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s;
        }
        
        .nav-item:hover, .nav-item.active {
            background: rgba(0, 212, 170, 0.1);
            color: var(--cyan);
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 40px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
        
        .header h1 {
            font-size: 32px;
            font-weight: 600;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--cyan), #00a88a);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 212, 170, 0.3);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--cyan);
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .doc-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .doc-card:hover {
            border-color: var(--cyan);
            transform: translateY(-4px);
        }
        
        .doc-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .doc-meta {
            color: var(--text-secondary);
            font-size: 13px;
        }
        
        .upgrade-banner {
            background: linear-gradient(135deg, rgba(0, 212, 170, 0.1), rgba(0, 168, 138, 0.1));
            border: 1px solid var(--cyan);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid #00ff88;
            color: #00ff88;
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-file-alt"></i>
                SheetDocs
            </div>
            
            <nav>
                <a href="/projects/sheetdocs/dashboard" class="nav-item active">
                    <i class="fas fa-th-large"></i>
                    Dashboard
                </a>
                <a href="/projects/sheetdocs/documents/new" class="nav-item">
                    <i class="fas fa-file-alt"></i>
                    New Document
                </a>
                <a href="/projects/sheetdocs/sheets/new" class="nav-item">
                    <i class="fas fa-table"></i>
                    New Spreadsheet
                </a>
                <a href="/projects/sheetdocs/documents" class="nav-item">
                    <i class="fas fa-folder"></i>
                    My Documents
                </a>
                <a href="/projects/sheetdocs/sheets" class="nav-item">
                    <i class="fas fa-th"></i>
                    My Sheets
                </a>
                <a href="/projects/sheetdocs/templates" class="nav-item">
                    <i class="fas fa-clone"></i>
                    Templates
                </a>
                <a href="/projects/sheetdocs/pricing" class="nav-item">
                    <i class="fas fa-crown"></i>
                    Upgrade
                </a>
                <a href="/" class="nav-item" style="margin-top: 20px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                    <i class="fas fa-home"></i>
                    Back to Home
                </a>
            </nav>
        </aside>
        
        <main class="main-content">
            <?php if (Helpers::hasFlash('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= View::e(Helpers::getFlash('success')) ?>
                </div>
            <?php endif; ?>
            
            <div class="header">
                <div>
                    <h1>Welcome to SheetDocs!</h1>
                    <p style="color: var(--text-secondary);">Create and collaborate on documents and spreadsheets</p>
                </div>
                <div style="display: flex; gap: 12px;">
                    <a href="/projects/sheetdocs/documents/new" class="btn btn-primary">
                        <i class="fas fa-file-alt"></i>
                        New Document
                    </a>
                    <a href="/projects/sheetdocs/sheets/new" class="btn btn-primary">
                        <i class="fas fa-table"></i>
                        New Spreadsheet
                    </a>
                </div>
            </div>
            
            <?php if ($subscription['plan'] === 'free'): ?>
            <div class="upgrade-banner">
                <div>
                    <h3 style="margin-bottom: 8px;">Upgrade to Premium</h3>
                    <p style="color: var(--text-secondary);">Get unlimited documents, sheets, and advanced features</p>
                </div>
                <a href="/projects/sheetdocs/pricing" class="btn btn-primary">
                    <i class="fas fa-crown"></i>
                    View Plans
                </a>
            </div>
            <?php endif; ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['document_count'] ?></div>
                    <div class="stat-label">Documents</div>
                    <?php if (isset($limits['features']['max_documents']) && $limits['features']['max_documents'] != -1): ?>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">
                            Limit: <?= $limits['features']['max_documents'] ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['sheet_count'] ?></div>
                    <div class="stat-label">Spreadsheets</div>
                    <?php if (isset($limits['features']['max_sheets']) && $limits['features']['max_sheets'] != -1): ?>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">
                            Limit: <?= $limits['features']['max_sheets'] ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value">
                        <?= $subscription['plan'] === 'paid' ? 'Premium' : 'Free' ?>
                    </div>
                    <div class="stat-label">Current Plan</div>
                    <?php if ($subscription['status'] === 'trial'): ?>
                        <div style="font-size: 12px; color: var(--cyan); margin-top: 4px;">
                            <i class="fas fa-star"></i> Trial Active
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($recentDocuments) || !empty($recentSheets)): ?>
            <h2 style="margin-bottom: 20px; font-size: 24px;">Recent Files</h2>
            <div class="documents-grid">
                <?php foreach (array_merge($recentDocuments, $recentSheets) as $doc): ?>
                <div class="doc-card" onclick="window.location.href='/projects/sheetdocs/<?= $doc['type'] === 'document' ? 'documents' : 'sheets' ?>/<?= $doc['id'] ?>/edit'">
                    <div class="doc-title">
                        <i class="fas fa-<?= $doc['type'] === 'document' ? 'file-alt' : 'table' ?>"></i>
                        <?= View::e($doc['title']) ?>
                    </div>
                    <div class="doc-meta">
                        Updated <?= Helpers::timeAgo($doc['updated_at']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
                <i class="fas fa-file-alt" style="font-size: 64px; opacity: 0.3; margin-bottom: 20px;"></i>
                <h3 style="margin-bottom: 12px;">No documents yet</h3>
                <p>Create your first document or spreadsheet to get started!</p>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
