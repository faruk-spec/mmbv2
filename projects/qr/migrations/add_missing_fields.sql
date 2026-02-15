-- Add missing fields to qr_codes table
ALTER TABLE qr_codes 
ADD COLUMN IF NOT EXISTS error_correction VARCHAR(1) DEFAULT 'H' COMMENT 'L, M, Q, H' AFTER background_color,
ADD COLUMN IF NOT EXISTS gradient_enabled TINYINT(1) DEFAULT 0 AFTER error_correction,
ADD COLUMN IF NOT EXISTS gradient_color VARCHAR(7) DEFAULT '#9945ff' AFTER gradient_enabled,
ADD COLUMN IF NOT EXISTS corner_style VARCHAR(50) NULL AFTER gradient_color,
ADD COLUMN IF NOT EXISTS dot_style VARCHAR(50) NULL AFTER corner_style,
ADD COLUMN IF NOT EXISTS marker_border_style VARCHAR(50) NULL AFTER dot_style,
ADD COLUMN IF NOT EXISTS marker_center_style VARCHAR(50) NULL AFTER marker_border_style,
ADD COLUMN IF NOT EXISTS custom_marker_color TINYINT(1) DEFAULT 0 AFTER marker_center_style,
ADD COLUMN IF NOT EXISTS marker_color VARCHAR(7) NULL AFTER custom_marker_color,
ADD COLUMN IF NOT EXISTS logo_color VARCHAR(7) DEFAULT '#9945ff' AFTER marker_color,
ADD COLUMN IF NOT EXISTS logo_size DECIMAL(3,2) DEFAULT 0.30 AFTER logo_color,
ADD COLUMN IF NOT EXISTS logo_remove_bg TINYINT(1) DEFAULT 0 AFTER logo_size,
ADD COLUMN IF NOT EXISTS transparent_bg TINYINT(1) DEFAULT 0 AFTER logo_remove_bg,
ADD COLUMN IF NOT EXISTS frame_label VARCHAR(255) NULL AFTER frame_style,
ADD COLUMN IF NOT EXISTS frame_font VARCHAR(50) NULL AFTER frame_label,
ADD COLUMN IF NOT EXISTS frame_color VARCHAR(7) NULL AFTER frame_font;
