<?php
/**
 * CardX – Bulk ID Card Generator Controller
 * Handles CSV upload, bulk card creation, and sample CSV downloads.
 *
 * @package MMB\Projects\IDCard\Controllers
 */

namespace Projects\IDCard\Controllers;

use Core\Auth;
use Core\Security;
use Core\Logger;
use Core\ActivityLogger;
use Projects\IDCard\Models\IDCardModel;

class BulkController
{
    private IDCardModel $model;
    /** @var array */
    private array $config;

    public function __construct()
    {
        $this->model  = new IDCardModel();
        $this->config = require PROJECT_PATH . '/config.php';
    }

    // ------------------------------------------------------------------ //
    //  Bulk generator form                                                 //
    // ------------------------------------------------------------------ //

    public function index(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            header('Location: /login');
            exit;
        }

        $jobs = $this->model->getBulkJobsByUser($userId, 15);
        $adminCfg = $this->model->getSetting('admin_config', []);

        $this->render('bulk', [
            'title'     => 'Bulk ID Card Generator',
            'user'      => Auth::user(),
            'templates' => $this->config['templates'],
            'jobs'      => $jobs,
            'csrfToken' => Security::generateCsrfToken(),
            'adminCfg'  => $adminCfg,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Process uploaded CSV → create cards in bulk                        //
    // ------------------------------------------------------------------ //

    public function upload(): void
    {
        header('Content-Type: application/json');

        $userId = Auth::id();
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
            exit;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
            exit;
        }

        $templateKey = trim($_POST['template_key'] ?? 'corporate');
        $templates   = $this->config['templates'];
        if (!isset($templates[$templateKey])) {
            $templateKey = 'corporate';
        }
        $tplConfig = $templates[$templateKey];

        // Check admin setting for bulk_enabled
        $adminCfg = $this->model->getSetting('admin_config', []);
        if (isset($adminCfg['bulk_enabled']) && !$adminCfg['bulk_enabled']) {
            echo json_encode(['success' => false, 'message' => 'Bulk generation is disabled by the administrator.']);
            exit;
        }
        $maxRows = (int) ($adminCfg['max_bulk_rows'] ?? 200);

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error.']);
            exit;
        }

        $file = $_FILES['csv_file'];

        // Validate MIME via finfo (not client-supplied header)
        $finfo   = new \finfo(FILEINFO_MIME_TYPE);
        $mime    = $finfo->file($file['tmp_name']);
        $allowed = ['text/plain', 'text/csv', 'application/csv', 'application/vnd.ms-excel'];
        if (!in_array($mime, $allowed, true)) {
            echo json_encode(['success' => false, 'message' => 'Only CSV files are accepted.']);
            exit;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            echo json_encode(['success' => false, 'message' => 'Unable to read uploaded file.']);
            exit;
        }

        // First row = headers
        $csvHeaders = fgetcsv($handle);
        if (!$csvHeaders) {
            fclose($handle);
            echo json_encode(['success' => false, 'message' => 'CSV file is empty or unreadable.']);
            exit;
        }
        $csvHeaders = array_map('trim', $csvHeaders);

        // Collect data rows (skip empty rows)
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $row = array_map('trim', $row);
            if (array_filter($row)) {
                $rows[] = $row;
            }
        }
        fclose($handle);

        if (count($rows) === 0) {
            echo json_encode(['success' => false, 'message' => 'No data rows found in the CSV.']);
            exit;
        }
        if (count($rows) > $maxRows) {
            echo json_encode(['success' => false, 'message' => "CSV exceeds the maximum of {$maxRows} rows."]);
            exit;
        }

        // Create a bulk job record
        $jobId = $this->model->createBulkJob($userId, $templateKey, count($rows));

        // Build field list for this template (excluding photo)
        $tplFields = array_filter($tplConfig['fields'], fn($f) => $f !== 'photo');

        // Build design from submitted fields
        $allowedStyles = ['classic','gradient_pro','neon','executive','stripe','metro',
                          'glass','zigzag','ribbon',
                          'v_sharp','v_curve','v_hex','v_circle','v_split',
                          'v_ribbon','v_arch','v_diamond','v_corner','v_dual',
                          'v_stripe','v_badge'];
        $isPortrait  = ($tplConfig['orientation'] ?? 'landscape') === 'portrait';
        $defStyle    = $isPortrait ? 'v_sharp' : 'classic';
        $rawStyle    = trim($_POST['design_style'] ?? '');
        $designStyle = in_array($rawStyle, $allowedStyles, true) ? $rawStyle : $defStyle;

        $design = [
            'primary_color'  => $this->sanitizeColor($_POST['primary_color']  ?? $tplConfig['color']),
            'accent_color'   => $this->sanitizeColor($_POST['accent_color']   ?? $tplConfig['accent']),
            'bg_color'       => $this->sanitizeColor($_POST['bg_color']       ?? $tplConfig['bg']),
            'text_color'     => $this->sanitizeColor($_POST['text_color']     ?? $tplConfig['text']),
            'font_family'    => $this->sanitizeFont($_POST['font_family']     ?? 'Poppins'),
            'design_style'   => $designStyle,
            'show_qr'        => false,
            'qr_size'        => 54,
            'card_width'     => 'standard',
            'profile_shape'  => in_array(
                trim($_POST['profile_shape'] ?? 'circle'),
                ['circle','oval','square'], true
            ) ? trim($_POST['profile_shape'] ?? 'circle') : 'circle',
        ];

        $completed = 0;
        $failed    = 0;
        $cardIds   = [];

        foreach ($rows as $row) {
            // Map CSV columns to template fields by header name
            $cardData = [];
            foreach ($tplFields as $field) {
                $label = $this->config['field_labels'][$field] ?? $field;
                // Try exact match, then case-insensitive
                $idx = $this->findCsvColumn($csvHeaders, [$field, $label]);
                if ($idx !== null && isset($row[$idx])) {
                    $cardData[$field] = $this->sanitize($row[$idx]);
                } else {
                    $cardData[$field] = '';
                }
            }

            if (empty($cardData['name'])) {
                $failed++;
                continue;
            }

            try {
                $id = $this->model->create([
                    'user_id'      => $userId,
                    'template_key' => $templateKey,
                    'card_data'    => $cardData,
                    'design'       => $design,
                    'status'       => 'generated',
                    'bulk_job_id'  => $jobId,
                ]);
                $completed++;
                $cardIds[] = $id;
            } catch (\Throwable $e) {
                Logger::error('BulkController create card: ' . $e->getMessage());
                $failed++;
            }
        }

        $this->model->updateBulkJob($jobId, $completed, $failed, 'done');
        try {
            ActivityLogger::log($userId, 'idcard_bulk_generated', [
                'job_id'      => $jobId,
                'template'    => $templateKey,
                'completed'   => $completed,
                'failed'      => $failed,
            ]);
        } catch (\Throwable $_) {}

        echo json_encode([
            'success'   => true,
            'job_id'    => $jobId,
            'completed' => $completed,
            'failed'    => $failed,
            'message'   => "Successfully generated {$completed} ID card(s)." . ($failed ? " {$failed} row(s) skipped." : ''),
        ]);
        exit;
    }

    // ------------------------------------------------------------------ //
    //  Download category-wise sample CSV                                  //
    // ------------------------------------------------------------------ //

    public function sampleCsv(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            header('Location: /login');
            exit;
        }

        $templateKey = trim($_GET['template'] ?? 'corporate');
        $templates   = $this->config['templates'];
        if (!isset($templates[$templateKey])) {
            $templateKey = 'corporate';
        }

        $tplConfig  = $templates[$templateKey];
        $tplName    = $tplConfig['name'];
        $tplFields  = array_filter($tplConfig['fields'], fn($f) => $f !== 'photo');
        $fieldLabels = $this->config['field_labels'];

        // Build header row using readable labels
        $headers = array_map(fn($f) => $fieldLabels[$f] ?? ucwords(str_replace('_', ' ', $f)), $tplFields);

        // Build 3 sample data rows per template
        $sampleRows = $this->getSampleRows($templateKey, $tplFields);

        $filename = 'sample-' . $templateKey . '-idcard.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Excel compatibility
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, $headers);
        foreach ($sampleRows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    // ------------------------------------------------------------------ //
    //  Sample data per template                                            //
    // ------------------------------------------------------------------ //

    private function getSampleRows(string $templateKey, array $fields): array
    {
        // Category-specific sample datasets (3 rows each)
        $samples = [
            'corporate' => [
                ['Alice Johnson', 'Acme Corp', '123 Business Ave, New York, NY 10001', 'Senior Manager', 'Operations', 'EMP-0042', '1988-04-15', '45 Park Lane, Brooklyn, NY 11201', '+1-212-555-0101', 'alice@acme.com'],
                ['Bob Smith', 'Acme Corp', '123 Business Ave, New York, NY 10001', 'Software Engineer', 'Engineering', 'EMP-0089', '1993-07-22', '8 Oak Street, Queens, NY 11354', '+1-212-555-0102', 'bob@acme.com'],
                ['Carol Lee', 'Acme Corp', '123 Business Ave, New York, NY 10001', 'HR Specialist', 'Human Resources', 'EMP-0117', '1990-11-05', '32 Maple Road, Manhattan, NY 10025', '+1-212-555-0103', 'carol@acme.com'],
            ],
            'student' => [
                ['Priya Sharma', 'City University', '1 University Road, Chicago, IL 60601', 'Computer Science', 'CS-2021-001', '3rd Year', '2001-03-10', '22 Elm Street, Chicago, IL 60614', '+1-312-555-0201', 'priya@cityuni.edu'],
                ['James Brown', 'City University', '1 University Road, Chicago, IL 60601', 'Business Administration', 'BA-2022-045', '2nd Year', '2002-09-18', '5 Pine Avenue, Chicago, IL 60618', '+1-312-555-0202', 'james@cityuni.edu'],
                ['Mei Zhang', 'City University', '1 University Road, Chicago, IL 60601', 'Data Science', 'DS-2023-012', '1st Year', '2003-06-25', '17 Oak Boulevard, Chicago, IL 60622', '+1-312-555-0203', 'mei@cityuni.edu'],
            ],
            'event' => [
                ['Tom Richards', 'TechStart Inc', 'CTO', 'DevConf 2026', 'BADGE-001', '+1-415-555-0301', 'tom@techstart.io'],
                ['Sara Kim', 'Design Studio', 'Lead Designer', 'DevConf 2026', 'BADGE-002', '+1-415-555-0302', 'sara@designstudio.com'],
                ['Luis Morales', 'Open Source Labs', 'Developer', 'DevConf 2026', 'BADGE-003', '+1-415-555-0303', 'luis@oslabs.dev'],
            ],
            'visitor' => [
                ['David Chen', 'Vendor Co', 'Jane Doe (Procurement)', 'Supplier Meeting', '2026-04-10', '2026-04-10', 'VISIT-A01', '+1-312-555-0401'],
                ['Emma Wilson', 'Consulting Ltd', 'Mark Lee (IT)', 'System Audit', '2026-04-11', '2026-04-11', 'VISIT-B02', '+1-312-555-0402'],
                ['Carlos Rivera', 'Freelance', 'Admin Office', 'Document Submission', '2026-04-12', '2026-04-12', 'VISIT-C03', '+1-312-555-0403'],
            ],
            'medical' => [
                ['Dr. Sarah Patel', 'City Hospital', '10 Medical Drive, Houston, TX 77001', 'Cardiologist', 'Cardiology', 'ML-55012', '1979-01-20', '4 Healer Lane, Houston, TX 77005', '+1-713-555-0501', 'sarah.patel@cityhosp.org', 'O+'],
                ['Nurse John Mills', 'City Hospital', '10 Medical Drive, Houston, TX 77001', 'Registered Nurse', 'ICU', 'NL-30201', '1987-08-14', '9 Oak Street, Houston, TX 77009', '+1-713-555-0502', 'john.mills@cityhosp.org', 'A-'],
                ['Dr. Liu Wei', 'City Hospital', '10 Medical Drive, Houston, TX 77001', 'Radiologist', 'Radiology', 'ML-71120', '1982-05-30', '21 Pines Blvd, Houston, TX 77019', '+1-713-555-0503', 'liu.wei@cityhosp.org', 'B+'],
            ],
            'tech' => [
                ['Arjun Nair', 'NovaTech', 'Full-Stack Developer', 'Engineering', 'NT-2201', '+1-650-555-0601', 'arjun@novatech.io'],
                ['Lily Wang', 'NovaTech', 'Product Manager', 'Product', 'NT-2202', '+1-650-555-0602', 'lily@novatech.io'],
                ['Marcus Bell', 'NovaTech', 'DevOps Engineer', 'Infrastructure', 'NT-2203', '+1-650-555-0603', 'marcus@novatech.io'],
            ],
            'bank' => [
                ['Rachel Green', 'First National Bank', '99 Finance Street, Boston, MA 02101', 'Branch Manager', 'Downtown Branch', 'BNK-0310', '1983-03-12', '55 Beacon Hill, Boston, MA 02108', '+1-617-555-0701', 'rachel.g@fnbank.com'],
                ['Chris Evans', 'First National Bank', '99 Finance Street, Boston, MA 02101', 'Loan Officer', 'South Branch', 'BNK-0421', '1990-09-05', '12 South Street, Boston, MA 02110', '+1-617-555-0702', 'chris.e@fnbank.com'],
                ['Nina Patel', 'First National Bank', '99 Finance Street, Boston, MA 02101', 'Teller', 'North Branch', 'BNK-0555', '1995-11-22', '7 North Avenue, Boston, MA 02113', '+1-617-555-0703', 'nina.p@fnbank.com'],
            ],
            'media' => [
                ['Jake Turner', 'Global Press', 'Senior Reporter', 'PRESS-001', 'American', '+1-202-555-0801', 'jake@globalpress.com'],
                ['Aisha Diallo', 'News Today', 'Photojournalist', 'PRESS-002', 'Canadian', '+1-202-555-0802', 'aisha@newstoday.ca'],
                ['Leo Fernandez', 'City Journal', 'Editor', 'PRESS-003', 'British', '+1-202-555-0803', 'leo@cityjournal.co.uk'],
            ],
            'govt' => [
                ['Officer R. Singh', 'Revenue Department', '1 Government Rd, Delhi 110001', 'Inspector', 'Tax Division', 'GOVT-1100', 'Indian', '1980-06-15', 'Block 5, Nehru Place, Delhi 110019', '+91-11-5555-0901', 'r.singh@revenue.gov.in'],
                ['Ms. A. Kumar', 'Revenue Department', '1 Government Rd, Delhi 110001', 'Superintendent', 'Customs Division', 'GOVT-1205', 'Indian', '1985-02-28', 'Flat 3, Vasant Kunj, Delhi 110070', '+91-11-5555-0902', 'a.kumar@customs.gov.in'],
                ['Mr. V. Mishra', 'Revenue Department', '1 Government Rd, Delhi 110001', 'Assistant Commissioner', 'GST Division', 'GOVT-1330', 'Indian', '1978-09-10', 'H-14, Saket, Delhi 110017', '+91-11-5555-0903', 'v.mishra@gst.gov.in'],
            ],
            'security' => [
                ['Guard Sam White', 'SecurePro Services', 'Senior Guard', 'GARD-001', 'Zone A - Entry', '1988-07-04', '+1-504-555-1001', 'A+'],
                ['Guard Pat Brown', 'SecurePro Services', 'Patrol Officer', 'GARD-002', 'Zone B - Perimeter', '1990-12-15', '+1-504-555-1002', 'O+'],
                ['Guard Kim Gray', 'SecurePro Services', 'Control Room', 'GARD-003', 'Zone C - CCTV', '1992-03-21', '+1-504-555-1003', 'B-'],
            ],
            // Vertical variants reuse the same sample data as the base template
            'corporate_v' => null,
            'student_v'   => null,
            'hospital_v'  => null,
            'event_v'     => null,
            'visitor_v'   => null,
            'govt_v'      => null,
            'ngo_v'       => null,
            'library_v'   => null,
            'gym_v'       => null,
            'transport_v' => null,
            'university_v'=> null,
            'security_v'  => null,
            'retail_v'    => null,
        ];

        // Fallback mappings for vertical variants
        $fallbacks = [
            'corporate_v'  => 'corporate',
            'student_v'    => 'student',
            'hospital_v'   => 'medical',
            'event_v'      => 'event',
            'visitor_v'    => 'visitor',
            'govt_v'       => 'govt',
            'ngo_v'        => 'corporate',
            'library_v'    => 'student',
            'gym_v'        => 'visitor',
            'transport_v'  => 'corporate',
            'university_v' => 'corporate',
            'security_v'   => 'security',
            'retail_v'     => 'corporate',
        ];

        // Resolve the sample data to use
        $rawRows = $samples[$templateKey] ?? null;
        if ($rawRows === null && isset($fallbacks[$templateKey])) {
            $rawRows = $samples[$fallbacks[$templateKey]] ?? null;
        }

        if (!$rawRows) {
            // Generic fallback: 3 rows of empty strings
            $rawRows = [
                array_fill(0, count($fields), 'Sample Value'),
                array_fill(0, count($fields), 'Sample Value 2'),
                array_fill(0, count($fields), 'Sample Value 3'),
            ];
        }

        // Trim each raw row to match the number of fields
        $fieldCount = count($fields);
        return array_map(function ($row) use ($fieldCount) {
            $row = array_values($row);
            while (count($row) < $fieldCount) {
                $row[] = '';
            }
            return array_slice($row, 0, $fieldCount);
        }, $rawRows);
    }

    // ------------------------------------------------------------------ //
    //  View all cards generated via bulk (user's bulk card list)          //
    // ------------------------------------------------------------------ //

    public function viewCards(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            header('Location: /login');
            exit;
        }

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;

        // Fetch only bulk-generated cards for this user, newest first
        $cards = $this->model->getByUserBulk($userId, $perPage, $offset);
        $total = $this->model->countByUserBulk($userId);
        $pages = max(1, (int) ceil($total / $perPage));

        $this->render('bulk_cards', [
            'title'     => 'My Bulk-Generated Cards',
            'user'      => Auth::user(),
            'cards'     => $cards,
            'total'     => $total,
            'page'      => $page,
            'perPage'   => $perPage,
            'pages'     => $pages,
            'templates' => $this->config['templates'],
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Helpers                                                             //
    // ------------------------------------------------------------------ //

    /**
     * Find the first CSV column index matching any of the given names (case-insensitive).
     */
    private function findCsvColumn(array $headers, array $names): ?int
    {
        foreach ($names as $name) {
            $lcName = strtolower(trim($name));
            foreach ($headers as $idx => $h) {
                if (strtolower(trim($h)) === $lcName) {
                    return $idx;
                }
            }
        }
        return null;
    }

    private function sanitize(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    private function sanitizeColor(string $value): string
    {
        $value = trim($value);
        if (preg_match('/^#[0-9a-fA-F]{3,6}$/', $value)) {
            return $value;
        }
        return '#000000';
    }

    private function sanitizeFont(string $value): string
    {
        $allowed = ['Poppins','Inter','Roboto','Lato','Open Sans','Montserrat','Raleway'];
        return in_array($value, $allowed, true) ? $value : 'Poppins';
    }

    protected function render(string $view, array $data = []): void
    {
        extract($data);
        ob_start();
        include PROJECT_PATH . '/views/' . $view . '.php';
        $content = ob_get_clean();
        include PROJECT_PATH . '/views/layout.php';
    }
}
