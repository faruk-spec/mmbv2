<?php
/**
 * Admin Support Settings Page
 * Ticket ID configuration + Live Support page content editor
 */
use Core\View;
View::extend('admin');
View::section('content');
?>

<div style="padding:28px 32px;max-width:820px;">

  <div style="margin-bottom:28px;">
    <h1 style="font-size:1.4rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 4px;display:flex;align-items:center;gap:10px;">
      <i class="fas fa-sliders" style="color:var(--cyan,#00f0ff);"></i> Support Settings
    </h1>
    <p style="color:var(--text-secondary,#8892a6);margin:0;font-size:.85rem;">Configure ticket numbering and live support page content.</p>
  </div>

  <?php if (!empty($_SESSION['_flash']['success'])): ?>
  <div style="background:rgba(0,255,136,.08);border:1px solid rgba(0,255,136,.2);color:#00ff88;padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:.88rem;">
    <?= htmlspecialchars($_SESSION['_flash']['success']) ?>
    <?php unset($_SESSION['_flash']['success']); ?>
  </div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['_flash']['error'])): ?>
  <div style="background:rgba(255,107,107,.08);border:1px solid rgba(255,107,107,.2);color:#ff6b6b;padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:.88rem;">
    <?= htmlspecialchars($_SESSION['_flash']['error']) ?>
    <?php unset($_SESSION['_flash']['error']); ?>
  </div>
  <?php endif; ?>

  <form method="POST" action="/admin/support/settings">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

    <!-- ── Ticket Numbering ────────────────────────────────────────────────── -->
    <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;padding:22px 24px;margin-bottom:24px;">
      <h2 style="font-size:1rem;font-weight:600;color:var(--text-primary,#e8eefc);margin:0 0 6px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-hashtag" style="color:var(--magenta,#ff2ec4);font-size:.9rem;"></i> Ticket Numbering
      </h2>
      <p style="color:var(--text-secondary,#8892a6);font-size:.82rem;margin:0 0 18px;">
        Set the starting ticket number. Ticket IDs are always displayed as 7-digit numbers (e.g. <code style="background:rgba(255,255,255,.06);padding:1px 5px;border-radius:4px;">#<?= str_pad($settings['ticket_id_start'] ?: '1234567', 7, '0', STR_PAD_LEFT) ?></code>).
        The value must be larger than the current maximum ticket ID.
        Current AUTO_INCREMENT: <strong><?= number_format($currentAutoIncrement) ?></strong>.
      </p>
      <label style="display:block;margin-bottom:6px;color:var(--text-secondary,#8892a6);font-size:.82rem;font-weight:500;">Starting Ticket Number</label>
      <div style="display:flex;gap:10px;align-items:center;">
        <input type="number" name="ticket_id_start" min="1" max="9999999"
               value="<?= htmlspecialchars($settings['ticket_id_start'] ?? '') ?>"
               placeholder="e.g. 1234567"
               style="width:180px;padding:9px 12px;background:var(--bg-secondary,#0c0c12);border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:8px;color:var(--text-primary,#e8eefc);font-size:.9rem;">
        <span style="color:var(--text-secondary,#8892a6);font-size:.8rem;">Leave blank to keep current setting.</span>
      </div>
    </div>

    <!-- ── Live Support Page Content ──────────────────────────────────────── -->
    <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;padding:22px 24px;margin-bottom:24px;">
      <h2 style="font-size:1rem;font-weight:600;color:var(--text-primary,#e8eefc);margin:0 0 6px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-headset" style="color:var(--cyan,#00f0ff);font-size:.9rem;"></i> Live Support Page Content
        <a href="/support/live" target="_blank" style="font-size:.75rem;font-weight:400;color:var(--cyan,#00f0ff);text-decoration:none;margin-left:auto;opacity:.7;">
          <i class="fas fa-arrow-up-right-from-square"></i> Preview page
        </a>
      </h2>
      <p style="color:var(--text-secondary,#8892a6);font-size:.82rem;margin:0 0 18px;">
        Edit the content shown on <code style="background:rgba(255,255,255,.06);padding:1px 5px;border-radius:4px;">/support/live</code>. Leave blank to use defaults.
      </p>

      <?php
      $fields = [
          'live_support_title'         => ['Page Title',       'Live Support',                          'text'],
          'live_support_tagline'       => ['Tagline / Intro',  'Chat live with our support team for immediate assistance.', 'text'],
          'live_support_response_time' => ['Response Time Text', 'Usually under 5 minutes during business hours.', 'text'],
          'live_support_hours'         => ['Business Hours',   'Mon–Fri: 9AM–6PM (UTC). Weekends limited.',  'text'],
          'live_support_extra_note'    => ['Bottom Note',      'For non-urgent issues, please create a ticket for a faster, tracked response.', 'textarea'],
      ];
      foreach ($fields as $key => [$label, $placeholder, $type]):
          $val = htmlspecialchars($settings[$key] ?? '');
      ?>
      <div style="margin-bottom:14px;">
        <label style="display:block;margin-bottom:5px;color:var(--text-secondary,#8892a6);font-size:.82rem;font-weight:500;"><?= $label ?></label>
        <?php if ($type === 'textarea'): ?>
        <textarea name="<?= $key ?>" rows="2"
                  placeholder="<?= htmlspecialchars($placeholder) ?>"
                  style="width:100%;padding:9px 12px;background:var(--bg-secondary,#0c0c12);border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:8px;color:var(--text-primary,#e8eefc);font-size:.88rem;resize:vertical;box-sizing:border-box;"><?= $val ?></textarea>
        <?php else: ?>
        <input type="text" name="<?= $key ?>"
               value="<?= $val ?>"
               placeholder="<?= htmlspecialchars($placeholder) ?>"
               style="width:100%;padding:9px 12px;background:var(--bg-secondary,#0c0c12);border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:8px;color:var(--text-primary,#e8eefc);font-size:.88rem;box-sizing:border-box;">
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ── Mail Templates link ──────────────────────────────────────────────── -->
    <div style="background:rgba(0,240,255,.04);border:1px solid rgba(0,240,255,.12);border-radius:10px;padding:14px 18px;margin-bottom:24px;display:flex;align-items:center;gap:12px;">
      <i class="fas fa-envelope" style="color:var(--cyan,#00f0ff);font-size:1rem;"></i>
      <div>
        <div style="font-weight:600;color:var(--text-primary,#e8eefc);font-size:.88rem;margin-bottom:2px;">Support Email Templates</div>
        <div style="color:var(--text-secondary,#8892a6);font-size:.8rem;">Edit the HTML templates used for ticket creation, replies, status changes, and closures.</div>
      </div>
      <a href="/admin/mail/templates" style="margin-left:auto;padding:7px 16px;background:rgba(0,240,255,.12);color:var(--cyan,#00f0ff);border:1px solid rgba(0,240,255,.25);border-radius:7px;text-decoration:none;font-size:.82rem;font-weight:600;white-space:nowrap;">
        <i class="fas fa-arrow-right"></i> Go to Templates
      </a>
    </div>

    <button type="submit" style="padding:10px 26px;background:linear-gradient(135deg,var(--cyan,#00f0ff),var(--magenta,#ff2ec4));border:none;border-radius:8px;color:white;font-weight:700;font-size:.92rem;cursor:pointer;">
      <i class="fas fa-save"></i> Save Settings
    </button>
  </form>
</div>

<?php View::endSection(); ?>
