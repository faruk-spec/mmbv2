<?php
/** @var array $bill @var array $config */
$typeLabel = $config['bill_types'][$bill['bill_type']] ?? ucfirst($bill['bill_type']);
$sym = ['INR'=>'₹','USD'=>'$','EUR'=>'€','GBP'=>'£'][$bill['currency']] ?? $bill['currency'].' ';
$group = $config['bill_groups'][$bill['bill_type']] ?? 'invoice';
$c     = $config['bill_colors'][$bill['bill_type']] ?? '#37474f';
$items    = $bill['items'];
$subtotal = (float)$bill['subtotal'];
$taxPct   = (float)$bill['tax_percent'];
$taxAmt   = (float)$bill['tax_amount'];
$discount = (float)$bill['discount_amount'];
$total    = (float)$bill['total_amount'];
$billDate = $bill['bill_date'] ? date('d M Y', strtotime($bill['bill_date'])) : '';
$td = json_decode($bill['template_data'] ?? '{}', true) ?: [];
$tplStyle = $td['template_style'] ?? '1';
?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($typeLabel) ?> - <?= htmlspecialchars($bill['bill_number']) ?></title>
<style>
@import url('https://fonts.googleapis.com/css2?family=VT323&family=Inter:wght@400;500;600;700;900&family=Playfair+Display:wght@700&display=swap');
@page { size: <?= $group==='thermal' ? '80mm auto' : 'A4 portrait' ?>; margin: <?= $group==='thermal' ? '0' : '10mm 12mm' ?>; }
* { box-sizing: border-box; }
body { margin: 0; padding: 0; background: #fff; font-family: 'Inter', Arial, sans-serif; }
@media screen { body { background: #e8e8e8; padding-top: 52px; padding-bottom: 20px; } }
.bill-action-bar { display: none; }
@media screen {
    .bill-action-bar {
        display: flex; align-items: center; gap: 8px;
        position: fixed; top: 0; left: 0; right: 0; z-index: 100;
        padding: 8px 16px; background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,.15);
    }
}
.bill-action-bar .bill-info { font-size: 12px; color: #555; margin-right: auto; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.bill-action-bar button, .bill-action-bar a {
    padding: 6px 14px; border: none; border-radius: 5px; font-size: 13px;
    cursor: pointer; font-weight: 600; white-space: nowrap;
    display: inline-flex; align-items: center; gap: 5px; text-decoration: none;
}
.btn-download { background: #f59e0b; color: #fff; }
.btn-download:hover { background: #d97706; }
.btn-download:disabled { opacity: .65; cursor: not-allowed; }
.btn-close-win { background: #e0e0e0; color: #333; }
.btn-close-win:hover { background: #bdbdbd; }
@media print {
    .bill-action-bar { display: none !important; }
    body { background: #fff !important; padding: 0 !important; margin: 0 !important; }
    #billDocument { max-width: 100% !important; width: 100% !important; margin: 0 !important; box-shadow: none !important; }
    #billDocument * { box-shadow: none !important; }
}
</style>
</head>
<body>
<div class="bill-action-bar">
    <span class="bill-info"><?= htmlspecialchars($typeLabel) ?> &mdash; #<?= htmlspecialchars($bill['bill_number']) ?></span>
    <button class="btn-close-win" onclick="history.length>1?history.back():window.location.href='/projects/billx/history'">&#8592; Back</button>
    <button class="btn-download" id="downloadBtn" onclick="downloadBillPDF()">&#8659; Download PDF</button>
</div>
<div id="billDocument" style="<?= $group==='thermal' ? 'width:80mm;max-width:80mm;' : 'max-width:700px;' ?> margin:0 auto;">


<?php include __DIR__ . '/_bill_render.php'; ?>

</div><!-- /billDocument -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous"
    onerror="document.getElementById('downloadBtn').title='PDF library failed to load. Ensure internet access is available.'"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" crossorigin="anonymous"
    onerror="document.getElementById('downloadBtn').title='PDF library failed to load. Ensure internet access is available.'"></script>
<script>
var _billGroup = <?= json_encode($group) ?>;
var _billFilename = <?= json_encode(preg_replace('/[^a-zA-Z0-9._-]/', '-', ($typeLabel ?? 'bill') . '-' . ($bill['bill_number'] ?? 'bill')) . '.pdf') ?>;

async function downloadBillPDF() {
    if (typeof html2canvas === 'undefined' || typeof window.jspdf === 'undefined') {
        alert('PDF download library not loaded. Please check your internet connection and refresh the page.');
        return;
    }
    var btn = document.getElementById('downloadBtn');
    var origText = btn.innerHTML;
    btn.innerHTML = '&#8987; Generating&hellip;';
    btn.disabled = true;
    try {
        var el = document.getElementById('billDocument');
        // Temporarily hide action bar so it doesn't appear in PDF
        var bar = document.querySelector('.bill-action-bar');
        var barOrigDisplay = bar ? bar.style.display : '';
        if (bar) bar.style.display = 'none';

        // Ensure page is scrolled to top so html2canvas captures from the beginning
        var origScrollX = window.scrollX, origScrollY = window.scrollY;
        window.scrollTo(0, 0);

        // Allow a paint frame after scroll so the DOM is in the right state
        await new Promise(function(r) { requestAnimationFrame(function() { requestAnimationFrame(r); }); });

        // Measure the FULL content dimensions (not just visible viewport)
        var elRect   = el.getBoundingClientRect();
        var fullW    = el.scrollWidth  || elRect.width;
        var fullH    = el.scrollHeight || elRect.height;
        // windowWidth/Height must be at least as large as the element so nothing is clipped
        var winW     = Math.max(document.documentElement.scrollWidth,  fullW);
        var winH     = Math.max(document.documentElement.scrollHeight, fullH);

        var canvas = await html2canvas(el, {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            logging: false,
            backgroundColor: '#ffffff',
            scrollX: 0,
            scrollY: 0,
            x: 0,
            y: 0,
            width:        fullW,
            height:       fullH,
            windowWidth:  winW,
            windowHeight: winH
        });
        if (bar) bar.style.display = barOrigDisplay;
        window.scrollTo(origScrollX, origScrollY);

        var imgData = canvas.toDataURL('image/png');
        var jsPDF = window.jspdf.jsPDF;
        var pdf;
        var margin = 8; // mm

        if (_billGroup === 'thermal') {
            // 80mm roll: exact width, height proportional — no cropping
            var mmWidth = 80;
            var mmHeight = (canvas.height / canvas.width) * mmWidth;
            pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: [mmWidth, mmHeight] });
            pdf.addImage(imgData, 'PNG', 0, 0, mmWidth, mmHeight);
        } else {
            // Custom-height page that fits the entire bill — no cropping regardless of item count
            var pageW = 210; // A4 width in mm
            var imgW  = pageW - margin * 2;
            var imgH  = (canvas.height / canvas.width) * imgW;
            var pageH = imgH + margin * 2;
            pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: [pageW, pageH] });
            pdf.addImage(imgData, 'PNG', margin, margin, imgW, imgH);
        }
        pdf.save(_billFilename);
    } catch (e) {
        console.error('PDF error:', e);
        alert('PDF generation failed: ' + e.message);
    } finally {
        btn.innerHTML = origText;
        btn.disabled = false;
    }
}

(function() {
    var params = new URLSearchParams(window.location.search);
    // ?download=1 triggers auto PDF download; ?autoprint=1 kept for backward compat from view.php links
    if (params.get('download') === '1' || params.get('autoprint') === '1') {
        var trigger = function() { setTimeout(downloadBillPDF, 1200); };
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(trigger);
        } else {
            window.addEventListener('load', function() { setTimeout(downloadBillPDF, 800); });
        }
    }
})();
</script>
</body>
</html>
