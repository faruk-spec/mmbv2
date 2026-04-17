<?php
/**
 * Skeleton content area — included by skeleton-screen.php.
 * Uses $_skContent ('stats'|'table'|'form'|'grid') set by the parent.
 */
$_sc = $_skContent ?? 'table';
?>

<?php if ($_sc === 'stats'): ?>
<!-- ── Stat tiles + chart + table ── -->
<!-- Page title -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <div class="skeleton" style="height:28px;width:200px;border-radius:8px;margin-bottom:8px;"></div>
        <div class="skeleton" style="height:14px;width:280px;border-radius:4px;"></div>
    </div>
</div>

<!-- 4 stat cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
    <?php for ($i = 0; $i < 4; $i++): ?>
    <div style="background:var(--bg-secondary,#0c0c12);border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:22px 20px;">
        <div class="skeleton" style="height:12px;width:60%;border-radius:4px;margin-bottom:14px;"></div>
        <div class="skeleton" style="height:36px;width:80%;border-radius:8px;margin-bottom:8px;"></div>
        <div class="skeleton" style="height:11px;width:45%;border-radius:4px;"></div>
    </div>
    <?php endfor; ?>
</div>

<!-- Bar chart placeholder -->
<div style="background:var(--bg-secondary,#0c0c12);border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:20px;margin-bottom:20px;">
    <div class="skeleton" style="height:16px;width:180px;border-radius:4px;margin-bottom:20px;"></div>
    <div style="display:flex;align-items:flex-end;gap:8px;height:120px;">
        <?php $barHts = [45,70,55,90,60,80,100,65,75,50,85,70]; ?>
        <?php foreach ($barHts as $pct): ?>
        <div class="skeleton" style="flex:1;height:<?= $pct ?>%;border-radius:4px 4px 0 0;"></div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Quick table -->
<div style="background:var(--bg-secondary,#0c0c12);border:1px solid rgba(255,255,255,.07);border-radius:12px;overflow:hidden;">
    <div style="padding:16px 20px;border-bottom:1px solid rgba(255,255,255,.07);">
        <div class="skeleton" style="height:16px;width:160px;border-radius:4px;"></div>
    </div>
    <?php for ($r = 0; $r < 5; $r++): ?>
    <div style="display:flex;align-items:center;gap:16px;padding:14px 20px;border-bottom:1px solid rgba(255,255,255,.04);">
        <div class="skeleton" style="width:32px;height:32px;border-radius:50%;flex-shrink:0;"></div>
        <div class="skeleton" style="height:13px;flex:1;border-radius:4px;"></div>
        <div class="skeleton" style="height:13px;width:100px;border-radius:4px;"></div>
        <div class="skeleton" style="height:22px;width:70px;border-radius:20px;"></div>
    </div>
    <?php endfor; ?>
</div>

<?php elseif ($_sc === 'table'): ?>
<!-- ── Table page (page-title + filter + table rows) ── -->
<!-- Page title + action button -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <div class="skeleton" style="height:28px;width:220px;border-radius:8px;margin-bottom:8px;"></div>
        <div class="skeleton" style="height:13px;width:180px;border-radius:4px;"></div>
    </div>
    <div class="skeleton" style="height:38px;width:120px;border-radius:8px;"></div>
</div>

<!-- Filter bar -->
<div style="background:var(--bg-secondary,#0c0c12);border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:16px 20px;display:flex;gap:12px;margin-bottom:20px;">
    <div class="skeleton" style="height:38px;flex:1;max-width:260px;border-radius:8px;"></div>
    <div class="skeleton" style="height:38px;width:130px;border-radius:8px;"></div>
    <div class="skeleton" style="height:38px;width:100px;border-radius:8px;"></div>
    <div class="skeleton" style="height:38px;width:80px;border-radius:8px;"></div>
</div>

<!-- Table -->
<div style="background:var(--bg-secondary,#0c0c12);border:1px solid rgba(255,255,255,.07);border-radius:12px;overflow:hidden;">
    <!-- Header -->
    <div style="display:grid;grid-template-columns:2fr 2fr 1fr 1fr 1fr;gap:0;padding:12px 20px;border-bottom:1px solid rgba(255,255,255,.1);">
        <?php foreach ([120,140,70,70,80] as $w): ?>
        <div class="skeleton" style="height:12px;width:<?= $w ?>px;border-radius:4px;"></div>
        <?php endforeach; ?>
    </div>
    <!-- Rows -->
    <?php $rowWidths = [[80,90,55,60,70],[90,80,65,50,75],[75,85,60,70,55],[85,95,70,60,65],[70,75,50,80,60]]; ?>
    <?php foreach ($rowWidths as $ri => $cols): ?>
    <div style="display:grid;grid-template-columns:2fr 2fr 1fr 1fr 1fr;gap:0;padding:14px 20px;border-bottom:1px solid rgba(255,255,255,.04);align-items:center;">
        <!-- First col: avatar + text -->
        <div style="display:flex;align-items:center;gap:10px;">
            <div class="skeleton" style="width:30px;height:30px;border-radius:50%;flex-shrink:0;"></div>
            <div class="skeleton" style="height:13px;width:<?= $cols[0] ?>px;border-radius:4px;"></div>
        </div>
        <?php foreach (array_slice($cols, 1) as $cw): ?>
        <div class="skeleton" style="height:13px;width:<?= $cw ?>px;border-radius:4px;"></div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    <!-- Pagination row -->
    <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;">
        <div class="skeleton" style="height:13px;width:120px;border-radius:4px;"></div>
        <div style="display:flex;gap:6px;">
            <?php for ($p = 0; $p < 5; $p++): ?>
            <div class="skeleton" style="width:32px;height:32px;border-radius:6px;"></div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<?php elseif ($_sc === 'form'): ?>
<!-- ── Settings / form page ── -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <div class="skeleton" style="height:28px;width:200px;border-radius:8px;margin-bottom:8px;"></div>
        <div class="skeleton" style="height:13px;width:320px;border-radius:4px;"></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
    <div>
        <!-- Card 1: main form -->
        <div style="background:var(--bg-secondary,#0c0c12);border:1px solid rgba(255,255,255,.07);border-radius:12px;overflow:hidden;margin-bottom:20px;">
            <div style="padding:16px 20px;border-bottom:1px solid rgba(255,255,255,.07);">
                <div class="skeleton" style="height:16px;width:160px;border-radius:4px;"></div>
            </div>
            <div style="padding:20px;display:flex;flex-direction:column;gap:18px;">
                <?php for ($f = 0; $f < 5; $f++): ?>
                <div>
                    <div class="skeleton" style="height:12px;width:<?= [100,130,90,120,80][$f] ?>px;border-radius:4px;margin-bottom:8px;"></div>
                    <div class="skeleton" style="height:40px;border-radius:8px;"></div>
                </div>
                <?php endfor; ?>
                <div class="skeleton" style="height:40px;width:140px;border-radius:8px;"></div>
            </div>
        </div>

        <!-- Card 2 -->
        <div style="background:var(--bg-secondary,#0c0c12);border:1px solid rgba(255,255,255,.07);border-radius:12px;overflow:hidden;">
            <div style="padding:16px 20px;border-bottom:1px solid rgba(255,255,255,.07);">
                <div class="skeleton" style="height:16px;width:140px;border-radius:4px;"></div>
            </div>
            <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">
                <?php for ($f = 0; $f < 3; $f++): ?>
                <div style="display:flex;align-items:center;gap:12px;">
                    <div class="skeleton" style="width:20px;height:20px;border-radius:4px;flex-shrink:0;"></div>
                    <div style="flex:1;">
                        <div class="skeleton" style="height:13px;width:<?= [160,200,140][$f] ?>px;border-radius:4px;margin-bottom:6px;"></div>
                        <div class="skeleton" style="height:11px;width:<?= [240,180,210][$f] ?>px;border-radius:4px;"></div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- Right: preview card -->
    <div>
        <div style="background:var(--bg-secondary,#0c0c12);border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:20px;position:sticky;top:20px;">
            <div class="skeleton" style="height:16px;width:100px;border-radius:4px;margin-bottom:16px;"></div>
            <div class="skeleton" style="height:120px;border-radius:10px;margin-bottom:16px;"></div>
            <div class="skeleton" style="height:13px;width:90%;border-radius:4px;margin-bottom:8px;"></div>
            <div class="skeleton" style="height:13px;width:70%;border-radius:4px;margin-bottom:8px;"></div>
            <div class="skeleton" style="height:13px;width:80%;border-radius:4px;"></div>
        </div>
    </div>
</div>

<?php elseif ($_sc === 'auth'): ?>
<!-- ── Auth page (login / register / forgot-password) ── -->
<div style="max-width:420px;margin:60px auto 0;">
    <div style="background:var(--bg-secondary,#0c0c12);border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:36px 32px;">
        <!-- Title -->
        <div class="skeleton" style="height:26px;width:140px;border-radius:8px;margin:0 auto 28px;"></div>

        <!-- Field 1 (Email) -->
        <div style="margin-bottom:18px;">
            <div class="skeleton" style="height:12px;width:80px;border-radius:4px;margin-bottom:8px;"></div>
            <div class="skeleton" style="height:44px;border-radius:8px;"></div>
        </div>

        <!-- Field 2 (Password) -->
        <div style="margin-bottom:22px;">
            <div class="skeleton" style="height:12px;width:90px;border-radius:4px;margin-bottom:8px;"></div>
            <div class="skeleton" style="height:44px;border-radius:8px;"></div>
        </div>

        <!-- Remember + Forgot row -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;">
            <div style="display:flex;align-items:center;gap:8px;">
                <div class="skeleton" style="width:16px;height:16px;border-radius:3px;"></div>
                <div class="skeleton" style="height:11px;width:80px;border-radius:4px;"></div>
            </div>
            <div class="skeleton" style="height:11px;width:100px;border-radius:4px;"></div>
        </div>

        <!-- Submit button -->
        <div class="skeleton" style="height:46px;border-radius:10px;"></div>

        <!-- Footer link -->
        <div style="margin-top:20px;text-align:center;">
            <div class="skeleton" style="height:12px;width:200px;border-radius:4px;margin:0 auto;"></div>
        </div>
    </div>
</div>

<?php else: /* grid */ ?>
<!-- ── App / module grid ── -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <div class="skeleton" style="height:28px;width:180px;border-radius:8px;margin-bottom:8px;"></div>
        <div class="skeleton" style="height:13px;width:260px;border-radius:4px;"></div>
    </div>
</div>

<!-- Card with grid inside -->
<div style="background:var(--bg-secondary,#0c0c12);border:1px solid rgba(255,255,255,.07);border-radius:12px;overflow:hidden;margin-bottom:20px;">
    <div style="padding:14px 20px;border-bottom:1px solid rgba(255,255,255,.07);">
        <div class="skeleton" style="height:15px;width:150px;border-radius:4px;"></div>
    </div>
    <div style="padding:20px;display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:14px;">
        <?php for ($g = 0; $g < 12; $g++): ?>
        <div style="display:flex;flex-direction:column;align-items:center;background:var(--bg-card,#0f0f18);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:18px 10px 14px;">
            <div class="skeleton" style="width:52px;height:52px;border-radius:12px;margin-bottom:10px;"></div>
            <div class="skeleton" style="height:11px;width:70%;border-radius:4px;margin-bottom:6px;"></div>
            <div class="skeleton" style="height:9px;width:55%;border-radius:4px;"></div>
        </div>
        <?php endfor; ?>
    </div>
</div>

<!-- Second card: recent activity / info rows -->
<div style="background:var(--bg-secondary,#0c0c12);border:1px solid rgba(255,255,255,.07);border-radius:12px;overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid rgba(255,255,255,.07);">
        <div class="skeleton" style="height:15px;width:140px;border-radius:4px;"></div>
    </div>
    <?php for ($r = 0; $r < 4; $r++): ?>
    <div style="display:flex;align-items:center;gap:14px;padding:13px 20px;border-bottom:1px solid rgba(255,255,255,.04);">
        <div class="skeleton" style="width:36px;height:36px;border-radius:10px;flex-shrink:0;"></div>
        <div style="flex:1;">
            <div class="skeleton" style="height:13px;width:<?= [60,70,55,65][$r] ?>%;border-radius:4px;margin-bottom:6px;"></div>
            <div class="skeleton" style="height:11px;width:<?= [40,50,35,45][$r] ?>%;border-radius:4px;"></div>
        </div>
        <div class="skeleton" style="height:22px;width:65px;border-radius:20px;"></div>
    </div>
    <?php endfor; ?>
</div>
<?php endif; ?>
