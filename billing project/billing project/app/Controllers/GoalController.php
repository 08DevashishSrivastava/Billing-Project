<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Goal;

class GoalController extends Controller
{
    private Goal $model;

    public function __construct() { $this->model = new Goal(); }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('goals.index', [
            'title'  => 'Savings Goals — FinPilot',
            'layout' => 'app',
            'goals'  => $this->model->forUser($this->currentUserId()),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $data = $this->only(['name','target_amount','current_amount','target_date','notes']);
        $errors = $this->validate($data, ['name' => 'required', 'target_amount' => 'required|numeric']);
        if ($errors) { Session::setFlash('error', implode(' ', $errors)); $this->redirect(url('goals')); return; }
        $data['user_id']        = $userId;
        $data['target_amount']  = (float)$data['target_amount'];
        $data['current_amount'] = (float)($data['current_amount'] ?? 0);
        $data['status']         = 'active';
        $this->model->create($data);
        Session::setFlash('success', 'Goal created!');
        $this->redirect(url('goals'));
    }

    public function update(int $id): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $goal = $this->model->findForUser($id, $userId);
        if (!$goal) { Session::setFlash('error', 'Goal not found.'); $this->redirect(url('goals')); return; }
        $data = $this->only(['name','target_amount','target_date','status','notes']);
        if (isset($data['target_amount'])) $data['target_amount'] = (float)$data['target_amount'];
        $this->model->update($id, $data);
        Session::setFlash('success', 'Goal updated.');
        $this->redirect(url('goals'));
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $goal = $this->model->findForUser($id, $userId);
        if (!$goal) { Session::setFlash('error', 'Goal not found.'); $this->redirect(url('goals')); return; }
        $this->model->delete($id);
        Session::setFlash('success', 'Goal deleted.');
        $this->redirect(url('goals'));
    }

    public function deposit(int $id): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $amount = (float)$this->input('amount', 0);
        if ($amount <= 0) { Session::setFlash('error', 'Deposit amount must be positive.'); $this->redirect(url('goals')); return; }
        $this->model->deposit($id, $userId, $amount);
        Session::setFlash('success', 'Deposit recorded!');
        $this->redirect(url('goals'));
    }
}
