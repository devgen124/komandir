<?php

namespace Itgalaxy\Wc\Exchange1c\Admin\PageParts;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\SettingsHelper;

class FieldSelect2
{
    public static function render($field, $name)
    {
        $currentValues = SettingsHelper::get($name, []);

        if (isset($field['options'][''])) {
            unset($field['options']['']);
        } ?>
        <div>
            <label for="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '_' . $name); ?>">
                <strong><?php echo esc_html($field['title']); ?></strong>
            </label>
            <select style="width: 100%"
                data-ui-component="select2"
                multiple
                id="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '_' . $name); ?>"
                name="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '[' . $name . ']'); ?>[]">
                <?php
                foreach ($field['options'] as $optionValue => $optionLabel) {
                    echo '<option value="'
                        . esc_attr($optionValue)
                        . '"'
                        . (in_array($optionValue, $currentValues) ? ' selected' : '')
                        . '>'
                        . esc_html($optionLabel)
                        . '</option>';
                } ?>
            </select>
            <br>
            <?php if (!empty($field['description'])) { ?>
                <p class="description">
                    <?php echo esc_html($field['description']); ?>
                </p>
            <?php } ?>
        </div>
        <?php
    }
}
