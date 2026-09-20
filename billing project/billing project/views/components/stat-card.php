<?php
/**
 * FinPilot Reusable Component: Stat Card
 * 
 * Props:
 * @var string $title        Card title/metric name
 * @var string $value        Primary metric value
 * @var string $icon         Bootstrap icon class (e.g. 'bi-wallet2')
 * @var string|null $subtitle Optional subtitle / context
 * @var array|null $trend     Optional trend ['label' => '+8.4%', 'type' => 'positive'|'negative'|'neutral']
 * @var string $accent       Color accent ('indigo', 'green', 'red', 'purple', 'amber')
 * @var string $class        Optional additional CSS classes
 */

$accentColorMap = [
    'indigo' => ['bg' => 'bg-indigo-500/15', 'text' => 'text-indigo-400', 'border' => 'border-indigo-500/30'],
    'green'  => ['bg' => 'bg-emerald-500/15', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500/30'],
    'red'    => ['bg' => 'bg-rose-500/15', 'text' => 'text-rose-400', 'border' => 'border-rose-500/30'],
    'purple' => ['bg' => 'bg-purple-500/15', 'text' => 'text-purple-400', 'border' => 'border-purple-500/30'],
    'amber'  => ['bg' => 'bg-amber-500/15', 'text' => 'text-amber-400', 'border' => 'border-amber-500/30'],
];

$accent = $accent ?? 'indigo';
$accentTheme = $accentColorMap[$accent] ?? $accentColorMap['indigo'];
$trend = $trend ?? null;
?>
<div class="fp-stat-card <?= e($class ?? '') ?>">
    <div class="flex items-center justify-between mb-3">
        <span class="fp-label"><?= e($title ?? 'Metric') ?></span>
        <div class="w-9 h-9 rounded-xl <?= $accentTheme['bg'] ?> <?= $accentTheme['text'] ?> flex items-center justify-center border <?= $accentTheme['border'] ?>">
            <i class="bi <?= e($icon ?? 'bi-bar-chart') ?> text-base"></i>
        </div>
    </div>
    
    <div class="flex items-baseline justify-between gap-2">
        <p class="fp-font-display text-2xl lg:text-3xl font-bold tracking-tight text-white fp-nums">
            <?= e($value ?? '0.00') ?>
        </p>
        <?php if ($trend): ?>
            <?php
            $trendClass = match($trend['type'] ?? 'neutral') {
                'positive' => 'fp-badge-success',
                'negative' => 'fp-badge-danger',
                default    => 'fp-badge-neutral',
            };
            $trendIcon = match($trend['type'] ?? 'neutral') {
                'positive' => 'bi-arrow-up-right',
                'negative' => 'bi-arrow-down-right',
                default    => 'bi-dash',
            };
            ?>
            <span class="fp-badge <?= $trendClass ?> text-xs font-semibold">
                <i class="bi <?= $trendIcon ?>"></i>
                <?= e($trend['label'] ?? '') ?>
            </span>
        <?php endif; ?>
    </div>

    <?php if (!empty($subtitle)): ?>
        <p class="text-xs text-white/50 mt-2 flex items-center gap-1.5">
            <?= $subtitle ?>
        </p>
    <?php endif; ?>
</div>
