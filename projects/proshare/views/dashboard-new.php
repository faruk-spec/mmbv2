<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProShare - Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f0f23;
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }
        
        .dashboard-header {
            margin-bottom: 30px;
        }
        
        h1 {
            color: #00f0ff;
            margin-bottom: 10px;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        
        .btn {
            background: #00f0ff;
            color: #0f0f23;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #00d4dd;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #333;
            color: #fff;
        }
        
        .btn-secondary:hover {
            background: #444;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: #1a1a2e;
            border: 2px solid #00f0ff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2.5em;
            color: #00f0ff;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #888;
            font-size: 1.1em;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        h2 {
            color: #00f0ff;
            margin-bottom: 20px;
        }
        
        .list-table {
            width: 100%;
            background: #1a1a2e;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .list-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .list-table th,
        .list-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #333;
        }
        
        .list-table th {
            background: #16213e;
            color: #00f0ff;
            font-weight: bold;
        }
        
        .list-table tr:hover {
            background: #16213e;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
        }
        
        .status-active {
            background: #0f4;
            color: #000;
        }
        
        .status-expired {
            background: #f80;
            color: #000;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #888;
            background: #1a1a2e;
            border-radius: 8px;
        }
        
        .empty-state a {
            color: #00f0ff;
            text-decoration: none;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .list-table {
                overflow-x: auto;
            }
            
            .list-table table {
                min-width: 600px;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <h1>🔒 ProShare Dashboard</h1>
        <p>Secure file and text sharing platform</p>
        <div class="actions">
            <a href="/projects/proshare/upload" class="btn">📁 Share Files</a>
            <a href="/projects/proshare/text" class="btn">📝 Share Text</a>
            <a href="/projects/proshare/files" class="btn btn-secondary">My Files</a>
            <a href="/projects/proshare/settings" class="btn btn-secondary">Settings</a>
            <a href="/dashboard" class="btn btn-secondary">Main Dashboard</a>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_files'] ?? 0 ?></div>
            <div class="stat-label">Total Files</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_texts'] ?? 0 ?></div>
            <div class="stat-label">Text Shares</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_downloads'] ?? 0 ?></div>
            <div class="stat-label">Total Downloads</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['active_shares'] ?? 0 ?></div>
            <div class="stat-label">Active Shares</div>
        </div>
    </div>
    
    <div class="section">
        <h2>Recent Files</h2>
        
        <?php if (!empty($recentFiles)): ?>
            <div class="list-table">
                <table>
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Status</th>
                            <th>Downloads</th>
                            <th>Expires</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentFiles as $file): ?>
                            <tr>
                                <td><?= htmlspecialchars($file['original_name']) ?></td>
                                <td>
                                    <span class="status status-<?= $file['status'] ?>">
                                        <?= ucfirst($file['status']) ?>
                                    </span>
                                </td>
                                <td><?= $file['downloads'] ?><?= $file['max_downloads'] ? ' / ' . $file['max_downloads'] : '' ?></td>
                                <td><?= $file['expires_at'] ? date('M d, H:i', strtotime($file['expires_at'])) : 'Never' ?></td>
                                <td>
                                    <a href="/s/<?= $file['short_code'] ?>" class="btn" style="padding: 5px 10px; font-size: 0.9em;" target="_blank">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>No files shared yet. <a href="/projects/proshare/upload">Share your first file</a></p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>Recent Text Shares</h2>
        
        <?php if (!empty($recentTexts)): ?>
            <div class="list-table">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTexts as $text): ?>
                            <tr>
                                <td><?= htmlspecialchars($text['title'] ?: 'Untitled') ?></td>
                                <td>
                                    <span class="status status-<?= $text['status'] ?>">
                                        <?= ucfirst($text['status']) ?>
                                    </span>
                                </td>
                                <td><?= $text['views'] ?><?= $text['max_views'] ? ' / ' . $text['max_views'] : '' ?></td>
                                <td><?= date('M d, H:i', strtotime($text['created_at'])) ?></td>
                                <td>
                                    <a href="/t/<?= $text['short_code'] ?>" class="btn" style="padding: 5px 10px; font-size: 0.9em;" target="_blank">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>No text shares yet. <a href="/projects/proshare/text">Share your first text</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
