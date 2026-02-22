<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print QR Codes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: white;
            color: black;
        }
        
        /* Page size configurations */
        @page {
            margin: <?= $margins === 'small' ? '10mm' : ($margins === 'large' ? '20mm' : '15mm') ?>;
            size: <?= $pageSize === 'letter' ? 'letter portrait' : ($pageSize === 'a3' ? 'A3 portrait' : ($pageSize === 'legal' ? 'legal portrait' : 'A4 portrait')) ?>;
        }
        
        .print-container {
            width: 100%;
            max-width: <?= $pageSize === 'a3' ? '297mm' : ($pageSize === 'letter' || $pageSize === 'legal' ? '8.5in' : '210mm') ?>;
            margin: 0 auto;
            padding: 20px;
        }
        
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(<?= 
                $qrSize === 'small' ? '4' : 
                ($qrSize === 'medium' ? '3' : 
                ($qrSize === 'large' ? '2' : '1')) 
            ?>, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .qr-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            page-break-inside: avoid;
            background: white;
        }
        
        .qr-image {
            width: 100%;
            max-width: <?= 
                $qrSize === 'small' ? '120px' : 
                ($qrSize === 'medium' ? '180px' : 
                ($qrSize === 'large' ? '240px' : '300px')) 
            ?>;
            height: auto;
            margin-bottom: <?= $showLabels ? '10px' : '0' ?>;
        }
        
        .qr-label {
            font-size: <?= 
                $qrSize === 'small' ? '10px' : 
                ($qrSize === 'medium' ? '12px' : '14px') 
            ?>;
            text-align: center;
            word-wrap: break-word;
            max-width: 100%;
            color: black;
        }
        
        .no-print {
            margin: 20px 0;
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            .qr-item {
                border: 1px solid #000;
            }
            
            body {
                background: white !important;
            }
        }
        
        .print-info {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .print-info h2 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .print-info p {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <div class="print-info">
            <h2>Print Preview - <?= count($qrCodes) ?> QR Code(s)</h2>
            <p>
                Page: <?= ucfirst($pageSize) ?> | 
                Size: <?= ucfirst($qrSize) ?> | 
                Margins: <?= ucfirst($margins) ?>
                <?= $removeBg ? ' | Background Removed' : '' ?>
                <?= $showLabels ? ' | Labels Shown' : '' ?>
            </p>
        </div>
        <div style="text-align: center; margin-bottom: 20px;">
            <button onclick="window.print()" class="btn">
                <i class="fas fa-print"></i> Print Now
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                Close
            </button>
        </div>
    </div>
    
    <div class="print-container">
        <div class="qr-grid">
            <?php foreach ($qrCodes as $qr): ?>
                <div class="qr-item">
                    <?php if (!empty($qr['qr_data_url'])): ?>
                        <?php 
                        $qrDataUrl = $qr['qr_data_url'];
                        // If remove background is enabled, we'd need to process the image
                        // For now, we'll just use the data URL as is
                        if ($removeBg) {
                            // In a full implementation, you'd process the image to remove background
                            // For now, we'll just add a white background explicitly
                        }
                        ?>
                        <img src="<?= htmlspecialchars($qrDataUrl) ?>" 
                             alt="QR Code" 
                             class="qr-image"
                             style="<?= $removeBg ? 'background: white; padding: 5px;' : '' ?>">
                    <?php else: ?>
                        <div style="width: 100%; max-width: 200px; height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                            <span style="color: #999;">QR Code</span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($showLabels): ?>
                        <div class="qr-label">
                            <?= htmlspecialchars(mb_strimwidth($qr['content'] ?? 'QR Code', 0, 50, '...')) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
        // Auto-print option (commented out by default)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
