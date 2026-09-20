<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Budget;
use App\Models\Category;
use App\Services\BudgetService;

class BudgetController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $userId  = $this->currentUserId();
        $month   = $this->input('month', date('Y-m'));
        $budgetSvc  = new BudgetService();
        $catModel   = new Category();

        $this->view('budgets.index', [
            'title'         => 'Budgets — FinPilot',
            'layout'        => 'app',
            'summary'       => $budgetSvc->monthSummary($userId, $month),
            'expenseCategories' => $catModel->forUserByType($userId, 'expense'),
            'month'         => $month,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $userId     = $this->currentUserId();
        $categoryId = (int)$this->input('category_id', 0);
        $amount     = (float)$this->input('amount', 0);
        $month      = $this->input('month', date('Y-m'));

        if ($categoryId <= 0 || $amount <= 0) {
            Session::setFlash('error', 'Category and amount are required.');
            $this->redirect(url('budgets'));
            return;
        }

        (new Budget())->upsert($userId, $categoryId, $amount, $month);
        Session::setFlash('success', 'Budget saved.');
        $this->redirect(url('budgets?month=' . $month));
    }

    public function update(int $id): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $budget = (new Budget())->findForUser($id, $userId);
        if (!$budget) { Session::setFlash('error', 'Budget not found.'); $this->redirect(url('budgets')); return; }
        (new Budget())->update($id, ['amount' => abs((float)$this->input('amount', $budget['amount']))]);
        Session::setFlash('success', 'Budget updated.');
        $this->redirect(url('budgets'));
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $budget = (new Budget())->findForUser($id, $userId);
        if (!$budget) { Session::setFlash('error', 'Budget not found.'); $this->redirect(url('budgets')); return; }
        (new Budget())->delete($id);
        Session::setFlash('success', 'Budget removed.');
        $this->redirect(url('budgets'));
    }
}
