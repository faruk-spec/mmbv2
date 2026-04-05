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
        .alert { padding:14px 16px; border-radius:10px; margin-bottom:20px; font-size:.9rem; }
        .alert-success { background:rgba(0,255,136,.1); border:1px solid #00ff88; color:#00ff88; }
        .alert-error   { background:rgba(255,107,107,.1); border:1px solid #ff6b6b; color:#ff6b6b; }
        .rating-group { display:flex; gap:8px; }
        .rating-group input[type=radio] { display:none; }
        .rating-group label { font-size:1.5rem; cursor:pointer; color:#8892a6; transition:color .1s; }
        .rating-group label:hover,
        .rating-group label:hover ~ label { color:#ffaa00; }
        .rating-group input:checked ~ label { color:#8892a6; }
        .rating-group input:checked + label,
        .rating-group label:has(~ input:checked) { color:#ffaa00; }
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
    <div class="form-card">
        <form method="POST" action="/forms/<?= htmlspecialchars($form['slug']) ?>" enctype="multipart/form-data">
            <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">

            <?php foreach ($form['fields'] as $field): ?>
            <?php
            $type     = $field['type'] ?? 'text';
            $name     = $field['name'] ?? '';
            $label    = $field['label'] ?? $name;
            $ph       = $field['placeholder'] ?? '';
            $required = !empty($field['required']);
            $options  = $field['options'] ?? [];
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
        </form>
    </div>
    <?php endif; ?>

    <p class="powered">Powered by <strong>FormX</strong></p>
</div>
</body>
</html>
