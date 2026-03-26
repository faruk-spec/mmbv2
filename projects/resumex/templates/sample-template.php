<?php
/**
 * ResumeX Custom Template — Sample File
 *
 * HOW TO CREATE YOUR OWN TEMPLATE
 * ================================
 * 1. Copy this file and rename it (e.g. my-template.php).
 * 2. Change the `key` value to a unique slug (lowercase, hyphens only, e.g. "my-template").
 *    ⚠ The key MUST be unique — duplicate keys are rejected on upload.
 * 3. Adjust the color/font/layout values to match your design.
 * 4. Upload the file via  Admin → ResumeX → Templates → Upload Template.
 *
 * REQUIRED FIELDS
 * ---------------
 * key              string   Unique slug (a-z, 0-9, hyphens). Max 100 chars.
 * name             string   Display name shown to users. Max 255 chars.
 * category         string   One of: professional | academic | dark | light | creative | custom
 * primaryColor     string   Hex color, e.g. "#2563eb"
 * secondaryColor   string   Hex color, e.g. "#1d4ed8"
 * backgroundColor  string   Hex color for the page background.
 * surfaceColor     string   Hex color for cards / panels.
 * textColor        string   Hex color for main body text.
 * textMuted        string   Hex color for secondary / muted text.
 * borderColor      string   Hex color or rgba() for borders.
 * fontFamily       string   One of: Inter | Merriweather | Fira Code | Georgia | Arial | Roboto
 * fontSize         string   Base font size in px (e.g. "13" or "14").
 * fontWeight       string   CSS font-weight (e.g. "400" or "500").
 * headerStyle      string   One of: gradient | underline | minimal | solid | banner
 * buttonStyle      string   One of: pill | square | rounded
 * cardStyle        string   One of: bordered | flat | shadow | glass
 * spacing          string   One of: compact | normal | spacious
 * layoutMode       string   One of: two-column | single
 * iconStyle        string   One of: filled | outline
 * accentHighlights boolean  true = accent color used for highlights.
 * animations       boolean  true = CSS transitions enabled.
 * layoutStyle      string   One of: sidebar-dark | minimal | academic | timeline | banner
 * colorVariants    array    Up to 4 color swatches shown in the editor.
 *                           Each item: { label: string, primary: "#rrggbb", secondary: "#rrggbb" }
 *
 * UPLOADING AN IMAGE IN YOUR TEMPLATE
 * ------------------------------------
 * Template definitions only contain colors / fonts / layout configuration;
 * they do NOT embed images directly.
 *
 * To add a profile photo to a resume the user uploads an image from inside the
 * Resume Editor (Profile Photo section).  The upload API endpoint is:
 *
 *   POST /projects/resumex/upload-image
 *   Content-Type: multipart/form-data
 *   Fields:
 *     _token   — CSRF token
 *     photo    — the image file  (JPEG / PNG / GIF / WebP, max 5 MB)
 *
 * The endpoint returns JSON:
 *   { "success": true, "url": "/storage/uploads/resumex/images/<filename>" }
 *
 * The returned URL is saved in resume_data.contact.photo and rendered by the
 * editor / preview / print views automatically.
 *
 * VALIDATION RULES ENFORCED ON UPLOAD
 * ------------------------------------
 * • File extension must be .php
 * • File must return an array (not execute arbitrary side-effects)
 * • `key` must match the pattern /^[a-z0-9\-]+$/ and be ≤ 100 chars
 * • `name` must be a non-empty string ≤ 255 chars
 * • All required color fields must be non-empty strings
 * • `colorVariants` must be an array of 1–4 items, each with label/primary/secondary
 * • Duplicate keys are rejected
 * • Only admin users can upload or delete templates
 *
 * @package MMB\Projects\ResumeX\Templates
 */

return [
    // ── Identity ──────────────────────────────────────────────────────────────
    'key'      => 'corporate-blue',           // Change this — must be unique
    'name'     => 'Corporate Blue',           // Display name
    'category' => 'professional',             // professional | academic | dark | light | creative | custom

    // ── Colors ────────────────────────────────────────────────────────────────
    'primaryColor'    => '#1e40af',           // Accent / heading color
    'secondaryColor'  => '#1d4ed8',           // Sub-accent color
    'backgroundColor' => '#f0f4ff',           // Page background
    'surfaceColor'    => '#ffffff',           // Card / panel background
    'textColor'       => '#0f172a',           // Main text
    'textMuted'       => '#475569',           // Secondary text
    'borderColor'     => '#bfdbfe',           // Borders

    // ── Typography ────────────────────────────────────────────────────────────
    'fontFamily'  => 'Inter',                 // Inter | Merriweather | Fira Code | Georgia | Arial | Roboto
    'fontSize'    => '14',                    // Base size in px
    'fontWeight'  => '400',                   // 400 = regular, 500 = medium

    // ── Layout & Style ────────────────────────────────────────────────────────
    'headerStyle'      => 'gradient',         // gradient | underline | minimal | solid | banner
    'buttonStyle'      => 'pill',             // pill | square | rounded
    'cardStyle'        => 'bordered',         // bordered | flat | shadow | glass
    'spacing'          => 'compact',          // compact | normal | spacious
    'layoutMode'       => 'two-column',       // two-column | single
    'iconStyle'        => 'filled',           // filled | outline
    'accentHighlights' => true,               // true | false
    'animations'       => true,               // true | false
    'layoutStyle'      => 'sidebar-dark',     // sidebar-dark | minimal | academic | timeline | banner

    // ── Color Swatches (1–4 items) ────────────────────────────────────────────
    'colorVariants' => [
        ['label' => 'Navy',    'primary' => '#1e40af', 'secondary' => '#1d4ed8'],
        ['label' => 'Teal',    'primary' => '#0d9488', 'secondary' => '#0f766e'],
        ['label' => 'Violet',  'primary' => '#7c3aed', 'secondary' => '#6d28d9'],
        ['label' => 'Slate',   'primary' => '#334155', 'secondary' => '#1e293b'],
    ],
];
