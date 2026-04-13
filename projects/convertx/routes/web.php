<?php
/**
 * ConvertX Routes
 *
 * @package MMB\Projects\ConvertX
 */

use Core\Auth;

$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri      = str_replace('/projects/convertx', '', $uri);
$uri      = $uri ?: '/';
$segments = explode('/', trim($uri, '/'));

switch ($segments[0]) {
    case '':
    case 'dashboard':
        require_once PROJECT_PATH . '/controllers/DashboardController.php';
        (new \Projects\ConvertX\Controllers\DashboardController())->index();
        break;

    case 'convert':
        require_once PROJECT_PATH . '/controllers/ConversionController.php';
        $ctrl = new \Projects\ConvertX\Controllers\ConversionController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->submit();
        } else {
            $ctrl->showForm();
        }
        break;

    case 'ai-process':
        require_once PROJECT_PATH . '/controllers/AIProcessController.php';
        $ctrl = new \Projects\ConvertX\Controllers\AIProcessController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->process();
        } else {
            $ctrl->show();
        }
        break;

    case 'job':
        require_once PROJECT_PATH . '/controllers/ConversionController.php';
        $ctrl = new \Projects\ConvertX\Controllers\ConversionController();
        $id   = (int) ($segments[1] ?? 0);
        $action = $segments[2] ?? 'status';
        if ($id) {
            match ($action) {
                'download' => $ctrl->download($id),
                'cancel'   => $ctrl->cancel($id),
                default    => $ctrl->status($id),
            };
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Job not found']);
        }
        break;

    case 'history':
        require_once PROJECT_PATH . '/controllers/ConversionController.php';
        (new \Projects\ConvertX\Controllers\ConversionController())->history();
        break;

    case 'batch':
        require_once PROJECT_PATH . '/controllers/BatchController.php';
        $ctrl   = new \Projects\ConvertX\Controllers\BatchController();
        $action = $segments[1] ?? '';

        if ($action === 'status' && isset($segments[2])) {
            // GET /batch/status/:batchId  — JSON status for all jobs in a batch
            $ctrl->batchStatus($segments[2]);
        } elseif ($action === 'download' && isset($segments[2])) {
            // GET /batch/download/:batchId  — stream all completed files as ZIP
            $ctrl->batchDownloadZip($segments[2]);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->submit();
        } else {
            $ctrl->showForm();
        }
        break;

    case 'pdf-merge':
        require_once PROJECT_PATH . '/controllers/PdfToolsController.php';
        $ctrl = new \Projects\ConvertX\Controllers\PdfToolsController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->submitMerge();
        } else {
            $ctrl->showMerge();
        }
        break;

    case 'pdf-split':
        require_once PROJECT_PATH . '/controllers/PdfToolsController.php';
        $ctrl = new \Projects\ConvertX\Controllers\PdfToolsController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->submitSplit();
        } else {
            $ctrl->showSplit();
        }
        break;

    case 'pdf-compress':
        require_once PROJECT_PATH . '/controllers/PdfToolsController.php';
        $ctrl = new \Projects\ConvertX\Controllers\PdfToolsController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->submitCompressPdf();
        } else {
            $ctrl->showCompressPdf();
        }
        break;

    case 'img-compress':
        require_once PROJECT_PATH . '/controllers/PdfToolsController.php';
        $ctrl = new \Projects\ConvertX\Controllers\PdfToolsController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->submitCompressImages();
        } else {
            $ctrl->showCompressImages();
        }
        break;

    case 'img-resize':
        require_once PROJECT_PATH . '/controllers/PdfToolsController.php';
        $ctrl = new \Projects\ConvertX\Controllers\PdfToolsController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->submitResizeImages();
        } else {
            $ctrl->showResizeImages();
        }
        break;

    case 'img-crop':
        require_once PROJECT_PATH . '/controllers/PdfToolsController.php';
        $ctrl = new \Projects\ConvertX\Controllers\PdfToolsController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->submitCropImage();
        } else {
            $ctrl->showCropImage();
        }
        break;

    case 'img-watermark':
        require_once PROJECT_PATH . '/controllers/PdfToolsController.php';
        $ctrl = new \Projects\ConvertX\Controllers\PdfToolsController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->submitWatermarkImage();
        } else {
            $ctrl->showWatermarkImage();
        }
        break;

    case 'img-meme':
        require_once PROJECT_PATH . '/controllers/PdfToolsController.php';
        $ctrl = new \Projects\ConvertX\Controllers\PdfToolsController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->submitMemeGenerator();
        } else {
            $ctrl->showMemeGenerator();
        }
        break;

    case 'img-rotate':
        require_once PROJECT_PATH . '/controllers/PdfToolsController.php';
        $ctrl = new \Projects\ConvertX\Controllers\PdfToolsController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->submitRotateImages();
        } else {
            $ctrl->showRotateImages();
        }
        break;

    case 'img-editor':
        require_once PROJECT_PATH . '/controllers/PdfToolsController.php';
        (new \Projects\ConvertX\Controllers\PdfToolsController())->showPhotoEditor();
        break;

    case 'img-upscale':
        require_once PROJECT_PATH . '/controllers/PdfToolsController.php';
        (new \Projects\ConvertX\Controllers\PdfToolsController())->showUpscaleImage();
        break;

    case 'img-remove-bg':
        require_once PROJECT_PATH . '/controllers/PdfToolsController.php';
        (new \Projects\ConvertX\Controllers\PdfToolsController())->showRemoveBg();
        break;

    case 'pdf-tools':
        // Download token endpoint shared by all PDF tools features
        require_once PROJECT_PATH . '/controllers/PdfToolsController.php';
        $ctrl   = new \Projects\ConvertX\Controllers\PdfToolsController();
        $action = $segments[1] ?? '';
        if ($action === 'download' && isset($segments[2])) {
            $ctrl->download($segments[2]);
        } else {
            http_response_code(404);
            echo 'Not found';
        }
        break;

    case 'api':
        require_once PROJECT_PATH . '/controllers/ApiController.php';
        $ctrl   = new \Projects\ConvertX\Controllers\ApiController();
        $action = $segments[1] ?? '';
        match ($action) {
            'convert'  => $ctrl->convert(),
            'status'   => $ctrl->jobStatus($segments[2] ?? ''),
            'download' => $ctrl->download($segments[2] ?? ''),
            'history'  => $ctrl->history(),
            'usage'    => $ctrl->usage(),
            'formats'  => $ctrl->formats(),
            'plans'    => $ctrl->plans(),
            'jobs'     => $ctrl->cancelJob($segments[2] ?? ''),
            default    => $ctrl->index(),
        };
        break;

    case 'ocr':
        require_once PROJECT_PATH . '/controllers/OcrController.php';
        $ctrl   = new \Projects\ConvertX\Controllers\OcrController();
        $action = $segments[1] ?? '';
        if ($action === 'process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->process();
        } else {
            $ctrl->show();
        }
        break;

    case 'ocr-ai':
        require_once PROJECT_PATH . '/controllers/OcrController.php';
        $ctrl   = new \Projects\ConvertX\Controllers\OcrController();
        $action = $segments[1] ?? '';
        if ($action === 'process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->processAi();
        } else {
            $ctrl->showAi();
        }
        break;

    case 'docs':
        require_once PROJECT_PATH . '/controllers/DashboardController.php';
        (new \Projects\ConvertX\Controllers\DashboardController())->docs();
        break;

    case 'plan':
        require_once PROJECT_PATH . '/controllers/DashboardController.php';
        (new \Projects\ConvertX\Controllers\DashboardController())->plan();
        break;

    case 'settings':
        require_once PROJECT_PATH . '/controllers/SettingsController.php';
        $ctrl = new \Projects\ConvertX\Controllers\SettingsController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->update();
        } else {
            $ctrl->index();
        }
        break;

    case 'apikeys':
        require_once PROJECT_PATH . '/controllers/SettingsController.php';
        $ctrl = new \Projects\ConvertX\Controllers\SettingsController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->update();
        } else {
            $ctrl->apikeys();
        }
        break;

    default:
        http_response_code(404);
        echo 'Page not found';
        break;
}
