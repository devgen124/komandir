<?php
/**
 * @var WC_Product $product
 * @var array $markingCategories
 * @var array $markingMeasure
 */

?>

<style>
    #woocommerce-product-data ul.wc-tabs li.marking_product_options.marking_product_tab a::before {
        content: "\f318";
    }
</style>

<div id="yookassa_marking_product_data" class="panel woocommerce_options_panel hidden">
    <div class="marking_product_field_category_group">
        <?php
        woocommerce_wp_select(array(
                'id'          => YooKassaMarkingProduct::CATEGORY_KEY,
                'options'     => $markingCategories,
                'label'       => __('Категория товара', 'yookassa'),
                'desc_tip'    => 'true',
                'size'        => '10',
                'name'        => YooKassaMarkingProduct::CATEGORY_KEY,
                'value'       => $product ? $product->get_meta(YooKassaMarkingProduct::CATEGORY_KEY) : '',
        ));
        ?>
    </div>

    <div class="options_group">
        <div class="yookassa_marking_measure_field">
            <p class="form-field <?= YooKassaMarkingProduct::MEASURE_KEY ?>">
                <label for="<?= YooKassaMarkingProduct::MEASURE_KEY ?>">
                    <?= __('Единица измерения', 'yookassa') ?>
                </label>
                <select
                        class="select short"
                        name="<?= YooKassaMarkingProduct::MEASURE_KEY ?>"
                        id="<?= YooKassaMarkingProduct::MEASURE_KEY ?>"
                >
                    <?php
                        $selectedMeasure = $product ? $product->get_meta(YooKassaMarkingProduct::MEASURE_KEY) : '';
                        foreach ($markingMeasure as $key => $unit) {
                            echo '<option value="' . esc_attr($key) . '" '
                                    . selected($selectedMeasure, $key, false) . '>'
                                    . __($unit, 'yookassa')
                                    . '</option>';
                        }
                    ?>
                </select>
            </p>
        </div>
        <div class="yookassa_marking_denominator_field">
            <p class="form-field <?= YooKassaMarkingProduct::DENOMINATOR_KEY ?>">
                <label for="<?= YooKassaMarkingProduct::DENOMINATOR_KEY ?>">
                    <?= __('Количество в упаковке', 'yookassa') ?>
                </label>
                <input
                    placeholder="Введите число"
                    class="input-text wc_input_decimal"
                    type="number"
                    size="5"
                    name="<?= YooKassaMarkingProduct::DENOMINATOR_KEY ?>"
                    id="<?= YooKassaMarkingProduct::DENOMINATOR_KEY ?>"
                    value="<?= $product ? esc_attr($product->get_meta(YooKassaMarkingProduct::DENOMINATOR_KEY)) : ''; ?>"
                    min="1"
                    step="any"
                />
            </p>
        </div>
    </div>
</div>
