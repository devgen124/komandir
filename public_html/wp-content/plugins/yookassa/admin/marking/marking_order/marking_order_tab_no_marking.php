<?php
/**
 * @var string|null $message
 * @var string|null $icon
 * @var string|null $tooltip
 * @var string $style
 */
?>
<td class="mark_code_column" style="text-align: center; <?= $style ?>">
    <div class="yookassa-error-container">
        <?php if (isset($icon)): ?>
            <span class="yookassa-error-tooltip" <?= isset($tooltip) ? 'data-tooltip="'.esc_attr($tooltip).'"' : '' ?>>
                <span class="yookassa-error-icon"><?= $icon ?></span>
                <span class="yookassa-error-text"><?= $message ?></span>
            </span>
        <?php else: ?>
            <?= $message ?>
        <?php endif; ?>
    </div>
</td>
