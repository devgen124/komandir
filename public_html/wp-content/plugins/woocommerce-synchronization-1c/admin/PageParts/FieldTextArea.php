<?php

namespace Itgalaxy\Wc\Exchange1c\Admin\PageParts;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\SettingsHelper;

class FieldTextArea
{
    public static function render($field, $name)
    {
        ?>
        <div>
            <label for="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '_' . $name); ?>">
                <strong><?php echo esc_html($field['title']); ?></strong>
            </label>
            <textarea class="large-text"
                id="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '_' . $name); ?>"
                name="<?php
                echo esc_attr(Bootstrap::OPTIONS_KEY . '[' . $name . ']'); ?>"><?php echo SettingsHelper::get($name) ? esc_attr(SettingsHelper::get($name)) : ''; ?></textarea>
            <?php if (!empty($field['description'])) { ?>
                <p class="description">
                    <?php echo esc_html($field['description']); ?>
                </p>
            <?php } ?>
        </div>
        <?php
    }
}
