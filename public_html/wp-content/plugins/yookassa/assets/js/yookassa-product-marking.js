(function ($) {
    'use strict';

    // Конфигурация селекторов для элементов управления
    const SELECTORS = {
        container: '#yookassa_marking_product_data',
        categorySelect: 'select',
        optionsGroup: '.options_group > div',
        measureField: '.options_group > div:first-child',
        measureSelect: 'select',
        denominatorBlock: '.options_group > div:nth-child(2)',
        denominatorField: 'input'
    };

    // Кэширование DOM-элементов
    const elements = {
        container: null,
        categorySelect: null,
        optionsGroup: null,
        measureField: null,
        measureSelect: null,
        denominatorBlock: null,
        denominatorField: null
    };

    /**
     * Инициализирует модуль, находит DOM-элементы и навешивает обработчики событий
     */
    function init() {
        cacheElements();
        bindEvents();
        updateVisibility(); // Первоначальное обновление видимости
    }

    /**
     * Кэширует часто используемые DOM-элементы для оптимизации производительности
     */
    function cacheElements() {
        elements.container = $(SELECTORS.container);
        elements.categorySelect = elements.container.find(SELECTORS.categorySelect);
        elements.optionsGroup = elements.container.find(SELECTORS.optionsGroup);
        elements.measureField = elements.container.find(SELECTORS.measureField);
        elements.measureSelect = elements.measureField.find(SELECTORS.measureSelect);
        elements.denominatorBlock = elements.container.find(SELECTORS.denominatorBlock);
        elements.denominatorField = elements.denominatorBlock.find(SELECTORS.denominatorField);
    }

    /**
     * Навешивает обработчики событий на элементы управления
     */
    function bindEvents() {
        elements.categorySelect.on('change', updateVisibility);
        elements.measureSelect.on('change', updateVisibility);
    }

    /**
     * Обновляет видимость полей в зависимости от выбранных значений
     * - Показывает/скрывает группу полей, если выбрана категория
     * - Показывает/скрывает поле знаменателя, если выбрана штучная мера
     */
    function updateVisibility() {
        const isCategorySelected = elements.categorySelect.val() !== '';
        const isPieceMeasure = elements.measureSelect.val() === 'piece';
        const isDenominatorRequired = isPieceMeasure && isCategorySelected;

        // Показываем/скрываем всю группу полей
        elements.optionsGroup.toggle(isCategorySelected);

        // Управляем видимостью блока знаменателя
        elements.denominatorBlock.toggle(isDenominatorRequired);
        elements.denominatorField.prop('required', isDenominatorRequired);
    }

    // Инициализация модуля при загрузке
    $(document).ready(init);

})(jQuery);
