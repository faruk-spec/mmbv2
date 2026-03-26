<?php
/**
 * Designed Template Renderer
 *
 * Renders a resume using a visual-designer layout definition.
 *
 * Available variables (injected by ResumeController):
 *   $resumeData    — array  : the user's resume content
 *   $themeSettings — array  : the chosen theme settings (including active color variant)
 *   $resume        — array  : the DB row for the resume
 *   $designerDesign — array : the parsed design JSON from the DB
 *   $isEmbed       — bool   : true when loaded inside an iframe (no chrome)
 *   $isPdf         — bool   : true when rendering for PDF export
 *   $title         — string : page <title>
 */

// --------------------------------------------------------------------------
// Resolve the active color variant
// --------------------------------------------------------------------------
$primaryColor   = $themeSettings['primaryColor']   ?? ($designerDesign['colorVariants'][0]['primary']   ?? '#007bff');
$secondaryColor = $themeSettings['secondaryColor'] ?? ($designerDesign['colorVariants'][0]['secondary'] ?? '#6f42c1');

// Walk through color variants and find the one that matches themeSettings
if (!empty($designerDesign['colorVariants']) && !empty($themeSettings['primaryColor'])) {
    foreach ($designerDesign['colorVariants'] as $cv) {
        if (($cv['primary'] ?? '') === $themeSettings['primaryColor']) {
            $primaryColor   = $cv['primary'];
            $secondaryColor = $cv['secondary'] ?? $primaryColor;
            break;
        }
    }
}

// --------------------------------------------------------------------------
// Canvas settings
// --------------------------------------------------------------------------
$canvas      = $designerDesign['canvas'] ?? [];
$canvasBg    = $canvas['background']  ?? '#ffffff';
$canvasFontF = $canvas['fontFamily']  ?? 'Inter, sans-serif';
$canvasW     = 794; // A4 width in px at 96dpi
$canvasH     = (int) ($canvas['height'] ?? 1123);
$blocks      = $designerDesign['blocks'] ?? [];

// --------------------------------------------------------------------------
// Helper: resolve a style value that may contain {{primary}} / {{secondary}}
// --------------------------------------------------------------------------
function rxdResolveColor(string $val, string $pri, string $sec): string
{
    $val = str_replace('{{primary}}',   $pri, $val);
    $val = str_replace('{{secondary}}', $sec, $val);
    return $val;
}

// --------------------------------------------------------------------------
// Helper: get resume field value as string/array
// --------------------------------------------------------------------------
function rxdGetField(string $field, array $data): mixed
{
    if (strpos($field, '.') !== false) {
        [$section, $key] = explode('.', $field, 2);
        return $data[$section][$key] ?? '';
    }
    return $data[$field] ?? '';
}

// --------------------------------------------------------------------------
// Helper: render one block's inner HTML
// --------------------------------------------------------------------------
function rxdRenderBlockHtml(array $block, array $data, string $pri, string $sec): string
{
    $type  = $block['type']  ?? 'field';
    $field = $block['field'] ?? '';

    // Static content blocks
    if ($type === 'divider') {
        $color = rxdResolveColor($block['style']['borderColor'] ?? $pri, $pri, $sec);
        return '<hr style="border:none;border-top:1px solid ' . htmlspecialchars($color, ENT_QUOTES) . ';margin:0;">';
    }
    if ($type === 'spacer') {
        return '';
    }
    if ($type === 'text') {
        return '<span>' . htmlspecialchars($block['content'] ?? '', ENT_QUOTES, 'UTF-8') . '</span>';
    }

    // Photo
    if ($field === 'contact.photo') {
        $src = htmlspecialchars($data['contact']['photo'] ?? '', ENT_QUOTES);
        if ($src === '') {
            return '<div style="width:100%;height:100%;background:#ddd;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2em;color:#999;">👤</div>';
        }
        return '<img src="' . $src . '" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;" alt="photo">';
    }

    // Simple string fields
    $simpleFields = ['contact.name','contact.email','contact.phone','contact.location',
                     'contact.website','contact.linkedin','contact.github','summary'];
    if (in_array($field, $simpleFields, true)) {
        $val = rxdGetField($field, $data);
        return '<span>' . nl2br(htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8')) . '</span>';
    }

    // List fields
    $listRenderers = [
        'experience'     => function(array $items): string {
            if (empty($items)) return '<em style="opacity:.4;">No experience added</em>';
            $out = '';
            foreach ($items as $item) {
                $out .= '<div style="margin-bottom:10px;">';
                $out .= '<div style="font-weight:700;">' . htmlspecialchars($item['title'] ?? '', ENT_QUOTES) . '</div>';
                $out .= '<div style="font-size:.88em;">' . htmlspecialchars($item['company'] ?? '', ENT_QUOTES);
                if (!empty($item['dates'])) $out .= ' · ' . htmlspecialchars($item['dates'], ENT_QUOTES);
                $out .= '</div>';
                if (!empty($item['description'])) {
                    $out .= '<div style="font-size:.85em;margin-top:3px;">' . nl2br(htmlspecialchars($item['description'], ENT_QUOTES)) . '</div>';
                }
                $out .= '</div>';
            }
            return $out;
        },
        'education'      => function(array $items): string {
            if (empty($items)) return '<em style="opacity:.4;">No education added</em>';
            $out = '';
            foreach ($items as $item) {
                $out .= '<div style="margin-bottom:8px;">';
                $out .= '<div style="font-weight:700;">' . htmlspecialchars($item['degree'] ?? '', ENT_QUOTES) . '</div>';
                $out .= '<div style="font-size:.88em;">' . htmlspecialchars($item['institution'] ?? '', ENT_QUOTES);
                if (!empty($item['dates'])) $out .= ' · ' . htmlspecialchars($item['dates'], ENT_QUOTES);
                $out .= '</div>';
                $out .= '</div>';
            }
            return $out;
        },
        'skills'         => function(array $items): string {
            if (empty($items)) return '<em style="opacity:.4;">No skills added</em>';
            $chips = '';
            foreach ($items as $item) {
                $name = htmlspecialchars(is_array($item) ? ($item['name'] ?? (string)$item) : (string)$item, ENT_QUOTES);
                $chips .= '<span style="display:inline-block;margin:2px 4px 2px 0;padding:2px 8px;background:rgba(0,0,0,.07);border-radius:10px;font-size:.82em;">' . $name . '</span>';
            }
            return $chips;
        },
        'certifications' => function(array $items): string {
            if (empty($items)) return '<em style="opacity:.4;">No certifications added</em>';
            $out = '';
            foreach ($items as $item) {
                $out .= '<div style="margin-bottom:5px;font-size:.88em;">🏆 ' . htmlspecialchars($item['name'] ?? (string)$item, ENT_QUOTES) . '</div>';
            }
            return $out;
        },
        'languages'      => function(array $items): string {
            if (empty($items)) return '<em style="opacity:.4;">No languages added</em>';
            $out = '';
            foreach ($items as $item) {
                $name = htmlspecialchars(is_array($item) ? ($item['language'] ?? (string)$item) : (string)$item, ENT_QUOTES);
                $level = is_array($item) ? htmlspecialchars($item['proficiency'] ?? '', ENT_QUOTES) : '';
                $out .= '<div style="font-size:.88em;">' . $name . ($level ? ' <span style="opacity:.6;">(' . $level . ')</span>' : '') . '</div>';
            }
            return $out;
        },
        'projects'       => function(array $items): string {
            if (empty($items)) return '<em style="opacity:.4;">No projects added</em>';
            $out = '';
            foreach ($items as $item) {
                $out .= '<div style="margin-bottom:8px;">';
                $out .= '<div style="font-weight:700;">' . htmlspecialchars($item['name'] ?? '', ENT_QUOTES) . '</div>';
                if (!empty($item['description'])) {
                    $out .= '<div style="font-size:.85em;">' . nl2br(htmlspecialchars($item['description'], ENT_QUOTES)) . '</div>';
                }
                $out .= '</div>';
            }
            return $out;
        },
        'awards'         => function(array $items): string {
            if (empty($items)) return '<em style="opacity:.4;">No awards added</em>';
            $out = '';
            foreach ($items as $item) {
                $out .= '<div style="margin-bottom:4px;font-size:.88em;">🥇 ' . htmlspecialchars($item['title'] ?? (string)$item, ENT_QUOTES) . '</div>';
            }
            return $out;
        },
        'volunteer'      => function(array $items): string {
            if (empty($items)) return '<em style="opacity:.4;">No volunteer work added</em>';
            $out = '';
            foreach ($items as $item) {
                $out .= '<div style="margin-bottom:6px;">';
                $out .= '<div style="font-weight:600;">' . htmlspecialchars($item['role'] ?? (string)$item, ENT_QUOTES) . '</div>';
                if (!empty($item['organization'])) $out .= '<div style="font-size:.85em;">' . htmlspecialchars($item['organization'], ENT_QUOTES) . '</div>';
                $out .= '</div>';
            }
            return $out;
        },
        'hobbies'        => function(array $items): string {
            if (empty($items)) return '<em style="opacity:.4;">No hobbies added</em>';
            $parts = [];
            foreach ($items as $item) {
                $parts[] = htmlspecialchars(is_array($item) ? ($item['name'] ?? (string)$item) : (string)$item, ENT_QUOTES);
            }
            return implode(', ', $parts);
        },
        'references'     => function(array $items): string {
            if (empty($items)) return '<em style="opacity:.4;">Available upon request</em>';
            $out = '';
            foreach ($items as $item) {
                $out .= '<div style="margin-bottom:6px;">';
                $out .= '<div style="font-weight:600;">' . htmlspecialchars($item['name'] ?? (string)$item, ENT_QUOTES) . '</div>';
                if (!empty($item['relation'])) $out .= '<div style="font-size:.85em;">' . htmlspecialchars($item['relation'], ENT_QUOTES) . '</div>';
                $out .= '</div>';
            }
            return $out;
        },
        'publications'   => function(array $items): string {
            if (empty($items)) return '<em style="opacity:.4;">No publications added</em>';
            $out = '';
            foreach ($items as $item) {
                $out .= '<div style="margin-bottom:6px;">';
                $out .= '<div style="font-weight:600;">' . htmlspecialchars($item['title'] ?? (string)$item, ENT_QUOTES) . '</div>';
                if (!empty($item['journal'])) $out .= '<div style="font-size:.85em;">' . htmlspecialchars($item['journal'], ENT_QUOTES) . '</div>';
                $out .= '</div>';
            }
            return $out;
        },
    ];

    if (isset($listRenderers[$field])) {
        $items = $data[$field] ?? [];
        return $listRenderers[$field](is_array($items) ? $items : []);
    }

    // Section heading
    if ($type === 'section_heading') {
        return '<span>' . htmlspecialchars($block['content'] ?? ucfirst($field), ENT_QUOTES, 'UTF-8') . '</span>';
    }

    return '';
}

// --------------------------------------------------------------------------
// Build the CSS for one block
// --------------------------------------------------------------------------
function rxdBlockCss(array $block, string $pri, string $sec): string
{
    $s     = $block['style'] ?? [];
    $x     = (int) ($block['x'] ?? 0);
    $y     = (int) ($block['y'] ?? 0);
    $w     = (int) ($block['w'] ?? 200);
    $h     = (int) ($block['h'] ?? 40);

    $rules = [
        'position'   => 'absolute',
        'left'       => $x . 'px',
        'top'        => $y . 'px',
        'width'      => $w . 'px',
        'min-height' => $h . 'px',
        'box-sizing' => 'border-box',
        'overflow'   => 'hidden',
    ];

    if (!empty($s['fontSize']))       $rules['font-size']      = ((int)$s['fontSize']) . 'px';
    if (!empty($s['fontWeight']))     $rules['font-weight']    = htmlspecialchars($s['fontWeight'], ENT_QUOTES);
    if (!empty($s['fontStyle']))      $rules['font-style']     = htmlspecialchars($s['fontStyle'], ENT_QUOTES);
    if (!empty($s['textDecoration'])) $rules['text-decoration']= htmlspecialchars($s['textDecoration'], ENT_QUOTES);
    if (!empty($s['textAlign']))      $rules['text-align']     = htmlspecialchars($s['textAlign'], ENT_QUOTES);
    if (!empty($s['lineHeight']))     $rules['line-height']    = htmlspecialchars($s['lineHeight'], ENT_QUOTES);
    if (!empty($s['letterSpacing']))  $rules['letter-spacing'] = htmlspecialchars($s['letterSpacing'], ENT_QUOTES);
    if (!empty($s['padding']))        $rules['padding']        = htmlspecialchars($s['padding'], ENT_QUOTES);
    if (!empty($s['borderRadius']))   $rules['border-radius']  = (int)$s['borderRadius'] . 'px';

    if (!empty($s['color'])) {
        $rules['color'] = rxdResolveColor(htmlspecialchars($s['color'], ENT_QUOTES), $pri, $sec);
    }
    if (!empty($s['backgroundColor']) && $s['backgroundColor'] !== 'transparent' && $s['backgroundColor'] !== '') {
        $rules['background-color'] = rxdResolveColor(htmlspecialchars($s['backgroundColor'], ENT_QUOTES), $pri, $sec);
    }
    if (!empty($s['backgroundImage'])) {
        $rules['background-image']    = 'url("' . htmlspecialchars($s['backgroundImage'], ENT_QUOTES) . '")';
        $rules['background-size']     = 'cover';
        $rules['background-position'] = 'center';
    }
    if (!empty($s['border'])) {
        $rules['border'] = htmlspecialchars($s['border'], ENT_QUOTES);
    }
    if (!empty($s['boxShadow'])) {
        $rules['box-shadow'] = htmlspecialchars($s['boxShadow'], ENT_QUOTES);
    }

    $css = '';
    foreach ($rules as $prop => $val) {
        $css .= $prop . ':' . $val . ';';
    }
    return $css;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($title ?? 'Resume', ENT_QUOTES) ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php if (!$isPdf): ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=<?= urlencode($canvasFontF) ?>:wght@300;400;600;700&display=swap" rel="stylesheet">
<?php endif; ?>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { background: #f0f0f0; }
.rxd-page {
    position: relative;
    width: <?= $canvasW ?>px;
    min-height: <?= $canvasH ?>px;
    background: <?= htmlspecialchars($canvasBg, ENT_QUOTES) ?>;
    font-family: <?= htmlspecialchars($canvasFontF, ENT_QUOTES) ?>, Inter, sans-serif;
    margin: 0 auto;
    overflow: hidden;
}
<?php if (!$isEmbed && !$isPdf): ?>
.rxd-page { box-shadow: 0 4px 32px rgba(0,0,0,.18); margin: 32px auto; }
<?php endif; ?>
@media print {
    html, body { background: white; }
    .rxd-page { box-shadow: none; margin: 0; }
}
</style>
<?php if (!$isEmbed && !$isPdf && !empty($designerDesign['canvas']['backgroundCss'])): ?>
<style><?= strip_tags($designerDesign['canvas']['backgroundCss']) ?></style>
<?php endif; ?>
</head>
<body>
<div class="rxd-page">
<?php foreach ($blocks as $block):
    if (empty($block['id'])) continue;
    $blockCss  = rxdBlockCss($block, $primaryColor, $secondaryColor);
    $innerHtml = rxdRenderBlockHtml($block, $resumeData, $primaryColor, $secondaryColor);
?>
<div id="rxd-<?= htmlspecialchars($block['id'], ENT_QUOTES) ?>" style="<?= $blockCss ?>">
<?= $innerHtml ?>
</div>
<?php endforeach; ?>
</div>
<?php if (!$isEmbed && !$isPdf): ?>
<script>
// Scale the A4 page to fit the viewport width on small screens
(function() {
    var page = document.querySelector('.rxd-page');
    if (!page) return;
    function scale() {
        var vw = window.innerWidth;
        var pw = <?= $canvasW ?>;
        var sc = Math.min(1, (vw - 32) / pw);
        page.style.transformOrigin = 'top center';
        page.style.transform = sc < 1 ? 'scale(' + sc + ')' : '';
        page.style.marginTop = sc < 1 ? Math.ceil(page.offsetHeight * (sc - 1) / 2) + 'px' : '';
    }
    scale();
    window.addEventListener('resize', scale);
}());
</script>
<?php endif; ?>
</body>
</html>
