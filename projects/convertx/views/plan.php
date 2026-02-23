<?php
/**
 * ConvertX – Plans & Pricing View
 */
$currentView = 'plan';
?>

<div style="max-width:900px;">
    <p style="font-size:.9rem;color:var(--text-muted);margin-bottom:2rem;">
        Choose the plan that fits your needs. Upgrade or downgrade at any time.
    </p>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.5rem;">

        <!-- Free -->
        <div class="card" style="border-color:var(--border);">
            <div style="font-size:1.25rem;font-weight:700;margin-bottom:.25rem;">Free</div>
            <div style="font-size:2.5rem;font-weight:800;color:var(--cx-primary);margin-bottom:.5rem;">$0<span style="font-size:1rem;color:var(--text-muted);">/mo</span></div>
            <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:1.25rem;">Perfect for occasional use</p>
            <ul style="list-style:none;font-size:.875rem;display:flex;flex-direction:column;gap:.5rem;margin-bottom:1.5rem;">
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>50 conversions/month</li>
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>10 MB max file size</li>
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>All basic formats</li>
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>OCR (basic)</li>
                <li><i class="fa-solid fa-xmark" style="color:var(--text-muted);margin-right:.4rem;"></i>AI summarization</li>
                <li><i class="fa-solid fa-xmark" style="color:var(--text-muted);margin-right:.4rem;"></i>API access</li>
                <li><i class="fa-solid fa-xmark" style="color:var(--text-muted);margin-right:.4rem;"></i>Webhooks</li>
                <li><i class="fa-solid fa-xmark" style="color:var(--text-muted);margin-right:.4rem;"></i>Batch conversion</li>
            </ul>
            <a href="/register" class="btn btn-secondary" style="width:100%;justify-content:center;">Get Started Free</a>
        </div>

        <!-- Pro -->
        <div class="card" style="border-color:var(--cx-primary);position:relative;overflow:hidden;">
            <div style="position:absolute;top:.75rem;right:.75rem;background:var(--cx-primary);color:#fff;font-size:.7rem;font-weight:700;padding:.2rem .6rem;border-radius:20px;">POPULAR</div>
            <div style="font-size:1.25rem;font-weight:700;margin-bottom:.25rem;">Pro</div>
            <div style="font-size:2.5rem;font-weight:800;color:var(--cx-primary);margin-bottom:.5rem;">$19<span style="font-size:1rem;color:var(--text-muted);">/mo</span></div>
            <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:1.25rem;">For power users and small teams</p>
            <ul style="list-style:none;font-size:.875rem;display:flex;flex-direction:column;gap:.5rem;margin-bottom:1.5rem;">
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>1,000 conversions/month</li>
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>100 MB max file size</li>
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>All formats</li>
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>Advanced OCR + AI</li>
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>Summarization &amp; translation</li>
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>API access (60 req/min)</li>
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>Webhooks</li>
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>Batch conversion</li>
            </ul>
            <a href="/checkout?plan=pro" class="btn btn-primary" style="width:100%;justify-content:center;">Upgrade to Pro</a>
        </div>

        <!-- Enterprise -->
        <div class="card" style="border-color:var(--cx-accent);">
            <div style="font-size:1.25rem;font-weight:700;margin-bottom:.25rem;">Enterprise</div>
            <div style="font-size:2.5rem;font-weight:800;color:var(--cx-accent);margin-bottom:.5rem;">Custom</div>
            <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:1.25rem;">For teams, agencies, and enterprises</p>
            <ul style="list-style:none;font-size:.875rem;display:flex;flex-direction:column;gap:.5rem;margin-bottom:1.5rem;">
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>Unlimited conversions</li>
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>500 MB max file size</li>
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>All Pro features</li>
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>Custom AI models</li>
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>SLA &amp; dedicated support</li>
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>300 req/min API</li>
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>GDPR &amp; SOC2 ready</li>
                <li><i class="fa-solid fa-check" style="color:var(--cx-success);margin-right:.4rem;"></i>White-label option</li>
            </ul>
            <a href="/contact-sales" class="btn btn-secondary" style="width:100%;justify-content:center;border-color:var(--cx-accent);color:var(--cx-accent);">Contact Sales</a>
        </div>

    </div>
</div>
