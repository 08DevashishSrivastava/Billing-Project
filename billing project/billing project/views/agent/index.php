<!-- AI Decision Agent -->
<div class="max-w-3xl mx-auto py-2">

    <!-- Hero Banner -->
    <div class="fp-card p-6 lg:p-8 mb-6 text-center relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/15 to-purple-600/15 pointer-events-none"></div>
        <div class="absolute -top-12 -left-12 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mx-auto mb-3.5 shadow-[0_0_24px_rgba(99,102,241,0.45)] border border-white/20">
                <i class="bi bi-robot text-2xl text-white"></i>
            </div>
            <h2 class="fp-font-display text-2xl font-bold text-white tracking-tight mb-1.5">FinPilot AI Decision Agent</h2>
            <p class="text-xs text-white/60 max-w-lg mx-auto leading-relaxed">
                Autonomous heuristic reasoning across your actual accounts, recurring bills, and budget limits.
                Every response is grounded strictly in <em>your</em> live data.
            </p>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-[11px] text-amber-300/80 mt-3 font-medium">
                <i class="bi bi-shield-exclamation text-xs"></i>
                <span>Decision support heuristic — verify critical capital decisions independently.</span>
            </div>
        </div>
    </div>

    <!-- Suggested Query Chips -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6" id="suggestions-grid">
        <?php foreach ([
            ['Where did I spend the most this month?', 'bi-bar-chart', 'Outflow Breakdown'],
            ['How are my budgets doing?', 'bi-pie-chart', 'Utilization Check'],
            ['What are my upcoming subscriptions?', 'bi-arrow-repeat', 'Commitments'],
            ['How far am I from my savings goals?', 'bi-trophy', 'Milestones'],
            ['Show me my monthly cash flow summary', 'bi-graph-up', 'Inflow vs Outflow'],
            ['Any unusual spending or anomalies?', 'bi-exclamation-triangle', 'Risk Detection'],
        ] as [$q, $icon, $tag]): ?>
            <button onclick="askAgent('<?= addslashes($q) ?>')"
                    class="text-left fp-card fp-card-interactive p-3.5 transition flex items-center gap-3 group">
                <div class="w-8 h-8 rounded-xl bg-indigo-500/15 text-indigo-400 flex items-center justify-center border border-indigo-500/30 flex-shrink-0 group-hover:scale-105 transition">
                    <i class="bi <?= $icon ?> text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="text-[10px] text-white/40 uppercase tracking-wider block font-mono"><?= $tag ?></span>
                    <span class="text-xs text-white/80 font-medium truncate block"><?= e($q) ?></span>
                </div>
                <i class="bi bi-arrow-right text-xs text-white/30 group-hover:text-indigo-400 group-hover:translate-x-0.5 transition"></i>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Chat Console Container -->
    <div class="fp-card overflow-hidden shadow-2xl border-white/15">
        <!-- Chat Stream Header -->
        <div class="px-5 py-3 border-b border-white/10 bg-white/[0.02] flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
                <span class="fp-badge-dot pulse" style="background: #10b981;"></span>
                <span class="font-semibold text-white/80">Neural Telemetry Stream</span>
            </div>
            <span class="text-[11px] text-white/40 font-mono">Grounded: Active DB</span>
        </div>

        <!-- Messages Viewport (ID preserved: #chat-messages) -->
        <div id="chat-messages" class="p-5 space-y-4 min-h-[260px] max-h-[420px] overflow-y-auto">
            <!-- Greeting Message -->
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center flex-shrink-0 border border-indigo-500/30">
                    <i class="bi bi-robot text-sm"></i>
                </div>
                <div class="fp-card p-4 max-w-lg text-xs text-white/80 leading-relaxed border-indigo-500/20">
                    <p class="font-medium text-white mb-1">Welcome to FinPilot Agent.</p>
                    <p>
                        I can analyze your transactions, calculate remaining budget runaways, track goal trajectories, and surface anomalous charges. Click any prompt above or query directly below.
                    </p>
                </div>
            </div>
        </div>

        <!-- Input Bar (IDs preserved: #agent-input, #send-btn) -->
        <div class="border-t border-white/10 p-4 bg-white/[0.01] flex gap-3 items-center">
            <div class="relative flex-1">
                <input type="text"
                       id="agent-input"
                       placeholder="Ask about spending, budgets, goals, or recurring bills…"
                       class="fp-input text-xs py-3 pl-4 pr-10 text-white"
                       onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendQuery();}">
                <i class="bi bi-stars absolute right-3 top-1/2 -translate-y-1/2 text-indigo-400 text-sm pointer-events-none"></i>
            </div>
            <button onclick="sendQuery()"
                    id="send-btn"
                    class="fp-btn fp-btn-primary fp-btn-md py-3 px-5 text-xs font-semibold flex items-center gap-2">
                <i class="bi bi-send-fill"></i>
                <span class="hidden sm:inline">Ask Agent</span>
            </button>
        </div>
    </div>
</div>

<script>
const chatMessages = document.getElementById('chat-messages');
const agentInput   = document.getElementById('agent-input');
const sendBtn      = document.getElementById('send-btn');

function askAgent(query) {
    agentInput.value = query;
    sendQuery();
}

function sendQuery() {
    const query = agentInput.value.trim();
    if (!query) return;

    // Append user message
    appendMessage('user', query);
    agentInput.value = '';
    sendBtn.disabled = true;

    // Show typing indicator
    const typingId = appendTyping();

    fetch('<?= url('agent/query') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ query, _csrf_token: '<?= \App\Core\Session::csrf() ?>' })
    })
    .then(r => r.json())
    .then(data => {
        removeTyping(typingId);
        if (data.error) {
            appendMessage('agent', '❌ ' + data.error, []);
        } else {
            appendMessage('agent', data.answer, data.suggestions || []);
        }
    })
    .catch(() => {
        removeTyping(typingId);
        appendMessage('agent', '❌ An unexpected connection error occurred. Please try again.', []);
    })
    .finally(() => {
        sendBtn.disabled = false;
        agentInput.focus();
    });
}

function appendMessage(role, text, suggestions = []) {
    const isUser = role === 'user';
    const div = document.createElement('div');
    div.className = 'flex items-start gap-3 ' + (isUser ? 'flex-row-reverse' : '');

    const avatar = isUser
        ? `<div class="w-8 h-8 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center flex-shrink-0 border border-purple-500/30"><i class="bi bi-person text-sm"></i></div>`
        : `<div class="w-8 h-8 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center flex-shrink-0 border border-indigo-500/30"><i class="bi bi-robot text-sm"></i></div>`;

    // Markdown simple conversions
    const formatted = text.replace(/\*\*(.*?)\*\*/g, '<strong class="text-white font-semibold">$1</strong>').replace(/\n/g, '<br>');

    let suggestHtml = '';
    if (suggestions.length) {
        suggestHtml = '<div class="mt-3 pt-2.5 border-t border-white/10 flex flex-wrap gap-1.5">' +
            suggestions.slice(0, 3).map(s =>
                `<button onclick="askAgent('${s.replace(/'/g,"\\'")}')"`+
                ` class="text-[11px] bg-indigo-500/15 hover:bg-indigo-500/30 text-indigo-300 px-2.5 py-1 rounded-full border border-indigo-500/25 transition">${s}</button>`
            ).join('') + '</div>';
    }

    const cardClass = isUser
        ? 'bg-purple-600/15 border border-purple-500/25 text-white/90'
        : 'fp-card border-white/10 text-white/80';

    div.innerHTML = avatar + `<div class="${cardClass} p-3.5 rounded-2xl max-w-lg text-xs leading-relaxed">${formatted}${suggestHtml}</div>`;
    chatMessages.appendChild(div);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function appendTyping() {
    const id = 'typing-' + Date.now();
    const div = document.createElement('div');
    div.id = id;
    div.className = 'flex items-start gap-3';
    div.innerHTML = `<div class="w-8 h-8 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center flex-shrink-0 border border-indigo-500/30"><i class="bi bi-robot text-sm"></i></div>
        <div class="fp-card p-3 rounded-2xl"><span class="text-white/40 text-xs animate-pulse font-mono flex items-center gap-1.5"><i class="bi bi-cpu"></i> Reasoning across financial telemetry…</span></div>`;
    chatMessages.appendChild(div);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    return id;
}

function removeTyping(id) {
    document.getElementById(id)?.remove();
}
</script>
