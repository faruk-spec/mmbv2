<?php
/**
 * ConvertX – Plans & Pricing View
 */
$currentView = 'plan';
?>

<!-- Page header -->
<div class="page-header" style="margin-bottom:2rem;text-align:center;">
    <h1 style="font-size:2rem;font-weight:700;background:linear-gradient(135deg,var(--cx-primary),var(--cx-accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
        Plans &amp; Pricing
    </h1>
    <p style="color:var(--text-secondary);margin-top:.5rem;">
        Choose the plan that fits your needs. Upgrade or downgrade at any time.
    </p>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.5rem;max-width:960px;">

    <!-- Free -->
    <div class="card" style="transition:transform .25s,box-shadow .25s;"
         onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 12px 30px rgba(99,102,241,.15)'"
         onmouseout="this.style.transform='';this.style.boxShadow=''">
        <div style="text-align:center;margin-bottom:1.25rem;">
            <div style="width:52px;height:52px;margin:0 auto .75rem;background:rgba(99,102,241,.12);border-radius:.75rem;display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-seedling" style="font-size:1.4rem;color:var(--cx-primary);"></i>
            </div>
            <div style="font-size:1.2rem;font-weight:700;">Free</div>
            <div style="font-size:2.75rem;font-weight:800;background:linear-gradient(135deg,var(--cx-primary),var(--cx-accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1.1;">$0</div>
            <div style="font-size:.8rem;color:var(--text-secondary);">/ month</div>
            <p style="font-size:.8rem;color:var(--text-secondary);margin-top:.5rem;">Perfect for occasional use</p>
        </div>
        <ul style="list-style:none;font-size:.875rem;display:flex;flex-direction:column;gap:.5rem;margin-bottom:1.5rem;">
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>50 conversions/month</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>10 MB max file size</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>All basic formats</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>OCR (basic)</li>
            <li><i class="fa-solid fa-xmark" style="color:var(--text-secondary);margin-right:.5rem;"></i><span style="color:var(--text-secondary);">AI summarization</span></li>
            <li><i class="fa-solid fa-xmark" style="color:var(--text-secondary);margin-right:.5rem;"></i><span style="color:var(--text-secondary);">API access</span></li>
            <li><i class="fa-solid fa-xmark" style="color:var(--text-secondary);margin-right:.5rem;"></i><span style="color:var(--text-secondary);">Batch conversion</span></li>
        </ul>
        <a href="/register" class="btn btn-secondary" style="width:100%;justify-content:center;">Get Started Free</a>
    </div>

    <!-- Pro (featured) -->
    <div class="card" style="border:2px solid var(--cx-primary);position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;"
         onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 12px 30px rgba(99,102,241,.3)'"
         onmouseout="this.style.transform='';this.style.boxShadow=''">
        <!-- Most popular badge -->
        <div style="position:absolute;top:.875rem;right:.875rem;background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));color:#fff;font-size:.65rem;font-weight:700;padding:.25rem .65rem;border-radius:20px;letter-spacing:.04em;animation:cx-pulse-glow 2.5s ease-in-out infinite;">POPULAR</div>
        <div style="text-align:center;margin-bottom:1.25rem;">
            <div style="width:52px;height:52px;margin:0 auto .75rem;background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));border-radius:.75rem;display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-bolt" style="font-size:1.4rem;color:#fff;"></i>
            </div>
            <div style="font-size:1.2rem;font-weight:700;">Pro</div>
            <div style="font-size:2.75rem;font-weight:800;background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1.1;">$19</div>
            <div style="font-size:.8rem;color:var(--text-secondary);">/ month</div>
            <p style="font-size:.8rem;color:var(--text-secondary);margin-top:.5rem;">For power users &amp; small teams</p>
        </div>
        <ul style="list-style:none;font-size:.875rem;display:flex;flex-direction:column;gap:.5rem;margin-bottom:1.5rem;">
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>1,000 conversions/month</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>100 MB max file size</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>All formats</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>Advanced OCR + AI</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>Summarization &amp; translation</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>API access (60 req/min)</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>Webhooks &amp; batch conversion</li>
        </ul>
        <a href="/checkout?plan=pro" class="btn btn-primary" style="width:100%;justify-content:center;">Upgrade to Pro</a>
    </div>

    <!-- Enterprise -->
    <div class="card" style="border-color:rgba(6,182,212,.4);transition:transform .25s,box-shadow .25s;"
         onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 12px 30px rgba(6,182,212,.2)'"
         onmouseout="this.style.transform='';this.style.boxShadow=''">
        <div style="text-align:center;margin-bottom:1.25rem;">
            <div style="width:52px;height:52px;margin:0 auto .75rem;background:linear-gradient(135deg,#0891b2,#10b981);border-radius:.75rem;display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-building" style="font-size:1.4rem;color:#fff;"></i>
            </div>
            <div style="font-size:1.2rem;font-weight:700;">Enterprise</div>
            <div style="font-size:2.75rem;font-weight:800;background:linear-gradient(135deg,var(--cx-accent),#10b981);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1.1;">Custom</div>
            <div style="font-size:.8rem;color:var(--text-secondary);">pricing</div>
            <p style="font-size:.8rem;color:var(--text-secondary);margin-top:.5rem;">For agencies &amp; enterprises</p>
        </div>
        <ul style="list-style:none;font-size:.875rem;display:flex;flex-direction:column;gap:.5rem;margin-bottom:1.5rem;">
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>Unlimited conversions</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>500 MB max file size</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>All Pro features</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>Custom AI models</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>SLA &amp; dedicated support</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>300 req/min API</li>
            <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.5rem;"></i>GDPR &amp; SOC2 ready</li>
        </ul>
        <a href="/contact-sales" class="btn btn-secondary" style="width:100%;justify-content:center;border-color:var(--cx-accent);color:var(--cx-accent);">Contact Sales</a>
    </div>

</div>
