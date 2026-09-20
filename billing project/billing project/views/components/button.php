<?php
/**
 * FinPilot Reusable Component: Button
 * 
 * Props:
 * @var string $label          Button label text
 * @var string $variant        'primary' | 'secondary' | 'danger' | 'ghost' (default: 'primary')
 * @var string $size           'sm' | 'md' | 'lg' (default: 'md')
 * @var string|null $icon      Bootstrap icon class (e.g. 'bi-plus-lg')
 * @var string $iconPosition   'left' | 'right' (default: 'left')
 * @var string|null $href      If provided, renders <a> tag instead of <button>
 * @var string $type           'button' | 'submit' | 'reset' (default: 'button')
 * @var string|null $id        Element ID
 * @var string $class          Additional CSS classes
 * @var bool $disabled         Disabled state
 * @var array $attributes      Additional key => value HTML attributes
 */

$variant = $variant ?? 'primary';
$size = $size ?? 'md';
$icon = $icon ?? null;
$iconPosition = $iconPosition ?? 'left';
$href = $href ?? null;
$type = $type ?? 'button';
$id = $id ?? null;
$class = $class ?? '';
$disabled = $disabled ?? false;
$attributes = $attributes ?? [];

$variantClass = match($variant) {
    'secondary' => 'fp-btn-secondary',
    'danger'    => 'fp-btn-danger',
    'ghost'     => 'fp-btn-ghost',
    default     => 'fp-btn-primary',
};

$sizeClass = match($size) {
    'sm' => 'fp-btn-sm',
    'lg' => 'fp-btn-lg',
    default => 'fp-btn-md',
};

$attrString = '';
foreach ($attributes as $k => $v) {
    $attrString .= ' ' . e($k) . '="' . e($v) . '"';
}
?>
<?php if ($href && !$disabled): ?>
    <a href="<?= e($href) ?>"
       <?= $id ? 'id="' . e($id) . '"' : '' ?>
       class="fp-btn <?= $variantClass ?> <?= $sizeClass ?> <?= e($class) ?>"
       <?= $attrString ?>>
        <?php if ($icon && $iconPosition === 'left'): ?>
            <i class="bi <?= e($icon) ?>"></i>
        <?php endif; ?>
        <span><?= e($label) ?></span>
        <?php if ($icon && $iconPosition === 'right'): ?>
            <i class="bi <?= e($icon) ?>"></i>
        <?php endif; ?>
    </a>
<?php else: ?>
    <button type="<?= e($type) ?>"
            <?= $id ? 'id="' . e($id) . '"' : '' ?>
            class="fp-btn <?= $variantClass ?> <?= $sizeClass ?> <?= e($class) ?>"
            <?= $disabled ? 'disabled' : '' ?>
            <?= $attrString ?>>
        <?php if ($icon && $iconPosition === 'left'): ?>
            <i class="bi <?= e($icon) ?>"></i>
        <?php endif; ?>
        <span><?= e($label) ?></span>
        <?php if ($icon && $iconPosition === 'right'): ?>
            <i class="bi <?= e($icon) ?>"></i>
        <?php endif; ?>
    </button>
<?php endif; ?>
