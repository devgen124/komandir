<?php

use Automattic\WooCommerce\Internal\Utilities\Users;
use YooKassa\Model\Receipt\ReceiptItemMeasure;

/**
 * Класс по добавлению маркировки в товарах
 */
class YooKassaMarkingProduct
{
    const BEER = 'beer';
    const DAIRY = 'dairy';
    const BEVERAGES = 'beverages';
    const WATER = 'water';
    const MEDICINES = 'medicines';
    const TOBACCO = 'tobacco';
    const LIGHT_INDUSTRY = 'light_industry';
    const FOOTWEAR = 'footwear';
    const FUR = 'fur';
    const NONALCOHOLIC_BEER = 'nonalcoholic_beer';
    const OIL = 'oil';
    const CANNED_FOOD = 'canned_food';
    const GROCERY = 'grocery';
    const AUTOFLUIDS = 'autofluids';
    const MEDICAL_DEVICES = 'medical_devices';
    const PERFUMES = 'perfumes';
    const TYRES = 'tyres';
    const PHOTO_CAMERAS_AND_FLASHBULBS = 'photo_cameras_and_flashbulbs';
    const DIETARYSUP = 'dietarysup';
    const ANTISEPTIC = 'antiseptic';
    const WHEELCHAIRS = 'wheelchairs';
    const CAVIAR = 'caviar';
    const BICYCLES = 'bicycles';
    const PET_FOOD = 'pet_food';
    const VETERINARY_PRODUCTS = 'veterinary_products';
    const TECHNICAL_REHABILITATION = 'technical_rehabilitation';
    const COSMETICS = 'cosmetics';

    /** @var string Категория маркировки */
    const CATEGORY_KEY = '_yookassa_marking_category';

    /** @var string Мера количества предмета расчета */
    const MEASURE_KEY = '_yookassa_marking_measure';

    /** @var string Знаменатель — общее количество товаров в потребительской упаковке */
    const DENOMINATOR_KEY = '_yookassa_marking_denominator';

    /** @var string Код товара (gs_1m, fur и т.д.) */
    const MARK_CODE_INFO_KEY = '_yookassa_mark_code_info';

    /** @var string Идентификатор федерального органа исполнительной власти */
    const FEDERAL_ID_KEY = '_yookassa_federal_id';

    /** @var string Дата документа основания */
    const DOCUMENT_DATE_KEY = '_yookassa_document_date';

    /** @var string Номер нормативного акта федерального органа исполнительной власти */
    const DOCUMENT_NUMBER_KEY = '_yookassa_document_number';

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * @param string $plugin_name
     * @param string $version
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }


    /**
     * Добавление вкладки
     *
     * @param array $tabs
     * @return array
     */
    public function addMarkingProductTab($tabs)
    {
        $tabs['marking_product'] = array(
            'label'    => __('Маркировка', 'yookassa'),
            'target'   => 'yookassa_marking_product_data',
            'priority' => 70,
        );
        return $tabs;
    }

    /**
     * Добавление содержимого вкладки
     *
     * @return void
     */
    public function markingProductTabContent()
    {
        $this->enqueue_custom_product_marking_script();
        $productId = isset($_GET['post']) ? absint($_GET['post']) : 0;
        $product = $productId ? wc_get_product($productId) : null;
        $this->render(
            'marking/marking_product/marking_product_tab.php',
            array(
                'product' => $product,
                'markingCategories' => $this->getMarkingCategories(),
                'markingMeasure' => $this->getMeasure()
            )
        );
    }

    /**
     * Сохранение данных
     *
     * @param int $productId
     * @return void
     * @throws Exception
     */
    public function saveMarkingProductFields($productId)
    {
        try {
            YooKassaLogger::info(sprintf(
                'Starting to save marking fields for product ID: %d',
                $productId
            ));

            $product = wc_get_product($productId);
            $category = isset($_POST[self::CATEGORY_KEY]) ? sanitize_key($_POST[self::CATEGORY_KEY]) : '';
            if (empty($category)) {
                YooKassaLogger::info(sprintf(
                    'Empty category received, deleting all marking metadata for product ID: %d',
                    $productId
                ));

                $product->delete_meta_data(self::CATEGORY_KEY);
                $product->delete_meta_data(self::MEASURE_KEY);
                $product->delete_meta_data(self::DENOMINATOR_KEY);
                $product->delete_meta_data(self::MARK_CODE_INFO_KEY);
                $product->save();

                YooKassaLogger::info(sprintf(
                    'Successfully cleared all marking metadata for product ID: %d',
                    $productId
                ));
                return;
            }

            $measure = isset($_POST[self::MEASURE_KEY]) ? sanitize_key($_POST[self::MEASURE_KEY]) : ReceiptItemMeasure::PIECE;
            $denominator = isset($_POST[self::DENOMINATOR_KEY]) ? absint($_POST[self::DENOMINATOR_KEY]) : 1;
            $markCodeInfo = $category === self::FUR ? 'fur' : 'gs_1m';

            YooKassaLogger::info(sprintf(
                'Saving marking fields for product ID: %d with values - Category: "%s", Measure: "%s", Denominator: %d, MarkCodeInfo: "%s"',
                $productId,
                $category,
                $measure,
                $denominator,
                $markCodeInfo
            ));

            $product->update_meta_data(self::MARK_CODE_INFO_KEY, $markCodeInfo);
            $product->update_meta_data(self::CATEGORY_KEY, $category);
            $product->update_meta_data(self::MEASURE_KEY, $measure);

            if ($measure !== ReceiptItemMeasure::PIECE) {
                YooKassaLogger::info(sprintf(
                    'Non-piece measure detected ("%s"), deleting denominator for product ID: %d',
                    $measure,
                    $productId
                ));
                $product->delete_meta_data(self::DENOMINATOR_KEY);
            } else {
                YooKassaLogger::info(sprintf(
                    'Piece measure detected, saving denominator: %d for product ID: %d',
                    $denominator,
                    $productId
                ));
                $product->update_meta_data(self::DENOMINATOR_KEY, $denominator);
            }

            $product->save();
            YooKassaLogger::info(sprintf(
                'Successfully saved marking fields for product ID: %d',
                $productId
            ));

        } catch (Exception $e) {
            YooKassaLogger::error(sprintf(
                'Error saving marking fields for product ID: %d. Error: %s. Trace: %s',
                $productId,
                $e->getMessage(),
                $e->getTraceAsString()
            ));
        }
    }

    /**
     * Отрисовывает новый таб с маркировкой
     *
     * @param string $viewPath
     * @param array $args
     * @return void
     */
    private function render($viewPath, $args)
    {
        extract($args);
        include(plugin_dir_path(__FILE__) . $viewPath);
    }

    /**
     * Добавляет скрипт на страницу
     * с созданием или редактированием товара
     *
     * @return void
     */
    private function enqueue_custom_product_marking_script()
    {
        if (!$this->isProductPage()) {
            return;
        }

        wp_register_script(
            $this->plugin_name . '-product-marking',
            YooKassa::$pluginUrl . 'assets/js/yookassa-product-marking.js',
            array('jquery'),
            $this->version,
            true
        );
        wp_enqueue_script( $this->plugin_name . '-product-marking' );
    }

    /**
     * Выполняет проверку,
     * что открыта страница товара в админке
     *
     * @return bool
     */
    private function isProductPage()
    {
        if (!is_admin()) {
            return false;
        }

        global $pagenow;

        $current_screen = get_current_screen();

        return (
            $current_screen
            && $current_screen->post_type === 'product'
            && ($pagenow === 'post.php' || $pagenow === 'post-new.php')
        );
    }

    /**
     * Возвращает сопоставленные данные
     * по категории маркировки и необходимым данным
     *
     * @param string $category
     * @return array|string[]
     */
    private function getPaymentSubjectIndustryDetails($category)
    {
        switch ($category) {
            case self::BEER:
            case self::DAIRY:
            case self::WATER:
            case self::TOBACCO:
            case self::LIGHT_INDUSTRY:
            case self::FOOTWEAR:
            case self::TYRES:
            case self::PHOTO_CAMERAS_AND_FLASHBULBS:
            case self::DIETARYSUP:
            case self::COSMETICS:
            case self::MEDICAL_DEVICES:
            case self::ANTISEPTIC:
            case self::WHEELCHAIRS:
            case self::BEVERAGES:
                return [
                    self::FEDERAL_ID_KEY => "030",
                    self::DOCUMENT_DATE_KEY => "2023-11-21",
                    self::DOCUMENT_NUMBER_KEY => "1944",
                    self::MARK_CODE_INFO_KEY => "gs_1m"
                ];
            case self::MEDICINES:
                return [
                    self::FEDERAL_ID_KEY => "020",
                    self::DOCUMENT_DATE_KEY => "2018-12-14",
                    self::DOCUMENT_NUMBER_KEY => "1556",
                    self::MARK_CODE_INFO_KEY => "gs_1m"
                ];
            case self::FUR:
                return [
                    self::FEDERAL_ID_KEY => null,
                    self::DOCUMENT_DATE_KEY => null,
                    self::DOCUMENT_NUMBER_KEY => null,
                    self::MARK_CODE_INFO_KEY => "fur"
                ];
            default:
                return [
                    self::FEDERAL_ID_KEY => null,
                    self::DOCUMENT_DATE_KEY => null,
                    self::DOCUMENT_NUMBER_KEY => null,
                    self::MARK_CODE_INFO_KEY => "gs_1m"
                ];
        }
    }

    /**
     * Возвращает список категорий для маркировки
     *
     * @return array
     */
    private function getMarkingCategories()
    {
        return array(
            '' => '-',
            self::BEER => __('Пиво и слабоалкогольные напитки', 'yookassa'),
            self::DAIRY => __('Молочная продукция', 'yookassa'),
            self::BEVERAGES => __('Безалкогольные напитки', 'yookassa'),
            self::WATER => __('Упакованная вода', 'yookassa'),
            self::MEDICINES => __('Лекарства', 'yookassa'),
            self::TOBACCO => __('Табак', 'yookassa'),
            self::LIGHT_INDUSTRY => __('Товары легкой промышленности', 'yookassa'),
            self::FOOTWEAR => __('Обувь', 'yookassa'),
            self::FUR => __('Шубы', 'yookassa'),
            self::NONALCOHOLIC_BEER => __('Безалкогольное пиво', 'yookassa'),
            self::OIL => __('Растительные масла', 'yookassa'),
            self::CANNED_FOOD => __('Консервированные продукты', 'yookassa'),
            self::GROCERY => __('Бакалея', 'yookassa'),
            self::AUTOFLUIDS => __('Моторные масла', 'yookassa'),
            self::MEDICAL_DEVICES => __('Медицинские изделия', 'yookassa'),
            self::PERFUMES => __('Духи и туалетная вода', 'yookassa'),
            self::TYRES => __('Шины и покрышки', 'yookassa'),
            self::PHOTO_CAMERAS_AND_FLASHBULBS => __('Фотоаппараты и лампы-вспышки', 'yookassa'),
            self::DIETARYSUP => __('Биологически активные добавки к пище', 'yookassa'),
            self::ANTISEPTIC => __('Антисептики и дезинфицирующие средства', 'yookassa'),
            self::WHEELCHAIRS => __('Кресла-коляски', 'yookassa'),
            self::CAVIAR => __('Морепродукты (икра)', 'yookassa'),
            self::BICYCLES => __('Велосипеды', 'yookassa'),
            self::PET_FOOD => __('Корма для животных', 'yookassa'),
            self::VETERINARY_PRODUCTS => __('Лекарственные препараты для ветеринарного применения', 'yookassa'),
            self::TECHNICAL_REHABILITATION => __('Технические средства реабилитации', 'yookassa'),
            self::COSMETICS => __('Парфюмерно-косметическая продукция и бытовая химия', 'yookassa'),
        );
    }

    /**
     * Возвращает список меры количества предмета расчета
     *
     * @return array
     */
    private function getMeasure()
    {
        return array(
            ReceiptItemMeasure::PIECE => __('Штука, единица товара', 'yookassa'),
            ReceiptItemMeasure::GRAM => __('Грамм', 'yookassa'),
            ReceiptItemMeasure::KILOGRAM => __('Килограмм', 'yookassa'),
            ReceiptItemMeasure::TON => __('Тонна', 'yookassa'),
            ReceiptItemMeasure::CENTIMETER => __('Сантиметр', 'yookassa'),
            ReceiptItemMeasure::DECIMETER => __('Дециметр', 'yookassa'),
            ReceiptItemMeasure::METER => __('Метр', 'yookassa'),
            ReceiptItemMeasure::SQUARE_CENTIMETER => __('Квадратный сантиметр', 'yookassa'),
            ReceiptItemMeasure::SQUARE_DECIMETER => __('Квадратный дециметр', 'yookassa'),
            ReceiptItemMeasure::SQUARE_METER => __('Квадратный метр', 'yookassa'),
            ReceiptItemMeasure::MILLILITER => __('Миллилитр', 'yookassa'),
            ReceiptItemMeasure::LITER => __('Литр', 'yookassa'),
            ReceiptItemMeasure::CUBIC_METER => __('Кубический метр', 'yookassa'),
            ReceiptItemMeasure::KILOWATT_HOUR => __('Килловат-час', 'yookassa'),
            ReceiptItemMeasure::GIGACALORIE => __('Гигакалория', 'yookassa'),
            ReceiptItemMeasure::DAY => __('Сутки', 'yookassa'),
            ReceiptItemMeasure::HOUR => __('Час', 'yookassa'),
            ReceiptItemMeasure::MINUTE => __('Минута', 'yookassa'),
            ReceiptItemMeasure::SECOND => __('Секунда', 'yookassa'),
            ReceiptItemMeasure::KILOBYTE => __('Килобайт', 'yookassa'),
            ReceiptItemMeasure::MEGABYTE => __('Мегабайт', 'yookassa'),
            ReceiptItemMeasure::GIGABYTE => __('Гигабайт', 'yookassa'),
            ReceiptItemMeasure::TERABYTE => __('Терабайт', 'yookassa'),
            ReceiptItemMeasure::ANOTHER => __('Другое', 'yookassa')
        );
    }
}
