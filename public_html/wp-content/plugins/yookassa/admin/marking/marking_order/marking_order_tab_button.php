<?php
/**
 * @var int $buttonId
 * @var string $iconClass
 */
?>

<td class="mark-code-column" style="text-align: center;">
    <button type="button"
            class="button yookassa-marking-button"
            id="<?php echo esc_attr($buttonId); ?>">
        <span class="yookassa-mark-code-icon <?php echo esc_attr($iconClass); ?>"></span>
    </button>
</td>
