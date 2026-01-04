<?php

/**
 * MIME Message Parser
 * 
 * Parses email messages and extracts headers, body, and attachments
 */

class MIMEParser
{
    /**
     * Parse an email message
     */
    public function parse($inbox, $msgNo, $structure, $header)
    {
        $parsed = [
            'message_id' => $header->message_id ?? '',
            'from' => $this->parseAddress($header->from ?? []),
            'to' => $this->parseAddressList($header->to ?? []),
            'cc' => $this->parseAddressList($header->cc ?? []),
            'bcc' => $this->parseAddressList($header->bcc ?? []),
            'reply_to' => $this->parseAddressList($header->reply_to ?? []),
            'subject' => $this->decodeSubject($header->subject ?? ''),
            'date' => $header->date ?? '',
            'size' => $header->Size ?? 0,
            'body_html' => '',
            'body_text' => '',
            'attachments' => []
        ];
        
        // Parse body and attachments
        $this->parseStructure($inbox, $msgNo, $structure, $parsed);
        
        // If no plain text body, convert HTML to plain text
        if (empty($parsed['body_text']) && !empty($parsed['body_html'])) {
            $parsed['body_text'] = $this->htmlToPlainText($parsed['body_html']);
        }
        
        // If no HTML body, convert plain text to HTML
        if (empty($parsed['body_html']) && !empty($parsed['body_text'])) {
            $parsed['body_html'] = nl2br(htmlspecialchars($parsed['body_text']));
        }
        
        return $parsed;
    }
    
    /**
     * Parse email structure recursively
     */
    private function parseStructure($inbox, $msgNo, $structure, &$parsed, $partNum = '')
    {
        if ($structure->type == 0 || $structure->type == 1) {
            // Text or multipart
            if (!empty($structure->parts)) {
                // Multipart message
                foreach ($structure->parts as $index => $part) {
                    $subPartNum = $partNum ? "$partNum." . ($index + 1) : ($index + 1);
                    $this->parseStructure($inbox, $msgNo, $part, $parsed, $subPartNum);
                }
            } else {
                // Single part
                $this->parseSinglePart($inbox, $msgNo, $structure, $parsed, $partNum);
            }
        } elseif ($structure->type == 2) {
            // Message
            if (!empty($structure->parts)) {
                foreach ($structure->parts as $index => $part) {
                    $subPartNum = $partNum ? "$partNum." . ($index + 1) : ($index + 1);
                    $this->parseStructure($inbox, $msgNo, $part, $parsed, $subPartNum);
                }
            }
        } else {
            // Attachment or other types
            $this->parseAttachment($inbox, $msgNo, $structure, $parsed, $partNum);
        }
    }
    
    /**
     * Parse a single part (text/html/plain)
     */
    private function parseSinglePart($inbox, $msgNo, $structure, &$parsed, $partNum)
    {
        $data = $partNum ? imap_fetchbody($inbox, $msgNo, $partNum) : imap_body($inbox, $msgNo);
        
        // Decode based on encoding
        $data = $this->decodeContent($data, $structure->encoding);
        
        // Determine subtype
        $subtype = strtolower($structure->subtype ?? '');
        
        // Check if it's an attachment
        $isAttachment = false;
        if (!empty($structure->dparameters)) {
            foreach ($structure->dparameters as $param) {
                if (strtolower($param->attribute) == 'filename') {
                    $isAttachment = true;
                    break;
                }
            }
        }
        if (!$isAttachment && !empty($structure->parameters)) {
            foreach ($structure->parameters as $param) {
                if (strtolower($param->attribute) == 'name') {
                    $isAttachment = true;
                    break;
                }
            }
        }
        
        if ($isAttachment) {
            $this->parseAttachment($inbox, $msgNo, $structure, $parsed, $partNum);
            return;
        }
        
        // Store body based on type
        if ($subtype == 'html') {
            $parsed['body_html'] = $this->sanitizeHtml($data);
        } elseif ($subtype == 'plain') {
            $parsed['body_text'] = $data;
        }
    }
    
    /**
     * Parse attachment
     */
    private function parseAttachment($inbox, $msgNo, $structure, &$parsed, $partNum)
    {
        $filename = '';
        
        // Get filename from parameters
        if (!empty($structure->dparameters)) {
            foreach ($structure->dparameters as $param) {
                if (strtolower($param->attribute) == 'filename') {
                    $filename = $this->decodeFilename($param->value);
                    break;
                }
            }
        }
        
        if (empty($filename) && !empty($structure->parameters)) {
            foreach ($structure->parameters as $param) {
                if (strtolower($param->attribute) == 'name') {
                    $filename = $this->decodeFilename($param->value);
                    break;
                }
            }
        }
        
        if (empty($filename)) {
            $filename = 'attachment_' . $partNum;
        }
        
        // Get content
        $content = $partNum ? imap_fetchbody($inbox, $msgNo, $partNum) : imap_body($inbox, $msgNo);
        $content = $this->decodeContent($content, $structure->encoding);
        
        // Determine MIME type
        $mimeType = $this->getMimeType($structure);
        
        $parsed['attachments'][] = [
            'filename' => $filename,
            'content' => $content,
            'mime_type' => $mimeType,
            'size' => strlen($content)
        ];
    }
    
    /**
     * Decode content based on encoding
     */
    private function decodeContent($data, $encoding)
    {
        switch ($encoding) {
            case 0: // 7bit
            case 1: // 8bit
                return $data;
            case 2: // Binary
                return $data;
            case 3: // Base64
                return base64_decode($data);
            case 4: // Quoted-printable
                return quoted_printable_decode($data);
            default:
                return $data;
        }
    }
    
    /**
     * Parse email address
     */
    private function parseAddress($addresses)
    {
        if (empty($addresses) || !is_array($addresses)) {
            return ['email' => '', 'name' => ''];
        }
        
        $address = $addresses[0];
        $email = isset($address->mailbox) && isset($address->host) 
            ? $address->mailbox . '@' . $address->host 
            : '';
        $name = $address->personal ?? '';
        $name = $this->decodeSubject($name);
        
        return ['email' => $email, 'name' => $name];
    }
    
    /**
     * Parse list of email addresses
     */
    private function parseAddressList($addresses)
    {
        if (empty($addresses) || !is_array($addresses)) {
            return '';
        }
        
        $list = [];
        foreach ($addresses as $address) {
            if (isset($address->mailbox) && isset($address->host)) {
                $email = $address->mailbox . '@' . $address->host;
                $name = isset($address->personal) ? $this->decodeSubject($address->personal) : '';
                $list[] = $name ? "$name <$email>" : $email;
            }
        }
        
        return implode(', ', $list);
    }
    
    /**
     * Decode subject line
     */
    private function decodeSubject($subject)
    {
        if (empty($subject)) {
            return '';
        }
        
        $elements = imap_mime_header_decode($subject);
        $decoded = '';
        
        foreach ($elements as $element) {
            $charset = ($element->charset == 'default') ? 'UTF-8' : $element->charset;
            $decoded .= $this->convertEncoding($element->text, $charset, 'UTF-8');
        }
        
        return $decoded;
    }
    
    /**
     * Decode filename
     */
    private function decodeFilename($filename)
    {
        return $this->decodeSubject($filename);
    }
    
    /**
     * Get MIME type from structure
     */
    private function getMimeType($structure)
    {
        $primaryTypes = ['text', 'multipart', 'message', 'application', 'audio', 'image', 'video', 'other'];
        $primary = $primaryTypes[$structure->type] ?? 'application';
        $secondary = strtolower($structure->subtype ?? 'octet-stream');
        
        return "$primary/$secondary";
    }
    
    /**
     * Convert character encoding
     */
    private function convertEncoding($string, $fromEncoding, $toEncoding)
    {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, $toEncoding, $fromEncoding);
        } elseif (function_exists('iconv')) {
            return iconv($fromEncoding, $toEncoding . '//IGNORE', $string);
        }
        
        return $string;
    }
    
    /**
     * Sanitize HTML content
     */
    private function sanitizeHtml($html)
    {
        // Remove potentially dangerous tags and attributes
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $html);
        $html = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        
        return $html;
    }
    
    /**
     * Convert HTML to plain text
     */
    private function htmlToPlainText($html)
    {
        // Remove scripts, styles
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        
        // Convert line breaks
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $html = preg_replace('/<\/p>/i', "\n\n", $html);
        
        // Strip remaining tags
        $text = strip_tags($html);
        
        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        
        // Clean up whitespace
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = trim($text);
        
        return $text;
    }
}
