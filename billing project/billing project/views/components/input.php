<?php
/**
 * FinPilot Reusable Component: Input Field
 * 
 * Props:
 * @var string $name           Input name attribute
 * @var string|null $label     Field label
 * @var string $type           Input type (default: 'text')
 * @var mixed $value           Initial / current value
 * @var string $placeholder    Placeholder text
 * @var bool $required         Required attribute
 * @var string|null $icon      Bootstrap icon class
 * @var string|null $prefix    Text or currency symbol prefix
 * @var string|null $error     Error message string
 * @var string|null $help      Helper text below input
 * @var string|null $id        Input ID (defaults to $name)
 * @var string $class          Additional input classes
 */

$name = $name ?? 'input_' . uniqid();
$id = $id ?? $name;
$type = $type ?? 'text';
$value = $value ?? '';
$placeholder = $placeholder ?? '';
$required = $required ?? false;
$icon = $icon ?? null;
$prefix = $prefix ?? null;
$error = $error ?? null;
$help = $help ?? null;
$class = $class ?? '';
?>
<div class="fp-input-group">
    <?php if ($label): ?>
        <label for="<?= e($id) ?>" class="fp-label flex items-center justify-between">
            <span>
                <?= e($label) ?>
                <?php if ($required): ?><span class="text-rose-400">*</span><?php endif; ?>
            </span>
        </label>
    <?php endif; ?>

    <div class="relative flex items-center">
        <?php if ($icon): ?>
            <span class="absolute left-3.5 text-white/40 pointer-events-none flex items-center">
                <i class="bi <?= e($icon) ?> text-sm"></i>
            </span>
        <?php endif; ?>

        <?php if ($prefix): ?>
            <span class="absolute <?= $icon ? 'left-9' : 'left-3.5' ?> text-white/50 font-semibold pointer-events-none fp-nums text-sm">
                <?= e($prefix) ?>
            </span>
        <?php endif; ?>

        <?php
        $paddingLeft = 'pl-3.5';
        if ($icon && $prefix) {
            $paddingLeft = 'pl-14';
        } elseif ($icon || $prefix) {
            $paddingLeft = 'pl-10';
        }
        ?>

        <input type="<?= e($type) ?>"
               name="<?= e($name) ?>"
               id="<?= e($id) ?>"
               value="<?= e((string)$value) ?>"
               placeholder="<?= e($placeholder) ?>"
               <?= $required ? 'required' : '' ?>
               class="fp-input <?= $paddingLeft ?> <?= $error ? '!border-rose-500/80 !shadow-[0_0_0_2px_rgba(244,63,94,0.2)]' : '' ?> <?= e($class) ?>"
               <?= isset($min) ? 'min="' . e($min) . '"' : '' ?>
               <?= isset($max) ? 'max="' . e($max) . '"' : '' ?>
               <?= isset($step) ? 'step="' . e($step) . '"' : '' ?>
               <?= isset($autocomplete) ? 'autocomplete="' . e($autocomplete) . '"' : '' ?>>
    </div>

    <?php if ($error): ?>
        <p class="text-xs text-rose-400 mt-0.5 flex items-center gap-1">
            <i class="bi bi-exclamation-circle"></i> <?= e($error) ?>
        </p>
    <?php elseif ($help): ?>
        <p class="text-xs text-white/40 mt-0.5"><?= e($help) ?></p>
    <?php endif; ?>
</div>
