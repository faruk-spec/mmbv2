<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Form') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Poppins',sans-serif;
            background:#06060a;
            color:#e8eefc;
            min-height:100vh;
            display:flex;
            flex-direction:column;
            align-items:center;
            padding:40px 16px;
        }
        body::before {
            content:'';
            position:fixed;
            inset:0;
            background:
                radial-gradient(ellipse at 20% 0%, rgba(0,240,255,.08) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 100%, rgba(255,46,196,.07) 0%, transparent 50%);
            pointer-events:none;
            z-index:-1;
        }
        .form-wrapper {
            width:100%;
            max-width:680px;
        }
        .form-header {
            margin-bottom:28px;
        }
        .form-header h1 {
            font-size:1.8rem;
            font-weight:700;
            background:linear-gradient(135deg,#00f0ff,#ff2ec4);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
            margin-bottom:8px;
        }
        .form-header p {
            color:#8892a6;
            font-size:.95rem;
            line-height:1.6;
        }
        .form-card {
            background:#0f0f18;
            border:1px solid rgba(255,255,255,.08);
            border-radius:16px;
            padding:32px;
        }
        .form-group {
            margin-bottom:20px;
        }
        .form-label {
            display:block;
            font-size:.875rem;
            font-weight:600;
            color:#8892a6;
            margin-bottom:6px;
        }
        .form-label .req {
            color:#ff2ec4;
            margin-left:4px;
        }
        .form-control {
            width:100%;
            padding:10px 14px;
            background:#0c0c12;
            border:1px solid rgba(255,255,255,.1);
            border-radius:8px;
            color:#e8eefc;
            font-size:.9rem;
            font-family:'Poppins',sans-serif;
            transition:border-color .2s;
            outline:none;
        }
        .form-control:focus { border-color:#00f0ff; box-shadow:0 0 0 3px rgba(0,240,255,.1); }
        textarea.form-control { resize:vertical; }
        select.form-control option { background:#0f0f18; }
        .form-check { display:flex; align-items:center; gap:10px; margin-bottom:8px; }
        .form-check input[type=checkbox],
        .form-check input[type=radio] { width:18px; height:18px; cursor:pointer; accent-color:#00f0ff; }
        .form-check label { font-size:.9rem; cursor:pointer; }
        .divider { border:none; border-top:1px solid rgba(255,255,255,.1); margin:24px 0; }
        .form-heading { font-size:1.15rem; font-weight:700; color:#e8eefc; margin-bottom:4px; margin-top:8px; }
        .form-paragraph { color:#8892a6; font-size:.9rem; line-height:1.6; }
        .btn-submit {
            width:100%;
            padding:13px;
            background:linear-gradient(135deg,#00f0ff,#9945ff);
            border:none;
            border-radius:10px;
            color:#fff;
            font-size:1rem;
            font-weight:700;
            cursor:pointer;
            font-family:'Poppins',sans-serif;
            transition:opacity .2s,transform .1s;
            margin-top:8px;
        }
        .btn-submit:hover { opacity:.9; transform:translateY(-1px); }
        .btn-draft {
            width:100%;
            padding:11px;
            background:transparent;
            border:1px solid rgba(153,69,255,.5);
            border-radius:10px;
            color:#9945ff;
            font-size:.875rem;
            font-weight:600;
            cursor:pointer;
            font-family:'Poppins',sans-serif;
            transition:all .2s;
            margin-top:10px;
        }
        .btn-draft:hover { background:rgba(153,69,255,.12); border-color:#9945ff; }
        .draft-restored { display:flex;align-items:center;gap:10px;padding:12px 16px;background:rgba(153,69,255,.1);border:1px solid rgba(153,69,255,.3);border-radius:10px;font-size:.85rem;color:#9945ff;margin-bottom:16px;justify-content:space-between; }
        /* Password gate */
        .pw-gate { background:#0f0f18; border:1px solid rgba(255,255,255,.08); border-radius:16px; padding:32px; text-align:center; }
        .pw-gate h2 { font-size:1.2rem; font-weight:700; margin-bottom:8px; }
        .pw-gate p  { color:#8892a6; font-size:.9rem; margin-bottom:20px; }
        .pw-gate .form-control { max-width:340px; margin:0 auto 14px; display:block; }
        .pw-gate .btn-submit   { max-width:340px; margin:0 auto; }
        .alert { padding:14px 16px; border-radius:10px; margin-bottom:20px; font-size:.9rem; }
        .alert-success { background:rgba(0,255,136,.1); border:1px solid #00ff88; color:#00ff88; }
        .alert-error   { background:rgba(255,107,107,.1); border:1px solid #ff6b6b; color:#ff6b6b; }
        .rating-group { display:flex; gap:8px; flex-direction: row-reverse; justify-content: flex-end; }
        .rating-group input[type=radio] { display:none; }
        .rating-group label { font-size:1.5rem; cursor:pointer; color:#8892a6; transition:color .15s; }
        .rating-group label:hover,
        .rating-group label:hover ~ label { color:#ffaa00; }
        .rating-group input:checked ~ label,
        .rating-group input:checked + label { color:#ffaa00; }
        .powered {
            margin-top:28px;
            text-align:center;
            font-size:.78rem;
            color:rgba(255,255,255,.25);
        }
        @media (max-width:500px) {
            .form-card { padding:20px 16px; }
            .form-header h1 { font-size:1.4rem; }
        }
    </style>
</head>
<body>
<div class="form-wrapper">
    <div class="form-header">
        <h1><?= htmlspecialchars($form['title']) ?></h1>
        <?php if (!empty($form['description'])): ?>
        <p><?= nl2br(htmlspecialchars($form['description'])) ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php
    use Core\Helpers;
    if (Helpers::hasFlash('error')):
    ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(Helpers::getFlash('error')) ?></div>
    <?php endif; ?>

    <?php if (empty($success)): ?>

    <?php if (!($gateOpen ?? true)): ?>
    <div class="pw-gate">
        <div style="font-size:2.5rem;margin-bottom:12px;">🔒</div>
        <h2>Password Required</h2>
        <p>This form is protected. Enter the password to continue.</p>
        <?php if (!empty($gateError)): ?>
        <div class="alert alert-error" style="max-width:340px;margin:0 auto 14px;"><i class="fas fa-exclamation-circle"></i> Incorrect password.</div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
            <input type="password" name="_gate_password" class="form-control" placeholder="Enter password…" required autofocus>
            <button type="submit" class="btn-submit"><i class="fas fa-unlock-alt"></i> Unlock Form</button>
        </form>
    </div>
    <?php else: ?>
    <div class="form-card">
        <!-- Draft restore banner -->
        <div id="draftBanner" style="display:none;" class="draft-restored">
            <span><i class="fas fa-bookmark"></i> You have a saved draft. <a href="#" id="loadDraftLink" style="color:#9945ff;text-decoration:underline;">Restore it?</a></span>
            <button onclick="discardDraft()" style="background:none;border:none;cursor:pointer;color:#8892a6;font-size:.85rem;">✕ Discard</button>
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
            ?>

            <?php if ($type === 'divider'): ?>
            <hr class="divider">

            <?php elseif ($type === 'heading'): ?>
            <?php $level = (int)($field['level'] ?? 2); if ($level < 1 || $level > 6) $level = 2; ?>
            <h<?= $level ?> class="form-heading"><?= htmlspecialchars($field['content'] ?? $label) ?></h<?= $level ?>>

            <?php elseif ($type === 'paragraph'): ?>
            <p class="form-paragraph"><?= nl2br(htmlspecialchars($field['content'] ?? '')) ?></p>

            <?php elseif ($type === 'hidden'): ?>
            <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($field['placeholder'] ?? '') ?>">

            <?php elseif ($type === 'textarea'): ?>
            <div class="form-group">
                <label class="form-label" for="f_<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($label) ?><?= $required ? '<span class="req">*</span>' : '' ?></label>
                <textarea id="f_<?= htmlspecialchars($name) ?>" name="<?= htmlspecialchars($name) ?>"
                          class="form-control" rows="<?= (int)($field['rows'] ?? 4) ?>"
                          placeholder="<?= htmlspecialchars($ph) ?>"
                          <?= $required ? 'required' : '' ?>></textarea>
            </div>

            <?php elseif ($type === 'select'): ?>
            <div class="form-group">
                <label class="form-label" for="f_<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($label) ?><?= $required ? '<span class="req">*</span>' : '' ?></label>
                <select id="f_<?= htmlspecialchars($name) ?>" name="<?= htmlspecialchars($name) ?>" class="form-control" <?= $required ? 'required' : '' ?>>
                    <option value="">— Select —</option>
                    <?php foreach ($options as $opt): ?>
                    <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php elseif ($type === 'radio'): ?>
            <div class="form-group">
                <label class="form-label"><?= htmlspecialchars($label) ?><?= $required ? '<span class="req">*</span>' : '' ?></label>
                <?php foreach ($options as $opt): ?>
                <div class="form-check">
                    <input type="radio" id="r_<?= htmlspecialchars($name) ?>_<?= htmlspecialchars($opt) ?>"
                           name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($opt) ?>" <?= $required ? 'required' : '' ?>>
                    <label for="r_<?= htmlspecialchars($name) ?>_<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></label>
                </div>
                <?php endforeach; ?>
            </div>

            <?php elseif ($type === 'checkbox'): ?>
            <div class="form-group">
                <label class="form-label"><?= htmlspecialchars($label) ?><?= $required ? '<span class="req">*</span>' : '' ?></label>
                <?php foreach ($options as $opt): ?>
                <div class="form-check">
                    <input type="checkbox" id="c_<?= htmlspecialchars($name) ?>_<?= htmlspecialchars($opt) ?>"
                           name="<?= htmlspecialchars($name) ?>[]" value="<?= htmlspecialchars($opt) ?>">
                    <label for="c_<?= htmlspecialchars($name) ?>_<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></label>
                </div>
                <?php endforeach; ?>
            </div>

            <?php elseif ($type === 'file'): ?>
            <div class="form-group">
                <label class="form-label" for="f_<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($label) ?><?= $required ? '<span class="req">*</span>' : '' ?></label>
                <input type="file" id="f_<?= htmlspecialchars($name) ?>" name="<?= htmlspecialchars($name) ?>"
                       class="form-control" <?= !empty($field['accept']) ? 'accept="'.htmlspecialchars($field['accept']).'"' : '' ?>
                       <?= $required ? 'required' : '' ?>>
            </div>

            <?php elseif ($type === 'rating'): ?>
            <div class="form-group">
                <label class="form-label"><?= htmlspecialchars($label) ?><?= $required ? '<span class="req">*</span>' : '' ?></label>
                <div class="rating-group">
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
            <div class="form-group">
                <label class="form-label" for="f_<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($label) ?><?= $required ? '<span class="req">*</span>' : '' ?></label>
                <input type="<?= htmlspecialchars($type) ?>"
                       id="f_<?= htmlspecialchars($name) ?>"
                       name="<?= htmlspecialchars($name) ?>"
                       class="form-control"
                       placeholder="<?= htmlspecialchars($ph) ?>"
                       <?= isset($field['min']) ? 'min="'.htmlspecialchars($field['min']).'"' : '' ?>
                       <?= isset($field['max']) ? 'max="'.htmlspecialchars($field['max']).'"' : '' ?>
                       <?= $required ? 'required' : '' ?>>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>

            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Submit
            </button>
            <button type="button" class="btn-draft" onclick="saveDraft()">
                <i class="fas fa-bookmark"></i> Save as Draft
            </button>
        </form>
    </div>
    <?php endif; /* gateOpen */ ?>
    <?php endif; /* success */ ?>

    <p class="powered">Powered by <strong>FormX</strong></p>
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

    // Clear draft on successful submit
    document.getElementById('fxPublicForm') && document.getElementById('fxPublicForm').addEventListener('submit', function() {
        localStorage.removeItem(DRAFT_KEY);
    });

})();
</script>
</body>
</html>
