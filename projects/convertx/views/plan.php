<?php
/**
 * ConvertX – Plans & Pricing View
 */
$currentView = 'plan';
?>

<!-- Page header -->
<div class="page-header">
    <h1>Plans &amp; Pricing</h1>
    <p>Choose the plan that fits your needs. Upgrade or downgrade at any time.</p>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1.5rem;max-width:960px;">

    <!-- Free -->
    <div class="cx-price-card">
        <div class="price-icon" style="width:52px;height:52px;background:rgba(99,102,241,.12);border-radius:.75rem;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
            <i class="fa-solid fa-seedling" style="font-size:1.4rem;color:var(--cx-primary);"></i>
        </div>
        <div class="plan-name">Free</div>
        <div class="plan-price" style="background:linear-gradient(135deg,var(--cx-primary),var(--cx-accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">$0</div>
        <div class="plan-period">/ month</div>
        <div class="plan-tagline">Perfect for occasional use</div>
        <ul>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> 50 conversions/month</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> 10 MB max file size</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> All basic formats</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> OCR (basic)</li>
            <li><i class="fa-solid fa-xmark" style="color:var(--text-muted);flex-shrink:0;"></i> <span style="color:var(--text-secondary);">AI summarization</span></li>
            <li><i class="fa-solid fa-xmark" style="color:var(--text-muted);flex-shrink:0;"></i> <span style="color:var(--text-secondary);">API access</span></li>
            <li><i class="fa-solid fa-xmark" style="color:var(--text-muted);flex-shrink:0;"></i> <span style="color:var(--text-secondary);">Batch conversion</span></li>
        </ul>
        <a href="/register" class="btn btn-secondary" style="width:100%;justify-content:center;">Get Started Free</a>
    </div>

    <!-- Pro (featured) -->
    <div class="cx-price-card cx-price-featured">
        <!-- Most popular badge -->
        <div style="position:absolute;top:.875rem;right:.875rem;background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));color:#fff;font-size:.65rem;font-weight:700;padding:.25rem .65rem;border-radius:20px;letter-spacing:.04em;animation:cx-pulse-glow 2.5s ease-in-out infinite;">POPULAR</div>
        <div class="price-icon" style="width:52px;height:52px;background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));border-radius:.75rem;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
            <i class="fa-solid fa-bolt" style="font-size:1.4rem;color:#fff;"></i>
        </div>
        <div class="plan-name">Pro</div>
        <div class="plan-price" style="background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">$19</div>
        <div class="plan-period">/ month</div>
        <div class="plan-tagline">For power users &amp; small teams</div>
        <ul>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> 1,000 conversions/month</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> 100 MB max file size</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> All formats</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> Advanced OCR + AI</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> Summarization &amp; translation</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> API access (60 req/min)</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> Webhooks &amp; batch conversion</li>
        </ul>
        <a href="/checkout?plan=pro" class="btn btn-primary" style="width:100%;justify-content:center;">Upgrade to Pro</a>
    </div>

    <!-- Enterprise -->
    <div class="cx-price-card" style="border-color:rgba(6,182,212,.35);">
        <div class="price-icon" style="width:52px;height:52px;background:linear-gradient(135deg,#0891b2,#10b981);border-radius:.75rem;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
            <i class="fa-solid fa-building" style="font-size:1.4rem;color:#fff;"></i>
        </div>
        <div class="plan-name">Enterprise</div>
        <div class="plan-price" style="background:linear-gradient(135deg,var(--cx-accent),#10b981);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Custom</div>
        <div class="plan-period">pricing</div>
        <div class="plan-tagline">For agencies &amp; enterprises</div>
        <ul>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> Unlimited conversions</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> 500 MB max file size</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> All Pro features</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> Custom AI models</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> SLA &amp; dedicated support</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> 300 req/min API</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);flex-shrink:0;"></i> GDPR &amp; SOC2 ready</li>
        </ul>
        <a href="/contact-sales" class="btn btn-secondary" style="width:100%;justify-content:center;border-color:var(--cx-accent);color:var(--cx-accent);">Contact Sales</a>
    </div>

</div>
