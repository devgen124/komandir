<?php

namespace Itgalaxy\Wc\Exchange1c\Admin\PageParts;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\SettingsHelper;

class FieldCheckbox
{
    public static function render($field, $name)
    {
        ?>
        <div>
            <label>
                <input type="checkbox"
                    id="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '_' . $name); ?>"
                    name="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '[' . $name . ']'); ?>"
                    <?php echo !SettingsHelper::isEmpty($name) ? 'checked' : ''; ?>
                    value="1">
                <strong><?php echo esc_html($field['title']); ?></strong>
            </label>
            <?php if (!empty($field['description'])) { ?>
                <p class="description">
                    <?php echo esc_html($field['description']); ?>
                </p>
            <?php } ?>
            <?php
            if (!empty($field['content'])) {
                echo '<hr>' . wp_kses_post($field['content']);
            } ?>
        </div>
        <?php
    }
}
