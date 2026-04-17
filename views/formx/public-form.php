<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Form') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        :root{--bg:#07070e;--card:#0f0f1a;--border:rgba(255,255,255,.09);--cyan:#3b82f6;--purple:#8b5cf6;--green:#22c55e;--red:#ef4444;--orange:#f59e0b;--text:#e2e8f5;--muted:#7a8499;}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;flex-direction:column;align-items:center;padding:32px 14px 48px;}
        body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 60% 40% at 15% 0%,rgba(59,130,246,.06) 0%,transparent 70%),radial-gradient(ellipse 50% 40% at 85% 100%,rgba(139,92,246,.05) 0%,transparent 70%);pointer-events:none;z-index:-1;}
        .wrap{width:100%;max-width:600px;}
        /* Header */
        .fh{margin-bottom:22px;}
        .fh h1{font-size:1.55rem;font-weight:700;background:linear-gradient(135deg,var(--cyan),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1.2;margin-bottom:6px;}
        .fh p{color:var(--muted);font-size:.875rem;line-height:1.6;}
        /* Card */
        .fc{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:24px;}
        /* Form elements */
        .fg{margin-bottom:16px;}
        .fl{display:block;font-size:.8rem;font-weight:600;color:var(--muted);margin-bottom:5px;letter-spacing:.01em;}
        .fl .req{color:var(--purple);margin-left:3px;}
        .fi,select.fi,textarea.fi{width:100%;padding:9px 12px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:.875rem;font-family:'Inter',sans-serif;outline:none;transition:border-color .18s,background .18s;}
        .fi:focus{border-color:var(--cyan);background:rgba(59,130,246,.03);}
        textarea.fi{resize:vertical;min-height:88px;}
        select.fi option{background:#0f0f1a;}
        .chk{display:flex;align-items:center;gap:9px;margin-bottom:7px;}
        .chk input[type=checkbox],.chk input[type=radio]{width:16px;height:16px;cursor:pointer;accent-color:var(--cyan);flex-shrink:0;}
        .chk label{font-size:.875rem;cursor:pointer;}
        hr.div{border:none;border-top:1px solid var(--border);margin:18px 0;}
        .fhead{font-size:1.05rem;font-weight:700;color:var(--text);margin-bottom:3px;margin-top:6px;}
        .fpara{color:var(--muted);font-size:.875rem;line-height:1.6;}
        /* Ratings */
        .rtg{display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:6px;}
        .rtg input[type=radio]{display:none;}
        .rtg label{font-size:1.4rem;cursor:pointer;color:rgba(255,255,255,.2);transition:color .12s;}
        .rtg label:hover,.rtg label:hover~label{color:var(--orange);}
        .rtg input:checked~label{color:var(--orange);}
        /* Buttons */
        .btn-sub{width:100%;padding:11px;background:linear-gradient(135deg,var(--cyan),var(--purple));border:none;border-radius:9px;color:#fff;font-size:.95rem;font-weight:700;cursor:pointer;font-family:'Inter',sans-serif;transition:opacity .2s,transform .1s;margin-top:6px;}
        .btn-sub:hover{opacity:.9;transform:translateY(-1px);}
        .btn-sub:disabled{opacity:.6;cursor:default;transform:none;}
        .btn-draft{width:100%;padding:9px;background:transparent;border:1px solid rgba(153,69,255,.4);border-radius:9px;color:var(--purple);font-size:.835rem;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;transition:all .18s;margin-top:8px;}
        .btn-draft:hover{background:rgba(153,69,255,.1);border-color:var(--purple);}
        /* Alerts */
        .al{padding:11px 14px;border-radius:9px;margin-bottom:16px;font-size:.875rem;display:flex;align-items:flex-start;gap:9px;}
        .al-ok{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);color:var(--green);}
        .al-err{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:var(--red);}
        /* Draft banner */
        .draft-banner{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 13px;background:rgba(153,69,255,.09);border:1px solid rgba(153,69,255,.25);border-radius:9px;font-size:.82rem;color:var(--purple);margin-bottom:14px;}
        /* Password gate */
        .pgw{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:28px;text-align:center;}
        .pgw .ico{font-size:2.2rem;margin-bottom:12px;}
        .pgw h2{font-size:1.1rem;font-weight:700;margin-bottom:6px;}
        .pgw p{color:var(--muted);font-size:.875rem;margin-bottom:18px;}
        /* Expiry banner */
        .exp-banner{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:.85rem;color:var(--orange);display:flex;align-items:center;gap:9px;}
        /* Expired state */
        .exp-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:36px 24px;text-align:center;}
        .exp-card .ico{font-size:2.5rem;margin-bottom:14px;opacity:.7;}
        .exp-card h2{font-size:1.2rem;font-weight:700;margin-bottom:6px;}
        .exp-card p{color:var(--muted);font-size:.875rem;}
        /* Success state */
        .suc-card{background:var(--card);border:1px solid rgba(34,197,94,.15);border-radius:14px;padding:36px 24px;text-align:center;}
        .suc-card .ico{font-size:3rem;margin-bottom:16px;}
        .suc-card h2{font-size:1.2rem;font-weight:700;margin-bottom:8px;color:var(--green);}
        .suc-card p{color:var(--muted);font-size:.9rem;line-height:1.6;}
        .suc-card .btn-sub{max-width:220px;margin:20px auto 0;}
        /* Countdown */
        .cdown{display:inline-flex;gap:12px;margin-top:14px;justify-content:center;}
        .cdown-unit{text-align:center;}
        .cdown-num{font-size:1.6rem;font-weight:700;color:var(--orange);line-height:1;display:block;}
        .cdown-lbl{font-size:.65rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);}
        /* Unavailable / 404 states */
        .unav-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:40px 24px;text-align:center;}
        .unav-card .unav-ico{font-size:3rem;margin-bottom:16px;display:block;}
        .unav-card h2{font-size:1.25rem;font-weight:700;margin-bottom:8px;}
        .unav-card p{color:var(--muted);font-size:.9rem;line-height:1.6;max-width:380px;margin:0 auto;}
        .unav-card .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;margin-bottom:14px;}
        .badge-draft{background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.3);color:var(--orange);}
        .badge-inactive{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:var(--red);}
        .unav-promo{margin-top:24px;padding-top:20px;border-top:1px solid var(--border);}
        .unav-promo p{font-size:.82rem;color:var(--muted);margin-bottom:10px;}
        .btn-promo{display:inline-flex;align-items:center;gap:7px;padding:9px 20px;background:linear-gradient(135deg,var(--cyan),var(--purple));border:none;border-radius:9px;color:#fff;font-size:.875rem;font-weight:700;cursor:pointer;text-decoration:none;transition:opacity .2s,transform .1s;}
        .btn-promo:hover{opacity:.9;transform:translateY(-1px);text-decoration:none;color:#fff;}
        /* Confirm modal */
        .cfm-overlay{position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:9999;padding:16px;}
        .cfm-box{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:28px 24px;max-width:380px;width:100%;text-align:center;box-shadow:0 24px 64px rgba(0,0,0,.5);}
        .cfm-ico{font-size:2.4rem;margin-bottom:14px;}
        .cfm-box h2{font-size:1.1rem;font-weight:700;margin-bottom:8px;}
        .cfm-box p{color:var(--muted);font-size:.875rem;line-height:1.6;margin-bottom:20px;}
        .cfm-btns{display:flex;gap:10px;}
        .cfm-btns .btn-sub{margin:0;flex:1;}
        .cfm-cancel{flex:1;padding:11px;background:transparent;border:1px solid var(--border);border-radius:9px;color:var(--muted);font-size:.9rem;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;transition:all .18s;}
        .cfm-cancel:hover{border-color:var(--red);color:var(--red);}
    </style>
</head>
<body>
<?php use Core\Helpers; ?>
<div class="wrap">

    <?php if (!empty($unavailable)): ?>
    <!-- ── Unavailable / Draft / Inactive state ───────────────────────────── -->
    <div class="fh">
        <h1><?= !empty($form['title']) ? htmlspecialchars($form['title']) : 'Form' ?></h1>
    </div>
    <div class="unav-card">
        <?php if ($unavailable === 'notfound'): ?>
        <span class="unav-ico">🔍</span>
        <h2>Form Not Found</h2>
        <p>This form link is invalid or the form has been removed. Double-check the link and try again.</p>
        <?php elseif ($unavailable === 'draft'): ?>
        <span class="unav-ico">🔧</span>
        <div class="badge badge-draft">Draft</div>
        <h2>Form Under Construction</h2>
        <p>This form is not ready yet. Check back later or contact the form owner.</p>
        <?php else: /* inactive */ ?>
        <span class="unav-ico">🚫</span>
        <div class="badge badge-inactive">Closed</div>
        <h2>Form Closed</h2>
        <p>This form is no longer accepting responses. It may have been closed by the owner.</p>
        <?php endif; ?>
        <div class="unav-promo">
            <p>Need to create your own forms?</p>
            <a href="/" class="btn-promo"><i class="fas fa-wpforms"></i> Try FormX — Free</a>
        </div>
    </div>
    <p class="pwr">Powered by <strong>FormX</strong></p>
    </div></body></html>
    <?php return; ?>
    <?php endif; ?>

    <div class="fh">
        <h1><?= htmlspecialchars($form['title']) ?></h1>
        <?php if (!empty($form['description'])): ?>
        <p><?= nl2br(htmlspecialchars($form['description'])) ?></p>
        <?php endif; ?>
    </div>

    <?php if (Helpers::hasFlash('error')): ?>
    <div class="al al-err"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(Helpers::getFlash('error')) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
    <!-- ── Success screen ─────────────────────────────────────────────────── -->
    <div class="suc-card">
        <div class="ico">✅</div>
        <h2>Submitted!</h2>
        <p><?= htmlspecialchars($success) ?></p>
        <button class="btn-sub" onclick="window.location.reload()" style="max-width:220px;margin:20px auto 0;display:block;">
            <i class="fas fa-plus"></i> Submit another
        </button>
    </div>

    <?php elseif (!empty($isExpired)): ?>
    <!-- ── Expired ─────────────────────────────────────────────────────────── -->
    <div class="exp-card">
        <div class="ico"><i class="fas fa-hourglass-end" style="color:var(--orange);"></i></div>
        <h2>Form Closed</h2>
        <p>This form expired on <?= htmlspecialchars(date('M j, Y \a\t H:i', strtotime($form['expires_at']))) ?> and is no longer accepting responses.</p>
    </div>

    <?php elseif (!($gateOpen ?? true)): ?>
    <!-- ── Gate: login required OR password ──────────────────────────────────── -->
    <?php if (($gateMode ?? 'password') === 'login'): ?>
    <div class="pgw">
        <div class="ico">🔐</div>
        <h2>Login Required</h2>
        <p>You need to be logged in to access this form.</p>
        <a href="/login?return=<?= urlencode($_SERVER['REQUEST_URI'] ?? '') ?>" class="btn-promo" style="margin-top:16px;display:inline-flex;">
            <i class="fas fa-sign-in-alt"></i> Sign In
        </a>
    </div>
    <?php else: ?>
    <!-- ── Password gate ────────────────────────────────────────────────────── -->
    <div class="pgw">
        <div class="ico">🔒</div>
        <h2>Password Required</h2>
        <p>This form is protected. Enter the password to continue.</p>
        <?php if (!empty($gateError)): ?>
        <div class="al al-err" style="max-width:340px;margin:0 auto 14px;"><i class="fas fa-exclamation-circle"></i> Incorrect password.</div>
        <?php endif; ?>
        <form method="POST" style="max-width:340px;margin:0 auto;">
            <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
            <input type="password" name="_gate_password" class="fi" style="margin-bottom:10px;" placeholder="Enter password…" required autofocus>
            <button type="submit" class="btn-sub"><i class="fas fa-unlock-alt"></i> Unlock Form</button>
        </form>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- ── Main form ─────────────────────────────────────────────────────────── -->

    <?php
    // Expiry countdown banner (show if expires within 72 hours)
    if (!empty($form['expires_at'])):
        $expiresTs  = strtotime($form['expires_at']);
        $secsLeft   = $expiresTs - time();
        if ($secsLeft > 0 && $secsLeft < 72 * 3600):
    ?>
    <div class="exp-banner">
        <i class="fas fa-clock"></i>
        <span>This form closes soon — </span>
        <span id="cdownText" style="font-weight:700;"></span>
    </div>
    <script>
    (function(){
        var end=<?= $expiresTs ?>;
        function upd(){
            var s=end-Math.floor(Date.now()/1000);
            if(s<=0){document.getElementById('cdownText').textContent='closed';return;}
            var h=Math.floor(s/3600),m=Math.floor((s%3600)/60),ss=s%60;
            document.getElementById('cdownText').textContent=
                (h?h+'h ':'')+m+'m '+ss+'s remaining';
            setTimeout(upd,1000);
        }
        upd();
    })();
    </script>
    <?php endif; endif; ?>

    <div class="fc">
        <!-- Draft restore banner -->
        <div id="draftBanner" style="display:none;" class="draft-banner">
            <span><i class="fas fa-bookmark"></i> Saved draft found. <a href="#" id="loadDraftLink" style="color:var(--purple);text-decoration:underline;">Restore?</a></span>
            <button onclick="discardDraft()" style="background:none;border:none;cursor:pointer;color:var(--muted);font-size:.82rem;">✕</button>
        </div>

        <form id="fxPublicForm" method="POST" action="/forms/<?= htmlspecialchars($form['slug']) ?>" enctype="multipart/form-data">
            <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">

            <?php foreach ($form['fields'] as $field): ?>
            <?php
            $type     = $field['type'] ?? 'text';
            $name     = $field['name'] ?? '';
            $label    = $field['label'] ?? $name;
            $ph       = $field['placeholder'] ?? '';
            $required = !empty($field['required']);
            $rawOpts  = $field['options'] ?? [];
            // Normalize options: stored as array (new builder) or newline-separated string (legacy)
            if (is_string($rawOpts)) {
                $options = array_filter(array_map('trim', explode("\n", $rawOpts)));
            } else {
                $options = $rawOpts;
            }
            $fid = 'f_' . htmlspecialchars($name);
            ?>

            <?php if ($type === 'divider'): ?>
            <hr class="div">

            <?php elseif ($type === 'heading'): ?>
            <?php $level = (int)($field['level'] ?? 2); if ($level < 1 || $level > 6) $level = 2; ?>
            <h<?= $level ?> class="fhead"><?= htmlspecialchars($field['content'] ?? $label) ?></h<?= $level ?>>

            <?php elseif ($type === 'paragraph'): ?>
            <p class="fpara"><?= nl2br(htmlspecialchars($field['content'] ?? '')) ?></p>

            <?php elseif ($type === 'hidden'): ?>
            <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($field['placeholder'] ?? '') ?>">

            <?php elseif ($type === 'textarea'): ?>
            <div class="fg">
                <label class="fl" for="<?= $fid ?>"><?= htmlspecialchars($label) ?><?= $required ? '<span class="req">*</span>' : '' ?></label>
                <textarea id="<?= $fid ?>" name="<?= htmlspecialchars($name) ?>"
                          class="fi" rows="<?= (int)($field['rows'] ?? 4) ?>"
                          placeholder="<?= htmlspecialchars($ph) ?>"
                          <?= $required ? 'required' : '' ?>></textarea>
            </div>

            <?php elseif ($type === 'select'): ?>
            <div class="fg">
                <label class="fl" for="<?= $fid ?>"><?= htmlspecialchars($label) ?><?= $required ? '<span class="req">*</span>' : '' ?></label>
                <select id="<?= $fid ?>" name="<?= htmlspecialchars($name) ?>" class="fi" <?= $required ? 'required' : '' ?>>
                    <option value="">— Select —</option>
                    <?php foreach ($options as $opt): ?>
                    <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php elseif ($type === 'radio'): ?>
            <div class="fg">
                <label class="fl"><?= htmlspecialchars($label) ?><?= $required ? '<span class="req">*</span>' : '' ?></label>
                <?php foreach ($options as $opt): ?>
                <div class="chk">
                    <input type="radio" id="r_<?= htmlspecialchars($name) ?>_<?= htmlspecialchars($opt) ?>"
                           name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($opt) ?>" <?= $required ? 'required' : '' ?>>
                    <label for="r_<?= htmlspecialchars($name) ?>_<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></label>
                </div>
                <?php endforeach; ?>
            </div>

            <?php elseif ($type === 'checkbox'): ?>
            <div class="fg">
                <label class="fl"><?= htmlspecialchars($label) ?><?= $required ? '<span class="req">*</span>' : '' ?></label>
                <?php foreach ($options as $opt): ?>
                <div class="chk">
                    <input type="checkbox" id="c_<?= htmlspecialchars($name) ?>_<?= htmlspecialchars($opt) ?>"
                           name="<?= htmlspecialchars($name) ?>[]" value="<?= htmlspecialchars($opt) ?>">
                    <label for="c_<?= htmlspecialchars($name) ?>_<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></label>
                </div>
                <?php endforeach; ?>
            </div>

            <?php elseif ($type === 'file'): ?>
            <div class="fg">
                <label class="fl" for="<?= $fid ?>"><?= htmlspecialchars($label) ?><?= $required ? '<span class="req">*</span>' : '' ?></label>
                <input type="file" id="<?= $fid ?>" name="<?= htmlspecialchars($name) ?>"
                       class="fi" <?= !empty($field['accept']) ? 'accept="'.htmlspecialchars($field['accept']).'"' : '' ?>
                       <?= $required ? 'required' : '' ?>>
            </div>

            <?php elseif ($type === 'rating'): ?>
            <div class="fg">
                <label class="fl"><?= htmlspecialchars($label) ?><?= $required ? '<span class="req">*</span>' : '' ?></label>
                <div class="rtg">
                    <?php $max = (int)($field['max'] ?? 5); if ($max < 1) $max = 5; ?>
                    <?php for ($star = $max; $star >= 1; $star--): ?>
                    <input type="radio" id="star<?= $star ?>_<?= htmlspecialchars($name) ?>"
                           name="<?= htmlspecialchars($name) ?>" value="<?= $star ?>" <?= $required ? 'required' : '' ?>>
                    <label for="star<?= $star ?>_<?= htmlspecialchars($name) ?>"><i class="fas fa-star"></i></label>
                    <?php endfor; ?>
                </div>
            </div>

            <?php else: ?>
            <!-- text, email, phone, number, url, date, time -->
            <div class="fg">
                <label class="fl" for="<?= $fid ?>"><?= htmlspecialchars($label) ?><?= $required ? '<span class="req">*</span>' : '' ?></label>
                <input type="<?= htmlspecialchars($type) ?>"
                       id="<?= $fid ?>"
                       name="<?= htmlspecialchars($name) ?>"
                       class="fi"
                       placeholder="<?= htmlspecialchars($ph) ?>"
                       <?= isset($field['min']) ? 'min="'.htmlspecialchars($field['min']).'"' : '' ?>
                       <?= isset($field['max']) ? 'max="'.htmlspecialchars($field['max']).'"' : '' ?>
                       <?= isset($field['maxlength']) ? 'maxlength="'.htmlspecialchars($field['maxlength']).'"' : ($type === 'text' ? 'maxlength="1000"' : ($type === 'email' ? 'maxlength="255"' : ($type === 'tel' || $type === 'phone' ? 'maxlength="20"' : ''))) ?>
                       <?= isset($field['pattern']) ? 'pattern="'.htmlspecialchars($field['pattern']).'"' : '' ?>
                       <?= $required ? 'required' : '' ?>>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>

            <button type="submit" class="btn-sub">
                <i class="fas fa-paper-plane"></i> Submit
            </button>
            <button type="button" class="btn-draft" onclick="saveDraft()">
                <i class="fas fa-bookmark"></i> Save as Draft
            </button>
        </form>
    </div>
    <?php endif; /* gate/expired/success */ ?>

    <!-- ── Confirmation modal (rendered always, hidden until needed) ─────────── -->
    <?php if (!empty($form) && !empty($form['settings']['confirm_submit'])): ?>
    <div id="fxConfirmModal" class="cfm-overlay" style="display:none;" onclick="if(event.target===this)document.getElementById('fxConfirmModal').style.display='none'">
        <div class="cfm-box">
            <div class="cfm-ico">📋</div>
            <h2>Ready to Submit?</h2>
            <p>Please review your answers before submitting. Once submitted, you may not be able to edit your response.</p>
            <div class="cfm-btns">
                <button class="cfm-cancel" onclick="document.getElementById('fxConfirmModal').style.display='none'">
                    <i class="fas fa-arrow-left"></i> Go Back
                </button>
                <button class="btn-sub" onclick="window._fxConfirmed=true;document.getElementById('fxConfirmModal').style.display='none';document.getElementById('fxPublicForm').requestSubmit()">
                    <i class="fas fa-paper-plane"></i> Submit
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <p class="pwr">Powered by <strong>FormX</strong></p>
</div>

<script>
(function() {
    var DRAFT_KEY = 'formx_draft_<?= (int)$form['id'] ?>';

    // ── Draft: check on load ──────────────────────────────────────────────
    function loadDraftData() {
        try { return JSON.parse(localStorage.getItem(DRAFT_KEY) || 'null'); } catch(e) { return null; }
    }

    function applyDraft(data) {
        if (!data) return;
        Object.keys(data).forEach(function(name) {
            var val = data[name];
            var inputs = document.querySelectorAll('#fxPublicForm [name="' + CSS.escape(name) + '"]');
            if (!inputs.length) return;
            if (inputs[0].type === 'checkbox') {
                var vals = Array.isArray(val) ? val : [val];
                inputs.forEach(function(inp) { inp.checked = vals.indexOf(inp.value) !== -1; });
            } else if (inputs[0].type === 'radio') {
                inputs.forEach(function(inp) { inp.checked = inp.value === val; });
            } else {
                inputs[0].value = val;
            }
        });
    }

    window.saveDraft = function() {
        var form = document.getElementById('fxPublicForm');
        if (!form) return;
        var data = {};
        var fd = new FormData(form);
        fd.forEach(function(v, k) {
            if (k === '_csrf_token' || k === '_gate_password') return;
            if (k.endsWith('[]')) {
                var key = k.slice(0, -2);
                if (!Array.isArray(data[key])) data[key] = [];
                data[key].push(v);
            } else {
                data[k] = v;
            }
        });
        localStorage.setItem(DRAFT_KEY, JSON.stringify(data));
        // Show brief confirmation
        var btn = document.querySelector('.btn-draft');
        if (btn) { var orig = btn.innerHTML; btn.innerHTML = '<i class="fas fa-check"></i> Draft Saved!'; btn.disabled = true; setTimeout(function(){ btn.innerHTML = orig; btn.disabled = false; }, 2000); }
    };

    window.discardDraft = function() {
        localStorage.removeItem(DRAFT_KEY);
        var banner = document.getElementById('draftBanner');
        if (banner) banner.style.display = 'none';
    };

    // Show restore banner if draft exists
    var draft = loadDraftData();
    if (draft && document.getElementById('draftBanner')) {
        document.getElementById('draftBanner').style.display = 'flex';
        document.getElementById('loadDraftLink').addEventListener('click', function(e) {
            e.preventDefault();
            applyDraft(draft);
            document.getElementById('draftBanner').style.display = 'none';
        });
    }

    // Clear draft on successful submit; also handle confirmation modal intercept
    var fxForm = document.getElementById('fxPublicForm');
    if (fxForm) {
        var needConfirm = <?= json_encode(!empty($form['settings']['confirm_submit'])) ?>;
        fxForm.addEventListener('submit', function(e) {
            if (needConfirm && !window._fxConfirmed) {
                e.preventDefault();
                var modal = document.getElementById('fxConfirmModal');
                if (modal) modal.style.display = 'flex';
                return false;
            }
            window._fxConfirmed = false;
            localStorage.removeItem(DRAFT_KEY);
        });
    }

})();
</script>
</body>
</html>
