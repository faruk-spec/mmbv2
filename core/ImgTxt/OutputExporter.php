<?php

/**
 * OutputExporter Class
 * 
 * Export OCR results to various formats including Excel, Word,
 * searchable PDF, JSON, and XML.
 * 
 * Features:
 * - Excel export for tables (XLSX format)
 * - Word document export (DOCX format)
 * - Searchable PDF generation with text layer
 * - JSON/XML structured output
 * - Support for multiple image formats (TIFF, BMP, WebP)
 */
class OutputExporter
{
    /**
     * Export OCR result to Excel format (for tables)
     * 
     * @param array $ocrResult OCR result with tables
     * @param string $outputPath Output file path
     * @return bool Success status
     */
    public static function exportToExcel($ocrResult, $outputPath)
    {
        if (empty($ocrResult['tables'])) {
            throw new Exception('No tables found in OCR result');
        }
        
        // Simple CSV export (can be opened in Excel)
        // For full XLSX support, would need phpspreadsheet library
        
        $csvPath = str_replace('.xlsx', '.csv', $outputPath);
        $fp = fopen($csvPath, 'w');
        
        if (!$fp) {
            throw new Exception('Failed to create CSV file');
        }
        
        foreach ($ocrResult['tables'] as $tableIndex => $table) {
            // Add table header
            fputcsv($fp, ['Table ' . ($tableIndex + 1)]);
            fputcsv($fp, []);
            
            // Add table data
            foreach ($table['data'] as $row) {
                $csvRow = [];
                foreach ($row as $cell) {
                    $csvRow[] = $cell['text'] ?? '';
                }
                fputcsv($fp, $csvRow);
            }
            
            // Add spacing between tables
            fputcsv($fp, []);
            fputcsv($fp, []);
        }
        
        fclose($fp);
        
        return true;
    }
    
    /**
     * Export OCR result to Word document format
     * 
     * @param array $ocrResult OCR result
     * @param string $outputPath Output file path
     * @return bool Success status
     */
    public static function exportToWord($ocrResult, $outputPath)
    {
        // Create RTF format (can be opened in Word)
        // For full DOCX support, would need phpword library
        
        $rtf = "{\\rtf1\\ansi\\deff0\n";
        $rtf .= "{\\fonttbl{\\f0 Arial;}}\n";
        $rtf .= "{\\colortbl;\\red0\\green0\\blue0;}\n";
        $rtf .= "\\fs24\n";
        
        // Add title
        $rtf .= "\\b\\fs32 OCR Result\\b0\\fs24\\par\n";
        $rtf .= "\\par\n";
        
        // Add metadata
        if (!empty($ocrResult['confidence'])) {
            $rtf .= "\\b Confidence: \\b0 " . $ocrResult['confidence'] . "%\\par\n";
        }
        
        if (!empty($ocrResult['language'])) {
            $rtf .= "\\b Language: \\b0 " . $ocrResult['language'] . "\\par\n";
        }
        
        $rtf .= "\\par\n";
        
        // Add text
        $text = $ocrResult['text'] ?? $ocrResult['full_text'] ?? '';
        $text = str_replace("\n", "\\par\n", $text);
        $text = str_replace("\\", "\\\\", $text);
        $rtf .= $text;
        
        // Add tables if present
        if (!empty($ocrResult['tables'])) {
            $rtf .= "\\par\\par\n";
            $rtf .= "\\b\\fs28 Detected Tables\\b0\\fs24\\par\\par\n";
            
            foreach ($ocrResult['tables'] as $tableIndex => $table) {
                $rtf .= "\\b Table " . ($tableIndex + 1) . "\\b0\\par\n";
                $rtf .= "Rows: " . $table['rows'] . ", Columns: " . $table['estimated_columns'] . "\\par\\par\n";
            }
        }
        
        $rtf .= "}\n";
        
        file_put_contents($outputPath, $rtf);
        
        return true;
    }
    
    /**
     * Generate searchable PDF with text layer
     * 
     * @param string $imagePath Original image
     * @param array $ocrResult OCR result
     * @param string $outputPath Output PDF path
     * @return bool Success status
     */
    public static function generateSearchablePDF($imagePath, $ocrResult, $outputPath)
    {
        // Use Tesseract to create searchable PDF directly
        $tesseractPath = '/usr/bin/tesseract';
        $outputBase = str_replace('.pdf', '', $outputPath);
        
        $command = sprintf(
            '%s %s %s -l %s pdf 2>&1',
            escapeshellarg($tesseractPath),
            escapeshellarg($imagePath),
            escapeshellarg($outputBase),
            escapeshellarg($ocrResult['language'] ?? 'eng')
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0 || !file_exists($outputPath)) {
            throw new Exception('Failed to generate searchable PDF');
        }
        
        return true;
    }
    
    /**
     * Export OCR result to JSON format
     * 
     * @param array $ocrResult OCR result
     * @param string $outputPath Output file path
     * @return bool Success status
     */
    public static function exportToJSON($ocrResult, $outputPath)
    {
        $json = json_encode($ocrResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if ($json === false) {
            throw new Exception('Failed to encode JSON');
        }
        
        file_put_contents($outputPath, $json);
        
        return true;
    }
    
    /**
     * Export OCR result to XML format
     * 
     * @param array $ocrResult OCR result
     * @param string $outputPath Output file path
     * @return bool Success status
     */
    public static function exportToXML($ocrResult, $outputPath)
    {
        $xml = new SimpleXMLElement('<ocr_result/>');
        
        // Add metadata
        $xml->addChild('confidence', $ocrResult['confidence'] ?? 0);
        $xml->addChild('language', $ocrResult['language'] ?? 'unknown');
        
        // Add text
        $textNode = $xml->addChild('text');
        $textNode[0] = $ocrResult['text'] ?? $ocrResult['full_text'] ?? '';
        
        // Add pages if present
        if (!empty($ocrResult['pages'])) {
            $pagesNode = $xml->addChild('pages');
            foreach ($ocrResult['pages'] as $page) {
                $pageNode = $pagesNode->addChild('page');
                $pageNode->addAttribute('number', $page['page']);
                $pageNode->addAttribute('confidence', $page['confidence']);
                $pageNode[0] = $page['text'];
            }
        }
        
        // Add tables if present
        if (!empty($ocrResult['tables'])) {
            $tablesNode = $xml->addChild('tables');
            foreach ($ocrResult['tables'] as $tableIndex => $table) {
                $tableNode = $tablesNode->addChild('table');
                $tableNode->addAttribute('index', $tableIndex);
                $tableNode->addAttribute('rows', $table['rows']);
                $tableNode->addAttribute('columns', $table['estimated_columns']);
            }
        }
        
        $xml->asXML($outputPath);
        
        return true;
    }
    
    /**
     * Convert image to supported format
     * 
     * @param string $inputPath Input image path
     * @param string $outputFormat Output format (png, jpg, tiff, bmp, webp)
     * @param string $outputPath Output file path
     * @return bool Success status
     */
    public static function convertImageFormat($inputPath, $outputFormat, $outputPath)
    {
        if (!file_exists($inputPath)) {
            throw new Exception('Input image not found');
        }
        
        $imageMagickPath = '/usr/bin/convert';
        
        $command = sprintf(
            '%s %s %s 2>&1',
            escapeshellarg($imageMagickPath),
            escapeshellarg($inputPath),
            escapeshellarg($outputPath)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0 || !file_exists($outputPath)) {
            throw new Exception('Failed to convert image format');
        }
        
        return true;
    }
    
    /**
     * Get available export formats
     * 
     * @return array Available formats
     */
    public static function getAvailableFormats()
    {
        return [
            'excel' => [
                'name' => 'Excel (CSV)',
                'extension' => 'csv',
                'description' => 'Comma-separated values for spreadsheets',
                'supports_tables' => true
            ],
            'word' => [
                'name' => 'Word (RTF)',
                'extension' => 'rtf',
                'description' => 'Rich text format for word processors',
                'supports_tables' => false
            ],
            'pdf' => [
                'name' => 'Searchable PDF',
                'extension' => 'pdf',
                'description' => 'PDF with searchable text layer',
                'supports_tables' => false
            ],
            'json' => [
                'name' => 'JSON',
                'extension' => 'json',
                'description' => 'JavaScript Object Notation',
                'supports_tables' => true
            ],
            'xml' => [
                'name' => 'XML',
                'extension' => 'xml',
                'description' => 'Extensible Markup Language',
                'supports_tables' => true
            ],
            'txt' => [
                'name' => 'Plain Text',
                'extension' => 'txt',
                'description' => 'Plain text file',
                'supports_tables' => false
            ]
        ];
    }
    
    /**
     * Export OCR result to specified format
     * 
     * @param array $ocrResult OCR result
     * @param string $format Export format
     * @param string $outputPath Output file path
     * @param array $options Export options
     * @return bool Success status
     */
    public static function export($ocrResult, $format, $outputPath, $options = [])
    {
        switch ($format) {
            case 'excel':
            case 'csv':
                return self::exportToExcel($ocrResult, $outputPath);
                
            case 'word':
            case 'rtf':
                return self::exportToWord($ocrResult, $outputPath);
                
            case 'pdf':
                if (empty($options['image_path'])) {
                    throw new Exception('Image path required for PDF export');
                }
                return self::generateSearchablePDF($options['image_path'], $ocrResult, $outputPath);
                
            case 'json':
                return self::exportToJSON($ocrResult, $outputPath);
                
            case 'xml':
                return self::exportToXML($ocrResult, $outputPath);
                
            case 'txt':
                $text = $ocrResult['text'] ?? $ocrResult['full_text'] ?? '';
                file_put_contents($outputPath, $text);
                return true;
                
            default:
                throw new Exception('Unsupported export format: ' . $format);
        }
    }
}
