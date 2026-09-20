<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected array $data = [];

    protected function view(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data);

        $viewPath = dirname(__DIR__, 2) . '/views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View {$view} not found");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Check if layout should be used
        if (isset($this->data['layout']) && $this->data['layout'] !== false) {
            $layoutPath = dirname(__DIR__, 2) . '/views/layouts/' . $this->data['layout'] . '.php';
            if (file_exists($layoutPath)) {
                require $layoutPath;
                return;
            }
        }

        echo $content;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect(string $url): void
    {
        Router::redirect($url);
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    protected function only(array $keys): array
    {
        $data = [];
        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                $data[$key] = $_POST[$key];
            }
        }
        return $data;
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value = $data[$field] ?? null;
            
            foreach ($fieldRules as $rule) {
                $params = [];
                if (strpos($rule, ':') !== false) {
                    [$rule, $paramString] = explode(':', $rule);
                    $params = explode(',', $paramString);
                }
                
                $error = $this->validateRule($field, $value, $rule, $params);
                if ($error) {
                    $errors[$field] = $error;
                    break;
                }
            }
        }
        
        return $errors;
    }

    private function validateRule(string $field, mixed $value, string $rule, array $params): ?string
    {
        $fieldName = ucfirst(str_replace('_', ' ', $field));
        
        return match ($rule) {
            'required' => empty($value) && $value !== '0' ? "{$fieldName} is required" : null,
            'email'    => !filter_var($value, FILTER_VALIDATE_EMAIL) ? "{$fieldName} must be a valid email" : null,
            'min'      => strlen((string)$value) < (int)$params[0] ? "{$fieldName} must be at least {$params[0]} characters" : null,
            'max'      => strlen((string)$value) > (int)$params[0] ? "{$fieldName} must not exceed {$params[0]} characters" : null,
            'numeric'  => !is_numeric($value) ? "{$fieldName} must be numeric" : null,
            'date'     => !strtotime($value) ? "{$fieldName} must be a valid date" : null,
            default    => null,
        };
    }

    protected function setFlash(string $type, string $message): void
    {
        Session::setFlash($type, $message);
    }

    protected function getFlash(string $type): ?string
    {
        return Session::getFlash($type);
    }

    // ── Authentication Gate ───────────────────────────────────────────────────

    /**
     * Enforce authentication. Redirects to /login if not logged in.
     * Call at the start of any protected controller method.
     */
    protected function requireAuth(): void
    {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Please log in to continue.');
            $this->redirect(url('login'));
            exit;
        }
    }

    /**
     * Return the current authenticated user array from session.
     */
    protected function currentUser(): array
    {
        return Session::get('user') ?? [];
    }

    /**
     * Return the current authenticated user's ID.
     */
    protected function currentUserId(): int
    {
        return (int) ($this->currentUser()['id'] ?? 0);
    }
}
