<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Account;
use App\Models\StatementImport;
use App\Services\ImportService;

class ImportController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $this->view('import.index', [
            'title'    => 'Import Statement — FinPilot',
            'layout'   => 'app',
            'accounts' => (new Account())->forUser($userId),
            'history'  => (new StatementImport())->historyForUser($userId),
        ]);
    }

    public function upload(): void
    {
        $this->requireAuth();
        $userId    = $this->currentUserId();
        $accountId = (int)$this->input('account_id', 0);
        $config    = require BASE_PATH . '/config/config.php';

        if ($accountId <= 0) {
            Session::setFlash('error', 'Please select an account.');
            $this->redirect(url('import'));
            return;
        }

        $file = $_FILES['statement'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'Please upload a valid CSV file.');
            $this->redirect(url('import'));
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'])) {
            Session::setFlash('error', 'Only CSV/TXT files are supported.');
            $this->redirect(url('import'));
            return;
        }

        $storagePath = $config['uploads']['storage_path'];
        if (!is_dir($storagePath)) mkdir($storagePath, 0755, true);
        $dest = $storagePath . '/' . uniqid('import_') . '.csv';
        move_uploaded_file($file['tmp_name'], $dest);

        // Column map: configurable (default: standard CSV format)
        $columnMap = [
            'date'        => $this->input('col_date', 'date'),
            'description' => $this->input('col_description', 'description'),
            'amount'      => $this->input('col_amount', 'amount'),
            'type'        => $this->input('col_type', 'type'),
        ];

        $svc   = new ImportService();
        $rows  = $svc->parseCsv($dest, $columnMap);

        // Store parsed rows in session for confirmation step
        Session::set('import_preview', ['rows' => $rows, 'account_id' => $accountId, 'filepath' => $dest]);
        Session::setFlash('info', count($rows) . ' rows parsed. Review and confirm below.');
        $this->redirect(url('import'));
    }

    public function confirm(): void
    {
        $this->requireAuth();
        $userId   = $this->currentUserId();
        $preview  = Session::get('import_preview');

        if (!$preview) {
            Session::setFlash('error', 'No pending import. Please upload a file first.');
            $this->redirect(url('import'));
            return;
        }

        Session::set('import_preview', null);

        $svc    = new ImportService();
        $result = $svc->importRows($userId, (int)$preview['account_id'], $preview['rows']);

        Session::setFlash('success',
            "Import complete: {$result['imported']} rows imported, {$result['duplicates']} duplicates skipped."
        );
        $this->redirect(url('import'));
    }
}
