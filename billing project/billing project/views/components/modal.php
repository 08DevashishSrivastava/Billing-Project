<?php
/**
 * FinPilot Reusable Component: Modal Dialog
 * 
 * Props:
 * @var string $id             Modal DOM ID (required)
 * @var string $title          Modal title
 * @var string $slot           Modal body content
 * @var string|null $footer    Optional modal footer actions
 * @var string $size           'sm' | 'md' | 'lg' (default: 'md')
 * @var bool $hidden           Whether modal starts hidden (default: true)
 */

$id = $id ?? 'modal_' . uniqid();
$title = $title ?? 'Dialog';
$slot = $slot ?? '';
$footer = $footer ?? null;
$size = $size ?? 'md';
$hidden = $hidden ?? true;

$maxWidth = match($size) {
    'sm' => 'max-w-sm',
    'lg' => 'max-w-2xl',
    default => 'max-w-md',
};
?>
<div id="<?= e($id) ?>"
     class="<?= $hidden ? 'hidden' : '' ?> fp-modal-backdrop"
     onclick="if(event.target === this) { this.classList.add('hidden'); }">
    <div class="fp-modal-card <?= $maxWidth ?> w-full mx-4" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-white/10">
            <h3 class="fp-font-display text-base font-semibold text-white">
                <?= e($title) ?>
            </h3>
            <button type="button"
                    onclick="document.getElementById('<?= e($id) ?>').classList.add('hidden')"
                    class="p-1 rounded-lg text-white/40 hover:text-white hover:bg-white/10 transition">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <!-- Body Content -->
        <div class="p-6">
            <?= $slot ?>
        </div>

        <!-- Optional Footer -->
        <?php if ($footer): ?>
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-white/10 bg-white/[0.02]">
                <?= $footer ?>
            </div>
        <?php endif; ?>
    </div>
</div>
