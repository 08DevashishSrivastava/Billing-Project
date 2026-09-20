<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\Category;
use App\Services\TransactionService;

class TransactionController extends Controller
{
    private Transaction $txModel;
    private Account $accModel;
    private Category $catModel;
    private TransactionService $txService;

    public function __construct()
    {
        $this->txModel   = new Transaction();
        $this->accModel  = new Account();
        $this->catModel  = new Category();
        $this->txService = new TransactionService();
    }

    public function index(): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();

        $filters = [
            'type'        => $this->input('type'),
            'category_id' => $this->input('category_id'),
            'account_id'  => $this->input('account_id'),
            'month'       => $this->input('month'),
            'search'      => $this->input('search'),
        ];
        $page = max(1, (int)$this->input('page', 1));
        $result = $this->txModel->listForUser($userId, $filters, $page, 15);

        $this->view('transactions.index', [
            'title'      => 'Transactions — FinPilot',
            'layout'     => 'app',
            'result'     => $result,
            'accounts'   => $this->accModel->forUser($userId),
            'categories' => $this->catModel->forUser($userId),
            'filters'    => $filters,
            'page'       => $page,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $this->view('transactions.create', [
            'title'      => 'Add Transaction — FinPilot',
            'layout'     => 'app',
            'accounts'   => $this->accModel->forUser($userId),
            'categories' => $this->catModel->forUser($userId),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();

        $data = $this->only(['account_id','category_id','transaction_date','amount','type','description','merchant','notes']);
        $errors = $this->validate($data, [
            'account_id'       => 'required',
            'transaction_date' => 'required|date',
            'amount'           => 'required|numeric',
            'type'             => 'required',
            'description'      => 'required',
        ]);

        if ($errors) {
            Session::setFlash('error', implode(' ', $errors));
            $this->redirect(url('transactions/create'));
            return;
        }

        $data['amount']      = abs((float)$data['amount']);
        $data['category_id'] = !empty($data['category_id']) ? (int)$data['category_id'] : null;
        $this->txService->create($userId, $data);
        Session::setFlash('success', 'Transaction added successfully.');
        $this->redirect(url('transactions'));
    }

    public function edit(int $id): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $tx = $this->txModel->findForUser($id, $userId);
        if (!$tx) { Session::setFlash('error', 'Transaction not found.'); $this->redirect(url('transactions')); return; }

        $this->view('transactions.create', [
            'title'      => 'Edit Transaction — FinPilot',
            'layout'     => 'app',
            'tx'         => $tx,
            'accounts'   => $this->accModel->forUser($userId),
            'categories' => $this->catModel->forUser($userId),
        ]);
    }

    public function update(int $id): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $data = $this->only(['account_id','category_id','transaction_date','amount','type','description','merchant','notes']);
        $data['amount']      = abs((float)($data['amount'] ?? 0));
        $data['category_id'] = !empty($data['category_id']) ? (int)$data['category_id'] : null;
        $this->txService->update($id, $userId, $data);
        Session::setFlash('success', 'Transaction updated.');
        $this->redirect(url('transactions'));
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $this->txService->delete($id, $userId);
        Session::setFlash('success', 'Transaction deleted.');
        $this->redirect(url('transactions'));
    }
}
