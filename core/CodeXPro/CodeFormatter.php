<?php

/**
 * CodeFormatter - Code formatting and beautification
 * 
 * Formats HTML, CSS, JavaScript code
 */
class CodeFormatter
{
    /**
     * Format HTML code
     */
    public static function formatHTML($html)
    {
        // Basic HTML formatting
        $html = trim($html);
        
        // Remove extra whitespace
        $html = preg_replace('/\s+/', ' ', $html);
        
        // Add newlines after tags
        $html = preg_replace('/>/', ">\n", $html);
        $html = preg_replace('/</', "\n<", $html);
        
        // Remove empty lines
        $html = preg_replace('/\n\s*\n/', "\n", $html);
        
        // Indent the code
        $html = self::indentHTML($html);
        
        return trim($html);
    }
    
    /**
     * Indent HTML code
     */
    private static function indentHTML($html)
    {
        $lines = explode("\n", $html);
        $formatted = [];
        $indent = 0;
        $indentStr = '    '; // 4 spaces
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Decrease indent for closing tags
            if (preg_match('/^<\//', $line)) {
                $indent = max(0, $indent - 1);
            }
            
            // Add indentation
            $formatted[] = str_repeat($indentStr, $indent) . $line;
            
            // Increase indent for opening tags (but not self-closing)
            if (preg_match('/^<[^\/!]/', $line) && !preg_match('/\/>$/', $line) && !preg_match('/<br>|<hr>|<img|<input|<meta|<link/', $line)) {
                $indent++;
            }
            
            // Decrease indent after closing tag on same line
            if (preg_match('/<\/[^>]+>$/', $line) && preg_match('/^<[^\/]/', $line)) {
                $indent = max(0, $indent - 1);
            }
        }
        
        return implode("\n", $formatted);
    }
    
    /**
     * Format CSS code
     */
    public static function formatCSS($css)
    {
        // Remove extra whitespace
        $css = trim($css);
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Add newlines
        $css = preg_replace('/\{/', " {\n    ", $css);
        $css = preg_replace('/\}/', "\n}\n\n", $css);
        $css = preg_replace('/;/', ";\n    ", $css);
        
        // Clean up
        $css = preg_replace('/\{\s+/', "{\n    ", $css);
        $css = preg_replace('/\s+\}/', "\n}", $css);
        $css = preg_replace('/\n\s*\n\s*\n/', "\n\n", $css);
        
        return trim($css);
    }
    
    /**
     * Format JavaScript code
     */
    public static function formatJavaScript($js)
    {
        // Basic JavaScript formatting
        $js = trim($js);
        
        // Remove extra whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        
        // Add newlines
        $js = preg_replace('/\{/', " {\n", $js);
        $js = preg_replace('/\}/', "\n}\n", $js);
        $js = preg_replace('/;/', ";\n", $js);
        
        // Indent the code
        $js = self::indentJS($js);
        
        return trim($js);
    }
    
    /**
     * Indent JavaScript code
     */
    private static function indentJS($js)
    {
        $lines = explode("\n", $js);
        $formatted = [];
        $indent = 0;
        $indentStr = '    '; // 4 spaces
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Decrease indent for closing braces
            if (preg_match('/^\}/', $line)) {
                $indent = max(0, $indent - 1);
            }
            
            // Add indentation
            $formatted[] = str_repeat($indentStr, $indent) . $line;
            
            // Increase indent for opening braces
            if (preg_match('/\{$/', $line)) {
                $indent++;
            }
        }
        
        return implode("\n", $formatted);
    }
    
    /**
     * Minify HTML
     */
    public static function minifyHTML($html)
    {
        // Remove comments
        $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);
        
        // Remove whitespace
        $html = preg_replace('/\s+/', ' ', $html);
        $html = preg_replace('/>\s+</', '><', $html);
        
        return trim($html);
    }
    
    /**
     * Minify CSS
     */
    public static function minifyCSS($css)
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove whitespace
        $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css);
        
        return trim($css);
    }
    
    /**
     * Minify JavaScript
     */
    public static function minifyJavaScript($js)
    {
        // Remove comments (simple approach)
        $js = preg_replace('!/\*.*?\*/!s', '', $js);
        $js = preg_replace('!//.*!', '', $js);
        
        // Remove whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        $js = preg_replace('/\s*([{}:;,()=<>])\s*/', '$1', $js);
        
        return trim($js);
    }
    
    /**
     * Validate HTML
     */
    public static function validateHTML($html)
    {
        $errors = [];
        
        // Check for unclosed tags
        preg_match_all('/<([a-z]+)[^>]*>/i', $html, $openTags);
        preg_match_all('/<\/([a-z]+)>/i', $html, $closeTags);
        
        $openTags = array_map('strtolower', $openTags[1]);
        $closeTags = array_map('strtolower', $closeTags[1]);
        
        // Filter self-closing tags
        $selfClosing = ['img', 'br', 'hr', 'input', 'meta', 'link'];
        $openTags = array_diff($openTags, $selfClosing);
        
        foreach ($openTags as $tag) {
            if (!in_array($tag, $closeTags)) {
                $errors[] = "Unclosed tag: <{$tag}>";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validate CSS
     */
    public static function validateCSS($css)
    {
        $errors = [];
        
        // Check for unmatched braces
        $open = substr_count($css, '{');
        $close = substr_count($css, '}');
        
        if ($open !== $close) {
            $errors[] = "Unmatched braces: {$open} opening, {$close} closing";
        }
        
        // Check for missing semicolons (basic check)
        preg_match_all('/\{([^}]+)\}/', $css, $blocks);
        foreach ($blocks[1] as $block) {
            $declarations = explode(';', trim($block));
            foreach ($declarations as $decl) {
                $decl = trim($decl);
                if (!empty($decl) && strpos($decl, ':') === false) {
                    $errors[] = "Invalid declaration: {$decl}";
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validate JavaScript (basic syntax check)
     */
    public static function validateJavaScript($js)
    {
        $errors = [];
        
        // Check for unmatched braces, brackets, parentheses
        $checks = [
            ['{', '}', 'braces'],
            ['[', ']', 'brackets'],
            ['(', ')', 'parentheses']
        ];
        
        foreach ($checks as $check) {
            $open = substr_count($js, $check[0]);
            $close = substr_count($js, $check[1]);
            
            if ($open !== $close) {
                $errors[] = "Unmatched {$check[2]}: {$open} opening, {$close} closing";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
