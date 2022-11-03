<?php

namespace Itgalaxy\Wc\Exchange1c\Admin\PageParts;

class SectionNomenclatureExchangeConfigure
{
    public static function render()
    {
        $userList = [];

        foreach (get_users(['role' => 'administrator']) as $user) {
            $userList[$user->ID] = $user->user_login;
        }

        $section = [
            'title' => esc_html__('Product catalog (nomenclature)', 'itgalaxy-woocommerce-1c'),
            'tabs' => [
                [
                    'title' => esc_html__('Main', 'itgalaxy-woocommerce-1c'),
                    'id' => 'nomenclature-main',
                    'fields' => [
                        'exchange_post_author' => [
                            'type' => 'select',
                            'title' => esc_html__('Product / Image Owner', 'itgalaxy-woocommerce-1c'),
                            'options' => $userList,
                        ],
                        'file_limit' => [
                            'type' => 'number',
                            'title' => esc_html__('File part size:', 'itgalaxy-woocommerce-1c'),
                            'description' => esc_html__(
                                'The maximum size of the part of the exchange files transmitted from 1C (in bytes).',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'default' => 5000000,
                            'fieldsetStart' => true,
                            'legend' => esc_html__('Exchange parameters', 'itgalaxy-woocommerce-1c'),
                        ],
                        'time_limit' => [
                            'type' => 'number',
                            'title' => esc_html__('Script running time (second):', 'itgalaxy-woocommerce-1c'),
                            'description' => esc_html__(
                                'Maximum time the sync script runs (in seconds), for one step processing progress. '
                                    . 'Recommended value: 20, this is suitable for most hosts.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'default' => 20,
                        ],
                        'use_file_zip' => [
                            'type' => 'checkbox',
                            'title' => esc_html__('Exchange in the archive', 'itgalaxy-woocommerce-1c'),
                            'description' => esc_html__(
                                'If enabled, the exchange takes place through a zip archive.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetEnd' => true,
                        ],
                        'remove_missing_full_unload_products' => [
                            'type' => 'select',
                            'title' => esc_html__('Remove missing products', 'itgalaxy-woocommerce-1c'),
                            'options' => [
                                '' => esc_html__('Not chosen', 'itgalaxy-woocommerce-1c'),
                                'completely' => esc_html__('complete removal', 'itgalaxy-woocommerce-1c'),
                                'trash' => esc_html__('to trash', 'itgalaxy-woocommerce-1c'),
                            ],
                            'description' => esc_html__(
                                'If chosen, all products that are missing in the unloading will be deleted '
                                . ' (if the product is created manually and is not related to the data from the upload, '
                                . 'then they will not be affected).',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetStart' => true,
                            'legend' => esc_html__('Full unload (not only changes)', 'itgalaxy-woocommerce-1c'),
                        ],
                        'remove_missing_full_unload_product_categories' => [
                            'type' => 'checkbox',
                            'title' => esc_html__('Remove missing categories', 'itgalaxy-woocommerce-1c'),
                            'description' => esc_html__(
                                'If enabled, all categories that are missing in the unloading will be deleted '
                                . ' (if the category is created manually and is not related to the data from the upload, '
                                . 'then they will not be affected).',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetEnd' => true,
                        ],
                        'remove_marked_products_to_trash' => [
                            'type' => 'checkbox',
                            'title' => esc_html__('Remove products marked for deletion (according to the uploading data) to trash', 'itgalaxy-woocommerce-1c'),
                            'description' => esc_html__(
                                'If the product has been uploaded and, according to the unloading data, it is marked for'
                                . ' deletion, then the product will be removed to trash. By default, the product is '
                                . 'removed completely.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'restore_products_from_trash' => [
                            'type' => 'checkbox',
                            'title' => esc_html__('Restore product from trash (status `trash`)', 'itgalaxy-woocommerce-1c'),
                            'description' => esc_html__(
                                'If the product is in unloading, does not have a deletion mark and the related product '
                                . 'on the site has been removed to the trash (has status `trash`), then the product '
                                . 'will be restored from the trash and published. By default, the product in the trash '
                                . 'will simply be updated.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                    ],
                ],
                [
                    'title' => esc_html__('For products', 'itgalaxy-woocommerce-1c'),
                    'id' => 'nomeclature-products',
                    'fields' => [
                        'find_product_by_sku' => [
                            'type' => 'checkbox',
                            'title' => esc_html__('Try to find a product by SKU', 'itgalaxy-woocommerce-1c'),
                            'description' => esc_html__(
                                'If enabled, then the plugin tries to find the product by SKU, if it is not '
                                . 'found by ID from 1C. It may be useful if the site already has products and, in '
                                . 'order not to create everything again, you can make their first link by SKU.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'product_create_new_in_status_draft' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Create a new product in the status "Draft"',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, a new product will be added in the status "Draft", by default the '
                                . 'product is created in the status "Publish".',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'product_use_full_name' => [
                            'type' => 'checkbox',
                            'title' => esc_html__('Use full name', 'itgalaxy-woocommerce-1c'),
                            'description' => esc_html__(
                                'If enabled, the title of the product will be recorded not "Name" and "Full Name" '
                                . 'of the details of the products.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetStart' => true,
                            'legend' => esc_html__('Product title', 'itgalaxy-woocommerce-1c'),
                            'fieldsetEnd' => true,
                        ],
                        'write_product_description_in_excerpt' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Write the "Description" in a short description of the product.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, the product description will be written in a short description '
                                . '(post_excerpt).',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetStart' => true,
                            'legend' => esc_html__('Product description', 'itgalaxy-woocommerce-1c'),
                        ],
                        'use_html_description' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Use for the main description "Description file for the site"',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If it is included, then the description of the product will be recorded not in '
                                . 'the "Description", but in the "Description in HTML format" from the details of '
                                . 'the product, if any, while the data from the "Description" will be recorded in '
                                . 'a excerpt description of the product.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'use_separate_file_with_html_description' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Use a separate file with a description',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, the plugin will fill in product description from the first "*.html" file '
                                . 'in the upload, if the file exists. If there is a file, then data from "Описание" '
                                . 'will be written into a excerpt description.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetEnd' => true,
                        ],
                        'get_product_sku_from' => [
                            'type' => 'select',
                            'title' => esc_html__('Get product sku from:', 'itgalaxy-woocommerce-1c'),
                            'options' => [
                                'sku' => esc_html__(
                                    'SKU (data from node "Товар->Артикул")',
                                    'itgalaxy-woocommerce-1c'
                                ),
                                'requisite_code' => esc_html__(
                                    'Requisite value "Code"',
                                    'itgalaxy-woocommerce-1c'
                                ),
                                'code' => esc_html__(
                                    'Code (data from node "Товар->Код")',
                                    'itgalaxy-woocommerce-1c'
                                ),
                                'barcode' => esc_html__(
                                    'Barcode (data from node "Товар->Штрихкод")',
                                    'itgalaxy-woocommerce-1c'
                                ),
                            ],
                            'description' => esc_html__(
                                'Indicate from which value the article number should be written.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'product_weight_use_factor' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Apply factor to original value',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'It can be useful if the unit of weight on the site and in 1C is different. '
                                . 'By applying a factor to the original value, you can convert the weight value from '
                                . 'the unloading to the unit that is used on the site.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetStart' => true,
                            'legend' => esc_html__('Product weight', 'itgalaxy-woocommerce-1c'),
                        ],
                        'product_weight_factor_value' => [
                            'type' => 'number',
                            'step' => '0.001',
                            'title' => esc_html__('Factor value', 'itgalaxy-woocommerce-1c'),
                            'description' => esc_html__(
                                'For example, on the site, grams are used, and in 1C, the weight is indicated in '
                                . 'kilograms. To get the value in grams, you need to use a factor of 1000. '
                                . 'Formula: initial value * factor = value.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetEnd' => true,
                        ],
                    ],
                ],
                [
                    'title' => esc_html__('For Attributes', 'itgalaxy-woocommerce-1c'),
                    'id' => 'nomenclature-attributes',
                    'fields' => [
                        'find_exists_attribute_by_name' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'An attempt to search for basic (generated from properties) attributes by name',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, then the plugin tries to find the attribute by name, if it is not '
                                . 'found by ID from 1C. It may be useful if the site already has attributes and, in '
                                . 'order not to create everything again, you can make their first link by name.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'find_exists_attribute_value_by_name' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'An attempt to search for basic (generated from properties) attribute values by name',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, then the plugin tries to find the attribute value by name, if it is not '
                                . 'found by ID from 1C. It may be useful if the site already has attribute values and, in '
                                . 'order not to create everything again, you can make their first link by name.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'merge_properties_with_same_name' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Merge properties with the same name',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, properties (both basic and for offers) with the same name will be merged'
                                . 'into one final attribute. This will be useful when nomenclature categories are '
                                . 'applied and, as a result, several properties are obtained in the unloading and on'
                                . 'the basis of them separate attributes are created, which is true in structure, '
                                . 'but in reality it interferes and looks like duplicates.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'property_use_separator_in_value' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Apply separator to values',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, when processing product property values, the value can be split into '
                                . 'several using the separator specified in the field below. Can be applied only to '
                                . 'simple (not "Справочник") basic properties of the product. It can be useful, since '
                                . 'most configurations do not allow you to specify multiple values for 1 property in 1 '
                                . 'product, as a result, you can specify several values, like one with a separator, '
                                . 'and as a result, the product will not have 1 value, but several on the site.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetStart' => true,
                            'legend' => esc_html__('Separator in values', 'itgalaxy-woocommerce-1c'),
                        ],
                        'property_separator_value' => [
                            'type' => 'text',
                            'title' => esc_html__('Separator value', 'itgalaxy-woocommerce-1c'),
                            'description' => esc_html__(
                                'For example, you specified "value 1||value 2||value 3" in the property, then to get '
                                . '3 values, specify "||" to separate.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetEnd' => true,
                        ],
                        'attribute_create_enable_public' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Enable archives when creating attributes',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, the checkbox "Enable archives?" will be enabled for the created attributes.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'attribute_variable_enable_visibility' => [
                            'type' => 'checkbox',
                            'title' => \esc_html__(
                                'Enable visibility for variable product attributes',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => \esc_html__(
                                'If enabled, the "Visible on the product page" checkbox will be enabled for attributes '
                                . 'that are used for variations in the product. By default, only "Used for variations" '
                                . 'is checked for such attributes.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                    ],
                ],
                [
                    'title' => esc_html__('For Images', 'itgalaxy-woocommerce-1c'),
                    'id' => 'nomenclature-images',
                    'fields' => [
                        'write_product_name_to_attachment_title' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Write the name of the product in the title of the media file',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, then in the title of the added media files (images) will be written the name of '
                                . 'the product to which the picture belongs. Please note that the title will be write '
                                . 'only for new or changed pictures.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'write_product_name_to_attachment_attribute_alt' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Write the name of the product in the "Attribute Alt" of the media file',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, the name of the product to which the picture belongs will be write in '
                                . 'the metadata `_wp_attachment_image_alt` added media files (images). Please note '
                                . 'that the metadata will be write only for new or changed pictures.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'more_check_image_changed' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Extra control over image changes',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'Turn this option on if you notice that changing the image in 1C does not lead '
                                . 'to a change on the site. This can occur in a number of configurations in '
                                . 'which the file name does not change when the image is changed.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                    ],
                ],
                [
                    'title' => esc_html__('For Categories', 'itgalaxy-woocommerce-1c'),
                    'id' => 'nomenclature-categories',
                    'fields' => [
                        'find_product_cat_term_by_name' => [
                            'type' => 'checkbox',
                            'title' => esc_html__('Try to find a category by name', 'itgalaxy-woocommerce-1c'),
                            'description' => esc_html__(
                                'If enabled, then the plugin tries to find the category by name, if it is not '
                                . 'found by ID from 1C. It may be useful if the site already has categories and, in '
                                . 'order not to create everything again, you can make their first link by name.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'set_category_thumbnail_by_product' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Set category thumbnails automatically',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, the category will be assigned a picture for the first product with an '
                                . 'image when processing the upload, if the product has a direct link to the category, '
                                . 'that is, it is linked directly.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                    ],
                ],
                [
                    'title' => esc_html__('For offers', 'itgalaxy-woocommerce-1c'),
                    'id' => 'nomeclature-offers',
                    'fields' => [
                        'products_stock_null_rule' => [
                            'type' => 'select',
                            'title' => esc_html__('Products with a stock <= 0:', 'itgalaxy-woocommerce-1c'),
                            'options' => [
                                '0' => esc_html__(
                                    'Hide (not available for viewing and ordering)',
                                    'itgalaxy-woocommerce-1c'
                                ),
                                '1' => esc_html__(
                                    'Do not hide and give the opportunity to put in the basket',
                                    'itgalaxy-woocommerce-1c'
                                ),
                                'not_hide_and_put_basket_with_disable_manage_stock_and_stock_status_onbackorder' => esc_html__(
                                    'Do not hide and give the opportunity to put in the basket (Manage stock - '
                                    . 'disable, Stock status - On back order)',
                                    'itgalaxy-woocommerce-1c'
                                ),
                                '2' => esc_html__(
                                    'Do not hide, but do not give the opportunity to put in the basket',
                                    'itgalaxy-woocommerce-1c'
                                ),
                                'with_negative_not_hide_and_put_basket_with_zero_hide_and_not_put_basket' => esc_html__(
                                    'Do not hide with a negative stock and give an opportunity to put in a basket, '
                                    . 'with a zero stock hide and do not give an opportunity to put in a basket.',
                                    'itgalaxy-woocommerce-1c'
                                ),
                            ],
                            'description' => esc_html__(
                                'Only products with a non-empty price can be opened.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetStart' => true,
                            'legend' => esc_html__('Stock actions', 'itgalaxy-woocommerce-1c'),
                        ],
                        'products_onbackorder_stock_positive_rule' => [
                            'type' => 'select',
                            'title' => esc_html__('Products with a stock > 0 (Allow backorders?):', 'itgalaxy-woocommerce-1c'),
                            'options' => [
                                'no' => esc_html__(
                                    'Do not allow',
                                    'itgalaxy-woocommerce-1c'
                                ),
                                'notify' => esc_html__(
                                    'Allow, but notify customer',
                                    'itgalaxy-woocommerce-1c'
                                ),
                                'yes' => esc_html__(
                                    'Allow',
                                    'itgalaxy-woocommerce-1c'
                                ),
                            ],
                            'fieldsetEnd' => true,
                        ],
                        'offers_delete_variation_if_offer_marked_deletion' => [
                            'type' => 'checkbox',
                            'title' => esc_html__('Remove variation if the variable offer is marked for removal', 'itgalaxy-woocommerce-1c'),
                            'description' => esc_html__(
                                'If enabled, when an variable offer related to variation is received marked for deletion '
                                . '(that is, a characteristic was marked for deletion), the variation will be deleted. '
                                . 'By default, variation is only disabled.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                    ],
                ],
                [
                    'title' => esc_html__('Skipping / excluding data', 'itgalaxy-woocommerce-1c'),
                    'id' => 'nomenclature-skipping-data',
                    'fields' => [
                        'skip_products_without_photo' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Skip products without photo',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, then products without photos will not be added to the site.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'skip_post_content_excerpt' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Skip product description',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, description and except will not be writed or modified.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetStart' => true,
                            'legend' => esc_html__('Main product data', 'itgalaxy-woocommerce-1c'),
                        ],
                        'skip_post_title' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Do not update product title',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, the product title will be writed when the product is created and '
                                . 'will no longer be changed according to the upload data.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'skip_product_weight' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Skip product weight',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, weight will not be writed or modified.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'skip_product_sizes' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Skip product sizes (length, width and height)',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, sizes will not be writed or modified.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'skip_post_images' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Do not update product images',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, the product images will be writed when the product is created '
                                . '(if there is) and will no longer be changed according to the upload data.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'skip_post_attributes' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Do not update product attributes',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, the product attributes will be writed when the product is created and '
                                . 'will no longer be changed according to the upload data.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'skip_product_manufacturer' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Skip product manufacturer',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, then data from "Товар->Изготовитель" will not be processed.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'skip_product_country' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Skip product country',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, then data from "Товар->Страна" will not be processed.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetEnd' => true,
                        ],
                        'skip_product_prices' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Skip product prices',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, prices will not be writed or modified.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetStart' => true,
                            'legend' => esc_html__('Offers data (price, stock, offer characteristics)', 'itgalaxy-woocommerce-1c'),
                        ],
                        'skip_product_stocks' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Skip product stocks',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, stocks will not be writed or modified.',
                                'itgalaxy-woocommerce-1c'
                            ),
                        ],
                        'skip_update_set_attribute_for_variations' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Do not change product attribute set for variations',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, then the set of attributes that are used for the variations will be writed when '
                                . 'the product is created and will no longer be changed according to the upload data.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetEnd' => true,
                        ],
                        'skip_product_cat_name' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Do not update category name',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, the category name will be writed when the category is created and '
                                . 'will no longer be changed according to the upload data.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetStart' => true,
                            'legend' => esc_html__('Categories', 'itgalaxy-woocommerce-1c'),
                        ],
                        'skip_categories' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Do not process groups',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, then categories on the site will not be created / updated based on '
                                . 'data about groups from 1C, and the category will not be assigned / changed to '
                                . 'products.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetEnd' => true,
                        ],
                    ],
                ],
            ],
        ];

        Section::render(
            apply_filters('itglx_wc1c_admin_section_nomenclature_fields', $section)
        );
    }
}
