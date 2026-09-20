<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\RecurringObligation;
use App\Models\Category;

class RecurringController extends Controller
{
    private RecurringObligation $model;

    public function __construct() { $this->model = new RecurringObligation(); }

    public function index(): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $this->view('recurring.index', [
            'title'      => 'Recurring Bills — FinPilot',
            'layout'     => 'app',
            'recurring'  => $this->model->activeForUser($userId),
            'projected'  => $this->model->projectedMonthlyCost($userId),
            'categories' => (new Category())->forUserByType($userId, 'expense'),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $data = $this->only(['merchant','expected_amount','frequency','is_subscription','category_id','next_due_date','last_payment_date']);
        $errors = $this->validate($data, ['merchant' => 'required', 'expected_amount' => 'required|numeric', 'frequency' => 'required']);
        if ($errors) { Session::setFlash('error', implode(' ', $errors)); $this->redirect(url('recurring')); return; }
        $data['user_id']         = $userId;
        $data['expected_amount'] = abs((float)$data['expected_amount']);
        $data['is_subscription'] = isset($data['is_subscription']) ? 1 : 0;
        $data['status']          = 'active';
        $data['confidence_score']= 1.00;
        $this->model->create($data);
        Session::setFlash('success', 'Recurring obligation added.');
        $this->redirect(url('recurring'));
    }

    public function update(int $id): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $r = $this->model->findForUser($id, $userId);
        if (!$r) { Session::setFlash('error', 'Not found.'); $this->redirect(url('recurring')); return; }
        $data = $this->only(['merchant','expected_amount','frequency','is_subscription','category_id','next_due_date','status']);
        if (isset($data['expected_amount'])) $data['expected_amount'] = abs((float)$data['expected_amount']);
        if (isset($data['is_subscription'])) $data['is_subscription'] = 1;
        $this->model->update($id, $data);
        Session::setFlash('success', 'Recurring obligation updated.');
        $this->redirect(url('recurring'));
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $r = $this->model->findForUser($id, $userId);
        if (!$r) { Session::setFlash('error', 'Not found.'); $this->redirect(url('recurring')); return; }
        $this->model->delete($id);
        Session::setFlash('success', 'Recurring obligation removed.');
        $this->redirect(url('recurring'));
    }
}
