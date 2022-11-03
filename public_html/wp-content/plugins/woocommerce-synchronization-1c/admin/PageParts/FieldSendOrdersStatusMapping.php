<?php

namespace Itgalaxy\Wc\Exchange1c\Admin\PageParts;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\SettingsHelper;

class FieldSendOrdersStatusMapping
{
    public static function render($field, $name)
    {
        echo '<div><h4>' . esc_html($field['title']) . '</h4>'
            . '<table>';

        $mappingStatuses = SettingsHelper::get($name, []);

        foreach (\wc_get_order_statuses() as $status => $label) {
            $value = str_replace('wc-', '', $status); ?>
            <tr>
                <th>
                    <?php echo esc_html($label); ?>
                </th>
                <td>
                    <input type="text"
                        id="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '_' . $name . '_' . $value); ?>"
                        name="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '[' . $name . '][' . $value . ']'); ?>"
                        value="<?php echo isset($mappingStatuses[$value]) ? esc_attr($mappingStatuses[$value]) : ''; ?>">
                </td>
                <td>
                    <small class="description">
                    <?php
                    echo esc_html__('If not specified, then the default value will be used - ', 'itgalaxy-woocommerce-1c')
                        . '<strong>' . esc_html($value) . '</strong>'; ?>
                    </small>
                </td>
            </tr>
            <?php
        }

        echo '</table></div>';
    }
}
