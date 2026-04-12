<?php
/**
 * ConvertX – Upscale Image (Coming Soon)
 */
$currentView = 'img-upscale';
?>

<div class="page-header">
    <h1>
        <i class="fa-solid fa-arrow-up-right-dots" style="color:var(--cx-primary);"></i>
        Upscale Image
        <span style="font-size:.65rem;font-weight:700;background:var(--cx-primary);color:#fff;padding:.15rem .5rem;border-radius:.35rem;vertical-align:middle;margin-left:.5rem;letter-spacing:.05em;">NEW — COMING SOON</span>
    </h1>
    <p>AI-powered image upscaling — enlarge images up to 4× while preserving sharpness and detail</p>
</div>

<div style="max-width:640px;margin:0 auto;">
    <div class="card" style="text-align:center;padding:2.5rem 2rem;">

        <!-- Icon / illustration -->
        <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,var(--cx-primary),color-mix(in srgb,var(--cx-primary) 60%,#7c3aed));display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;box-shadow:0 8px 30px rgba(99,102,241,.35);">
            <i class="fa-solid fa-arrow-up-right-dots" style="font-size:2rem;color:#fff;"></i>
        </div>

        <h2 style="margin:0 0 .5rem;font-size:1.5rem;font-weight:700;">AI-Powered Image Upscaling</h2>
        <p style="color:var(--text-secondary);font-size:.92rem;margin:0 0 1.5rem;line-height:1.6;">
            Enlarge your images with high resolution. Easily increase the size of your JPG and PNG images
            while maintaining visual quality — powered by advanced AI super-resolution technology.
        </p>

        <!-- Feature pills -->
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;justify-content:center;margin-bottom:1.5rem;">
            <span class="cx-pill"><i class="fa-solid fa-magnifying-glass-plus"></i> Up to 4× upscaling</span>
            <span class="cx-pill"><i class="fa-solid fa-image"></i> JPG &amp; PNG support</span>
            <span class="cx-pill"><i class="fa-solid fa-wand-magic-sparkles"></i> AI super-resolution</span>
            <span class="cx-pill"><i class="fa-solid fa-bolt"></i> Fast processing</span>
        </div>

        <!-- Coming soon badge -->
        <div style="display:inline-flex;align-items:center;gap:.5rem;padding:.625rem 1.25rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:2rem;font-size:.85rem;font-weight:600;color:var(--cx-primary);margin-bottom:1.5rem;">
            <i class="fa-solid fa-clock"></i>
            We're working on it — coming soon
        </div>

        <!-- Notify hint -->
        <p style="font-size:.78rem;color:var(--text-muted);margin:0;">
            <i class="fa-solid fa-circle-info"></i>
            This feature will integrate with an AI upscaling API. Configure your API key in
            <a href="/admin/projects/convertx/image-tools-settings" style="color:var(--cx-primary);">Admin → ConvertX → Image Tools APIs</a>.
        </p>

    </div>
</div>

<style>
.cx-pill {
    display:inline-flex; align-items:center; gap:.35rem; padding:.3rem .75rem;
    background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:2rem;
    font-size:.78rem; color:var(--text-secondary);
}
</style>
