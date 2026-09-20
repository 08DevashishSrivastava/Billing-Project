<?php
/**
 * FinPilot Reusable Component: Progress Bar & Gauge
 * 
 * Props:
 * @var float|int $value       Current value
 * @var float|int $max         Maximum value (default: 100)
 * @var string $variant        'auto' | 'primary' | 'success' | 'warning' | 'danger' (default: 'auto')
 * @var string|null $label     Left header label
 * @var string|null $sublabel  Right header label
 * @var string $size           'sm' (4px) | 'md' (8px) | 'lg' (12px)
 * @var string $class          Additional CSS classes
 */

$value = (float)($value ?? 0);
$max = (float)($max ?? 100);
$variant = $variant ?? 'auto';
$label = $label ?? null;
$sublabel = $sublabel ?? null;
$size = $size ?? 'md';
$class = $class ?? '';

$pct = $max > 0 ? min(100, max(0, round(($value / $max) * 100, 1))) : 0;

if ($variant === 'auto') {
    if ($pct >= 100) {
        $variantClass = 'fp-progress-danger';
    } elseif ($pct >= 80) {
        $variantClass = 'fp-progress-warning';
    } else {
        $variantClass = 'fp-progress-success';
    }
} else {
    $variantClass = match($variant) {
        'primary' => 'fp-progress-primary',
        'success' => 'fp-progress-success',
        'warning' => 'fp-progress-warning',
        'danger'  => 'fp-progress-danger',
        default   => 'fp-progress-primary',
    };
}

$trackHeight = match($size) {
    'sm' => 'h-1.5',
    'lg' => 'h-3',
    default => 'h-2',
};
?>
<div class="w-full <?= e($class) ?>">
    <?php if ($label || $sublabel): ?>
        <div class="flex items-center justify-between text-xs mb-1.5 font-medium">
            <span class="text-white/70"><?= $label ?? '' ?></span>
            <span class="text-white/50 fp-nums"><?= $sublabel ?? ($pct . '%') ?></span>
        </div>
    <?php endif; ?>

    <div class="fp-progress-track <?= $trackHeight ?>">
        <div class="fp-progress-fill <?= $variantClass ?>" style="width: <?= $pct ?>%;"></div>
    </div>
</div>
