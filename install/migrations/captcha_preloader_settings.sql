-- Migration: Add captcha and preloader settings
-- Created automatically by copilot/add-preloader-options

INSERT IGNORE INTO `settings` (`key`, `value`, `type`) VALUES
    ('captcha_enabled',         '0',       'boolean'),
    ('captcha_on_login',        '0',       'boolean'),
    ('captcha_on_register',     '0',       'boolean'),
    ('preloader_enabled',       '0',       'boolean'),
    ('preloader_type',          'text',    'string'),
    ('preloader_text',          'Loading…','string'),
    ('preloader_text_color',    '#7C3AED', 'string'),
    ('preloader_bg_color',      '#06060a', 'string'),
    ('preloader_animation',     'reveal',  'string'),
    ('preloader_speed',         '800',     'integer'),
    ('preloader_image_path',    '',        'string'),
    ('skeleton_enabled',        '0',       'boolean'),
    ('action_loader_enabled',   '0',       'boolean'),
    ('action_loader_blur',      '8',       'integer'),
    ('action_loader_image',     '',        'string');
