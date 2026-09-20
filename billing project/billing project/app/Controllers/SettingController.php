<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

class SettingController extends Controller
{
    private User $userModel;

    public function __construct() { $this->userModel = new User(); }

    public function index(): void
    {
        $this->requireAuth();
        $config = require BASE_PATH . '/config/config.php';
        $this->view('settings.index', [
            'title'      => 'Settings — FinPilot',
            'layout'     => 'app',
            'user'       => $this->currentUser(),
            'currencies' => $config['currencies'],
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $action = $this->input('action', 'profile');

        if ($action === 'profile') {
            $data = $this->only(['name','email','currency']);
            $errors = $this->validate($data, ['name' => 'required', 'email' => 'required|email']);
            if ($errors) { Session::setFlash('error', implode(' ', $errors)); $this->redirect(url('settings')); return; }
            $this->userModel->updateProfile($userId, $data);

            // Refresh session user
            $updated = $this->userModel->find($userId);
            Session::set('user', $updated);
            Session::setFlash('success', 'Profile updated.');
        } elseif ($action === 'password') {
            $current = $this->input('current_password', '');
            $new     = $this->input('new_password', '');
            $confirm = $this->input('confirm_password', '');

            if ($new !== $confirm) { Session::setFlash('error', 'New passwords do not match.'); $this->redirect(url('settings')); return; }
            if (strlen($new) < 8)  { Session::setFlash('error', 'Password must be at least 8 characters.'); $this->redirect(url('settings')); return; }

            if (!$this->userModel->changePassword($userId, $current, $new)) {
                Session::setFlash('error', 'Current password is incorrect.');
                $this->redirect(url('settings'));
                return;
            }
            Session::setFlash('success', 'Password changed successfully.');
        }

        $this->redirect(url('settings'));
    }

    public function resetData(): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        // Safely delete only the current user's data; users table row is preserved
        $db = \App\Core\Database::getInstance();
        $db->execute("DELETE FROM transactions WHERE user_id = ?", [$userId]);
        $db->execute("DELETE FROM budgets WHERE user_id = ?", [$userId]);
        $db->execute("DELETE FROM goals WHERE user_id = ?", [$userId]);
        $db->execute("DELETE FROM recurring_obligations WHERE user_id = ?", [$userId]);
        $db->execute("DELETE FROM statement_imports WHERE user_id = ?", [$userId]);
        $db->execute("UPDATE accounts SET balance = 0 WHERE user_id = ?", [$userId]);

        Session::setFlash('success', 'All your financial data has been reset. Your account and settings are intact.');
        $this->redirect(url('dashboard'));
    }
}
