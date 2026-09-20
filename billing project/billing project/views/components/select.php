<?php
/**
 * FinPilot Reusable Component: Select Dropdown
 * 
 * Props:
 * @var string $name           Select name attribute
 * @var string|null $label     Field label
 * @var array $options         Array of ['value' => '...', 'label' => '...'] or key => value
 * @var mixed $selected        Selected value
 * @var string|null $placeholder Empty state placeholder
 * @var bool $required         Required attribute
 * @var string|null $id        Select element ID
 * @var string|null $error     Error message string
 * @var string $class          Additional CSS classes
 */

$name = $name ?? 'select_' . uniqid();
$id = $id ?? $name;
$options = $options ?? [];
$selected = $selected ?? null;
$placeholder = $placeholder ?? null;
$required = $required ?? false;
$error = $error ?? null;
$class = $class ?? '';
?>
<div class="fp-input-group">
    <?php if (!empty($label)): ?>
        <label for="<?= e($id) ?>" class="fp-label">
            <?= e($label) ?>
            <?php if ($required): ?><span class="text-rose-400">*</span><?php endif; ?>
        </label>
    <?php endif; ?>

    <select name="<?= e($name) ?>"
            id="<?= e($id) ?>"
            <?= $required ? 'required' : '' ?>
            class="fp-select <?= $error ? '!border-rose-500/80' : '' ?> <?= e($class) ?>">
        <?php if ($placeholder): ?>
            <option value="" <?= ($selected === null || $selected === '') ? 'selected' : '' ?> disabled>
                <?= e($placeholder) ?>
            </option>
        <?php endif; ?>

        <?php foreach ($options as $key => $opt): ?>
            <?php
            $optVal = is_array($opt) ? ($opt['value'] ?? $key) : $key;
            $optLabel = is_array($opt) ? ($opt['label'] ?? $optVal) : $opt;
            $isSelected = (string)$optVal === (string)$selected;
            ?>
            <option value="<?= e((string)$optVal) ?>" <?= $isSelected ? 'selected' : '' ?>>
                <?= e((string)$optLabel) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if ($error): ?>
        <p class="text-xs text-rose-400 mt-0.5"><?= e($error) ?></p>
    <?php endif; ?>
</div>
