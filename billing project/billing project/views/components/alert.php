<?php
/**
 * FinPilot Reusable Component: Alert / Notification Callout
 * 
 * Props:
 * @var string $message        Alert text content
 * @var string|null $title     Optional alert title
 * @var string $variant        'info' | 'success' | 'warning' | 'danger' (default: 'info')
 * @var string|null $icon      Bootstrap icon class
 * @var bool $dismissible      Whether the alert has a dismiss button
 * @var string $class          Additional CSS classes
 */

$variant = $variant ?? 'info';
$title = $title ?? null;
$message = $message ?? '';
$dismissible = $dismissible ?? false;
$class = $class ?? '';

$defaultIcons = [
    'info'    => 'bi-info-circle-fill',
    'success' => 'bi-check-circle-fill',
    'warning' => 'bi-exclamation-triangle-fill',
    'danger'  => 'bi-x-circle-fill',
];

$icon = $icon ?? ($defaultIcons[$variant] ?? 'bi-info-circle');
$variantClass = 'fp-alert-' . $variant;
?>
<div class="fp-alert <?= $variantClass ?> <?= e($class) ?>" role="alert">
    <i class="bi <?= e($icon) ?> text-lg flex-shrink-0 mt-0.5"></i>
    <div class="flex-1 min-w-0">
        <?php if ($title): ?>
            <h4 class="font-semibold text-sm mb-0.5"><?= e($title) ?></h4>
        <?php endif; ?>
        <div class="text-sm opacity-90"><?= $message ?></div>
    </div>
    <?php if ($dismissible): ?>
        <button type="button"
                onclick="this.closest('.fp-alert').remove()"
                class="p-1 -mr-1 -mt-1 opacity-70 hover:opacity-100 transition"
                aria-label="Close">
            <i class="bi bi-x-lg text-xs"></i>
        </button>
    <?php endif; ?>
</div>
