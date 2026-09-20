<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AgentService;

class AgentController extends Controller
{
    private AgentService $agent;

    public function __construct()
    {
        $this->agent = new AgentService();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('agent.index', [
            'title'  => 'AI Decision Agent — FinPilot',
            'layout' => 'app',
        ]);
    }

    public function query(): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $query  = trim((string)($this->input('query', '')));

        if ($query === '') {
            $this->json(['error' => 'Query cannot be empty.'], 400);
            return;
        }

        $result = $this->agent->processQuery($userId, $query);
        $this->json($result);
    }
}
