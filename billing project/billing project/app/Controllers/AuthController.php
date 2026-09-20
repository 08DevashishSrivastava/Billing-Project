<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function loginForm(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect(url('dashboard'));
            return;
        }
        $this->view('auth.login', ['title' => 'Sign In — FinPilot', 'layout' => false]);
    }

    public function login(): void
    {
        $email    = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            Session::setFlash('error', 'Email and password are required.');
            $this->redirect(url('login'));
            return;
        }

        $user = $this->userModel->authenticate($email, $password);
        if (!$user) {
            Session::setFlash('error', 'Invalid email or password.');
            $this->redirect(url('login'));
            return;
        }

        Session::login($user);
        Session::setFlash('success', 'Welcome back, ' . e($user['name']) . '!');
        $this->redirect(url('dashboard'));
    }

    public function registerForm(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect(url('dashboard'));
            return;
        }
        $this->view('auth.register', ['title' => 'Create Account — FinPilot', 'layout' => false]);
    }

    public function register(): void
    {
        $name     = trim((string)($_POST['name']     ?? ''));
        $email    = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');
        $currency = trim((string)($_POST['currency'] ?? '$'));

        $errors = $this->validate(
            ['name' => $name, 'email' => $email, 'password' => $password],
            ['name' => 'required|min:2', 'email' => 'required|email', 'password' => 'required|min:8']
        );

        if ($errors) {
            Session::setFlash('error', implode(' ', $errors));
            $this->redirect(url('register'));
            return;
        }

        try {
            $userId = $this->userModel->register($name, $email, $password, $currency);
            $user   = $this->userModel->find($userId);
            Session::login($user);
            Session::setFlash('success', 'Welcome to FinPilot, ' . e($name) . '! Let\'s set up your first account.');
            $this->redirect(url('accounts'));
        } catch (\RuntimeException $e) {
            Session::setFlash('error', $e->getMessage());
            $this->redirect(url('register'));
        }
    }

    public function logout(): void
    {
        Session::logout();
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        $this->redirect(url('login'));
    }
}
