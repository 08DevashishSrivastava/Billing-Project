<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Account;

class AccountController extends Controller
{
    private Account $model;

    public function __construct() { $this->model = new Account(); }

    public function index(): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $this->view('accounts.index', [
            'title'    => 'Accounts — FinPilot',
            'layout'   => 'app',
            'accounts' => $this->model->forUser($userId),
            'netWorth' => $this->model->totalNetWorthForUser($userId),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $data = $this->only(['name','type','balance','institution','color']);
        $errors = $this->validate($data, ['name' => 'required', 'type' => 'required']);
        if ($errors) { Session::setFlash('error', implode(' ', $errors)); $this->redirect(url('accounts')); return; }
        $data['user_id'] = $userId;
        $data['balance'] = (float)($data['balance'] ?? 0);
        $this->model->create($data);
        Session::setFlash('success', 'Account created.');
        $this->redirect(url('accounts'));
    }

    public function update(int $id): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $acc = $this->model->findForUser($id, $userId);
        if (!$acc) { Session::setFlash('error', 'Account not found.'); $this->redirect(url('accounts')); return; }
        $data = $this->only(['name','type','institution','color','is_active']);
        $this->model->update($id, $data);
        Session::setFlash('success', 'Account updated.');
        $this->redirect(url('accounts'));
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $acc = $this->model->findForUser($id, $userId);
        if (!$acc) { Session::setFlash('error', 'Account not found.'); $this->redirect(url('accounts')); return; }
        $this->model->delete($id);
        Session::setFlash('success', 'Account deleted.');
        $this->redirect(url('accounts'));
    }
}
