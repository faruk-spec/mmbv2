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
        
        <?php
        // Handle custom page size
        $orientation = $_GET['orientation'] ?? 'portrait';
        $customWidth = isset($_GET['customWidth']) ? intval($_GET['customWidth']) : null;
        $customHeight = isset($_GET['customHeight']) ? intval($_GET['customHeight']) : null;
        
        // Handle custom QR size (per row)
        $customPerRow = isset($_GET['customPerRow']) ? intval($_GET['customPerRow']) : null;
        
        // Determine grid columns
        if ($qrSize === 'custom' && $customPerRow) {
            $gridColumns = $customPerRow;
        } else {
            $gridColumns = $qrSize === 'small' ? 4 : 
                          ($qrSize === 'medium' ? 3 : 
                          ($qrSize === 'large' ? 2 : 1));
        }
        
        // Determine page dimensions
        if ($pageSize === 'custom' && $customWidth && $customHeight) {
            $pageDimension = "{$customWidth}mm {$customHeight}mm";
            $maxWidth = "{$customWidth}mm";
        } else {
            $pageDimension = $pageSize === 'letter' ? 'letter' : 
                            ($pageSize === 'a3' ? 'A3' : 
                            ($pageSize === 'legal' ? 'legal' : 'A4'));
            $maxWidth = $pageSize === 'a3' ? '297mm' : 
                       ($pageSize === 'letter' || $pageSize === 'legal' ? '8.5in' : '210mm');
        }
        ?>
        
        /* Page size configurations */
        @page {
            margin: <?= $margins === 'small' ? '10mm' : ($margins === 'large' ? '20mm' : '15mm') ?>;
            size: <?= $pageDimension ?> <?= $orientation ?>;
        }
        
        .print-container {
            width: 100%;
            max-width: <?= $maxWidth ?>;
            margin: 0 auto;
            padding: 20px;
        }
        
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(<?= $gridColumns ?>, 1fr);
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
        
        .qr-image-container {
            width: 100%;
            max-width: <?= 
                $qrSize === 'small' ? '120px' : 
                ($qrSize === 'medium' ? '180px' : 
                ($qrSize === 'large' ? '240px' : 
                ($qrSize === 'xlarge' ? '300px' : '180px'))) 
            ?>;
            height: auto;
            margin-bottom: <?= $showLabels ? '10px' : '0' ?>;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .qr-image-container img {
            width: 100%;
            height: auto;
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
                <?php if ($pageSize === 'custom'): ?>
                    Page: Custom (<?= $customWidth ?>×<?= $customHeight ?> mm) | 
                <?php else: ?>
                    Page: <?= ucfirst($pageSize) ?> | 
                <?php endif; ?>
                <?php if ($qrSize === 'custom'): ?>
                    Size: Custom (<?= $customPerRow ?> per row) | 
                <?php else: ?>
                    Size: <?= ucfirst($qrSize) ?> | 
                <?php endif; ?>
                Margins: <?= ucfirst($margins) ?> |
                Orientation: <?= ucfirst($orientation) ?>
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
            <?php foreach ($qrCodes as $index => $qr): ?>
                <div class="qr-item">
                    <div class="qr-image-container" id="qr-container-<?= $index ?>" style="<?= $removeBg ? 'background: white; padding: 5px;' : '' ?>">
                        <!-- QR code will be generated here -->
                    </div>
                    
                    <?php if ($showLabels): ?>
                        <div class="qr-label">
                            <?= htmlspecialchars(mb_strimwidth($qr['content'] ?? 'QR Code', 0, 50, '...')) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Include QR Code Generator Library -->
    <script src="https://unpkg.com/qrcode-generator@1.4.4/qrcode.js"></script>
    <script>
        // Generate QR codes for each item
        <?php foreach ($qrCodes as $index => $qr): ?>
        (function() {
            try {
                const qr = qrcode(0, 'H'); // High error correction
                qr.addData(<?= json_encode($qr['content']) ?>);
                qr.make();
                
                // Calculate cell size based on container size
                const cellSize = <?= 
                    $qrSize === 'small' ? '3' : 
                    ($qrSize === 'medium' ? '5' : 
                    ($qrSize === 'large' ? '7' : 
                    ($qrSize === 'xlarge' ? '9' : '5'))) 
                ?>;
                
                // Generate QR code image
                const img = qr.createImgTag(cellSize, 0);
                document.getElementById('qr-container-<?= $index ?>').innerHTML = img;
            } catch (e) {
                console.error('Failed to generate QR code:', e);
                document.getElementById('qr-container-<?= $index ?>').innerHTML = 
                    '<div style="color: #999;">Error generating QR</div>';
            }
        })();
        <?php endforeach; ?>
    </script>
</body>
</html>
