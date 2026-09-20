<?php
/**
 * FinPilot Reusable Component: Badge / Telemetry Chip
 * 
 * Props:
 * @var string $label        Badge label text
 * @var string $variant      'income' | 'expense' | 'success' | 'warning' | 'info' | 'neutral' (default: 'neutral')
 * @var string|null $icon    Bootstrap icon class (e.g. 'bi-check-circle')
 * @var bool $dot            Whether to show a colored dot indicator
 * @var bool $pulse          Whether the dot should pulse
 * @var string $class        Additional CSS classes
 */

$variant = $variant ?? 'neutral';
$icon = $icon ?? null;
$dot = $dot ?? false;
$pulse = $pulse ?? false;
$class = $class ?? '';

$variantClass = match($variant) {
    'income'  => 'fp-badge-income',
    'expense' => 'fp-badge-expense',
    'success' => 'fp-badge-success',
    'warning' => 'fp-badge-warning',
    'info'    => 'fp-badge-info',
    default   => 'fp-badge-neutral',
};
?>
<span class="fp-badge <?= $variantClass ?> <?= e($class) ?>">
    <?php if ($dot): ?>
        <span class="fp-badge-dot <?= $pulse ? 'pulse' : '' ?>"></span>
    <?php endif; ?>
    <?php if ($icon): ?>
        <i class="bi <?= e($icon) ?>"></i>
    <?php endif; ?>
    <span><?= e($label ?? '') ?></span>
</span>
