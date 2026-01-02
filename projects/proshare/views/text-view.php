<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($text['title'] ?: 'Shared Text') ?> - ProShare</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f0f23;
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        h1 {
            color: #00f0ff;
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .meta-info {
            background: #1a1a2e;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .meta-item {
            color: #888;
            font-size: 0.9em;
        }
        
        .meta-item span {
            color: #00f0ff;
            font-weight: bold;
        }
        
        .content-panel {
            background: #1a1a2e;
            border: 1px solid #00f0ff;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .text-content {
            background: #0f0f23;
            border: 1px solid #333;
            border-radius: 4px;
            padding: 20px;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: 'Courier New', Courier, monospace;
            max-height: 600px;
            overflow-y: auto;
            line-height: 1.6;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            background: #00f0ff;
            color: #0f0f23;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
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
        
        .warning {
            background: #ff6b6b;
            color: #fff;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .container {
                width: 100%;
            }
            
            .meta-info {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= htmlspecialchars($text['title'] ?: 'Shared Text') ?></h1>
        </div>
        
        <?php if ($text['self_destruct']): ?>
            <div class="warning">
                ⚠️ This text will self-destruct after you close this page!
            </div>
        <?php endif; ?>
        
        <div class="meta-info">
            <div class="meta-item">
                Views: <span><?= $text['views'] ?></span>
                <?php if ($text['max_views']): ?>
                    / <?= $text['max_views'] ?>
                <?php endif; ?>
            </div>
            <div class="meta-item">
                Created: <span><?= date('M d, Y H:i', strtotime($text['created_at'])) ?></span>
            </div>
            <?php if ($text['expires_at']): ?>
                <div class="meta-item">
                    Expires: <span><?= date('M d, Y H:i', strtotime($text['expires_at'])) ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="content-panel">
            <div class="text-content" id="textContent"><?= htmlspecialchars($text['content']) ?></div>
            
            <div class="actions">
                <button class="btn" onclick="copyToClipboard()">📋 Copy to Clipboard</button>
                <button class="btn btn-secondary" onclick="downloadAsFile()">💾 Download as TXT</button>
                <a href="/projects/proshare/text" class="btn btn-secondary">Share Your Own</a>
            </div>
        </div>
        
        <div style="text-align: center; color: #888; font-size: 0.9em;">
            <p>Shared securely via <a href="/projects/proshare" style="color: #00f0ff;">ProShare</a></p>
        </div>
    </div>
    
    <script>
        function copyToClipboard() {
            const text = document.getElementById('textContent').textContent;
            navigator.clipboard.writeText(text).then(() => {
                alert('Text copied to clipboard!');
            }).catch(() => {
                // Fallback for older browsers
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('Text copied to clipboard!');
            });
        }
        
        function downloadAsFile() {
            const text = document.getElementById('textContent').textContent;
            const title = '<?= addslashes($text['title'] ?: 'shared-text') ?>';
            const filename = title.replace(/[^a-z0-9]/gi, '-').toLowerCase() + '.txt';
            
            const blob = new Blob([text], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.click();
            URL.revokeObjectURL(url);
        }
    </script>
</body>
</html>
