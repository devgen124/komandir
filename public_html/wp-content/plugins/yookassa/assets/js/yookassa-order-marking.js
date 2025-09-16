(function ($) {
    'use strict';

    // Конфигурация селекторов элементов
    const SELECTORS = {
        popup: '#yookassa-marking-popup-overlay',
        title: '#yookassa-marking-popup-title',
        fieldsContainer: '#yookassa-marking-popup-fields',
        form: '#yookassa-marking-popup-form',
        footer: '#yookassa-marking-popup-footer',
        preloader: '.yookassa-preloader',
        btnSave: '#yookassa-marking-save-btn',
        btnClear: '#yookassa-marking-clear-btn',
        messageContainer: '#yookassa-marking-popup-message',
        warningContainer: '#yookassa-marking-popup-warning',
        btnClose: '#yookassa-marking-close-btn'
    };

    // Коды клавиш
    const KEY_CODES = {
        ENTER: 'Enter',
        TAB: 'Tab'
    };

    // Состояние модуля
    const state = {
        markingData: {}, // Данные маркировки по ID товаров
        validateData: {}, // Правила валидации полей
        originalValues: {}, // Оригинальные значения полей
        invalidFields: [] // Поля с ошибками валидации
    };

    // Кэш DOM-элементов
    const elements = {};

    /**
     * Инициализирует модуль - кэширует элементы и навешивает обработчики событий.
     * Вызывается при загрузке документа.
     * @example
     * // Автоматически вызывается при готовности документа
     * init();
     */
    function init() {
        cacheElements();
        bindEvents();
    }

    /**
     * Кэширует часто используемые DOM-элементы для быстрого доступа.
     * Сохраняет элементы в объект elements по ключам из SELECTORS.
     * @example
     * // Кэширует все элементы из SELECTORS
     * cacheElements();
     */
    function cacheElements() {
        Object.keys(SELECTORS).forEach(key => {
            elements[key] = $(SELECTORS[key]);
        });
    }

    /**
     * Навешивает обработчики событий на элементы интерфейса.
     * Включает обработчики для полей формы, кнопок открытия/закрытия попапа и других элементов.
     * @example
     * // Навешивает все необходимые обработчики событий
     * bindEvents();
     */
    function bindEvents() {
        // Обработчики полей формы
        elements.fieldsContainer
            .on('click', '.clear-field', handleClearFieldClick)
            .on('keydown', '.marking-input', handleInputKeydown);

        // Эту кнопку в кэш не помещаем, т.к. страница заказа не всегда перезагружается после изменений
        $(document).on('click', '.yookassa-marking-button', handleOpenPopup);

        // Основные кнопки
        elements.btnSave.on('click', handleSaveMarking);
        elements.btnClear.on('click', handleClearMarking);
        elements.btnClose.on('click', handleClosePopup);

        // Изменения в полях ввода
        elements.form.on('input change paste', '.marking-input', handleFieldChange);
    }

    /**
     * Обрабатывает клик по кнопке очистки поля ввода.
     * @param {Event} e - Событие клика
     * @example
     * // Очищает поле при клике на кнопку "×"
     * handleClearFieldClick(event);
     */
    function handleClearFieldClick(e) {
        clearMarkingFields($(e.currentTarget).siblings('input'), true);
    }

    /**
     * Обрабатывает нажатие клавиш в поле ввода (Enter и Tab).
     * Обеспечивает навигацию между полями формы.
     * @param {KeyboardEvent} e - Событие клавиатуры
     * @example
     * // При нажатии Tab и Enter переходит к следующему полю
     * handleInputKeydown(event);
     */
    function handleInputKeydown(e) {
        if (e.code !== KEY_CODES.ENTER && e.code !== KEY_CODES.TAB) return;

        e.preventDefault();
        const input = $(e.currentTarget);
        const inputs = elements.fieldsContainer.find('.marking-input');
        const currentIndex = inputs.index(input);

        if (e.shiftKey && e.code === KEY_CODES.TAB && currentIndex > 0) {
            inputs.eq(currentIndex - 1).focus().select();
        } else if (currentIndex < inputs.length - 1) {
            inputs.eq(currentIndex + 1).focus().select();
        } else {
            input.blur();
        }
    }

    /**
     * Обрабатывает открытие попапа с маркировкой товара.
     * Загружает данные товара и отображает форму для ввода маркировки.
     * @param {Event} e - Событие клика
     * @example
     * // Открывает попап для товара с ID 123
     * handleOpenPopup(event);
     */
    function handleOpenPopup(e) {
        const button = $(e.currentTarget);
        const itemId = button.attr('id').replace('yookassa-marking-button-', '');

        resetPopupState();
        loadItemData(itemId);
    }

    /**
     * Обрабатывает сохранение данных маркировки товара.
     * Валидирует поля, отправляет данные на сервер и при успехе вызывает переданный коллбэк.
     *
     * @param {Event} e - Событие клика
     * @param {Function} [onSuccess] - Необязательный коллбэк, вызывается при успешном сохранении (например, для закрытия попапа)
     * @returns {Promise<void>}
     * @example
     * // Сохраняет данные и закрывает попап при успехе
     * await handleSaveMarking(event, () => closePopup());
     */
    async function handleSaveMarking(e, onSuccess) {
        e.preventDefault();
        const itemId = getCurrentItemId();

        clearPopupMessage();
        disableFooterButtons(true);
        enablePreloadSaveButton();
        saveCurrentFields(itemId);
        elements.warningContainer.hide();

        if (!itemId) {
            return handleValidationError('Не смогли сохранить маркировку. Обновите страницу — если ошибка не уйдёт, напишите нам на cms@yoomoney.ru');
        }

        const validationResult = validateFields(state.markingData[itemId], state.validateData);

        if (validationResult.hasErrors) {
            return handleValidationError(validationResult.errorMessage);
        }

        try {
            const response = await saveMarkingData(itemId);

            if (response.data.type === 'validation_error') {
                Object.keys(response.data.fields).forEach(field => highlightField(field));
                markingErrorsWithTypes(response.data.fields);
                return handleValidationError(response.data.message);
            }

            handleSaveSuccess(response, itemId);
            if (onSuccess) onSuccess();
        } catch (error) {
            handleSaveError(error);
        }
    }

    /**
     * Обрабатывает очистку всех данных маркировки для текущего товара.
     * Показывает подтверждающий диалог перед очисткой.
     * @param {Event} e - Событие клика
     * @example
     * // Очищает все поля после подтверждения
     * handleClearMarking(event);
     */
    function handleClearMarking(e) {
        e.preventDefault();
        if (!confirm('Вы уверены, что хотите очистить все данные?')) return;

        clearAllData();
        updateButtonsState();
    }

    /**
     * Обрабатывает закрытие попапа маркировки.
     * Проверяет наличие несохраненных изменений и предлагает сохранить их.
     * @param {Event} e - Событие клика
     * @returns {Promise<void>}
     * @example
     * // Закрывает попап с проверкой изменений
     * await handleClosePopup(event);
     */
    async function handleClosePopup(e) {
        e.preventDefault();

        if (!hasChanges()) {
            closePopup();
            return;
        }

        const userResponse = confirm('У вас есть несохраненные изменения. Сохранить перед закрытием?');

        if (userResponse) {
            await handleSaveMarking(e, () => {
                closePopup();
            });
        } else {
            discardChanges();
            closePopup();
        }
    }

    /**
     * Обрабатывает изменение значений полей ввода.
     * Обновляет состояние кнопок при изменении данных.
     * @example
     * // Вызывается при любом изменении полей ввода
     * handleFieldChange();
     */
    function handleFieldChange() {
        updateButtonsState();
    }

    function markingErrorsWithTypes(errorsData) {
        for (const fieldName in errorsData) {
            if (!errorsData.hasOwnProperty(fieldName)) continue;

            const errorInfo = errorsData[fieldName];
            const input = elements.form.find(`input[name="${fieldName}"]`);
            if (!input) continue;

            const fieldGroup = input.closest('.field-group');
            const errorMessage = errorInfo.actual_type === null
                ? `нужно указать ${errorInfo.expected_type}`
                : `указан код ${errorInfo.actual_type}, требуется ${errorInfo.expected_type}`;

            $(fieldGroup).find('.field-error').remove();
            $(fieldGroup).append($('<div class="field-error"></div>').text(errorMessage));
        }
    }

    /**
     * Сбрасывает состояние попапа перед открытием.
     * Скрывает форму, показывает прелоадер, очищает сообщения.
     * @example
     * // Подготавливает попап к открытию
     * resetPopupState();
     */
    function resetPopupState() {
        elements.popup.show();
        elements.preloader.show();
        elements.form.hide();
        elements.warningContainer.hide();
        elements.title.text('');
        clearPopupMessage();
        clearMarkingFields();
    }

    /**
     * Загружает данные товара для маркировки с сервера.
     * @param {string} itemId - ID товара
     * @example
     * // Загружает данные для товара с ID 123
     * loadItemData('123');
     */
    function loadItemData(itemId) {
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'woocommerce_get_oder_item_meta',
                itemId: itemId,
                security: ajax_object.nonce_get_order_item_meta
            },
            success: (response) => handleLoadSuccess(response, itemId),
            error: handleLoadError
        });
    }

    /**
     * Обрабатывает успешную загрузку данных товара с сервера.
     * @param {Object} response - Ответ сервера
     * @param {string} itemId - ID товара
     * @example
     * // Обрабатывает успешный ответ от сервера
     * handleLoadSuccess(response, '123');
     */
    function handleLoadSuccess(response, itemId) {
        if (!response.success) {
            showPopupMessage(response.data);
            updateButtonsState(false);
            elements.form.data('itemId', itemId).show();
            elements.preloader.hide();
            console.warn(response.data);
            return;
        }

        setupPopup(response.data, itemId);
    }

    /**
     * Обрабатывает ошибку загрузки данных товара.
     * @param {Object} xhr - Объект XHR запроса
     * @example
     * // Обрабатывает ошибку при загрузке данных
     * handleLoadError(xhr);
     */
    function handleLoadError(xhr) {
        showPopupMessage(`Что-то пошло не так. Обновите страницу — если ошибка не уйдёт, напишите нам на cms@yoomoney.ru ${xhr.status} ${xhr.statusText}`);
        updateButtonsState(false);
        elements.form.data('itemId', getCurrentItemId()).show();
        elements.preloader.hide();
        console.error('Ошибка AJAX запроса:', xhr.status, xhr.statusText);
    }

    /**
     * Настраивает попап с данными товара после успешной загрузки.
     * @param {Object} data - Данные товара
     * @param {string} itemId - ID товара
     * @example
     * // Настраивает попап с полученными данными
     * setupPopup(itemData, '123');
     */
    function setupPopup(data, itemId) {
        const { quantity, title: popupTitle, fields, jsonMeta } = data;
        const metaData = $.parseJSON(jsonMeta) || {};

        // Сохраняем правила валидации
        fields.forEach(field => {
            if (field.validate) {
                state.validateData[field.name] = field.validate;
            }
        });

        elements.title.html(popupTitle);
        createFields({
            fields: fields,
            quantity: quantity,
            metaData: state.markingData[itemId] || metaData
        });

        elements.form.data('itemId', itemId).show();
        elements.preloader.hide();

        saveCurrentFields(itemId, state.markingData[itemId] || metaData, true);
        updateButtonsState(true);
    }

    /**
     * Создает HTML-разметку для группы полей ввода маркировки.
     * @param {Array<Object>} fields - Конфигурация полей
     * @param {number} groupIndex - Индекс группы (номер товара)
     * @param {Object} [metaData={}] - Данные для заполнения полей
     * @returns {string} HTML-разметка группы полей
     * @example
     * // Создает разметку для группы полей
     * const html = createFieldGroupHTML(fieldsConfig, 1, {marking_field_1: '123456'});
     */
    function createFieldGroupHTML(fields, groupIndex, metaData = {}) {
        let groupHTML = `<div class="field-group" style="margin-bottom: 20px;">
            <span class="group-label">Товар #${groupIndex}</span>`;

        fields.forEach((fieldConfig) => {
            const fieldName = fieldConfig.name;
            const placeholder = fieldConfig.placeholder || '';
            const uniqueFieldName = `${fieldName}_${groupIndex}`;
            const value = metaData[uniqueFieldName] || '';

            groupHTML += `
            <div style="margin-bottom:5px; position:relative;">
                <input type="text" id="${uniqueFieldName}" name="${uniqueFieldName}"
                       placeholder="${placeholder}" value="${value}"
                       style="width:100%; padding: 6px 25px 6px 6px;"
                       class="marking-input" />
                <span class="clear-field">×</span>
            </div>`;
        });

        groupHTML += `</div>`;
        return groupHTML;
    }

    /**
     * Создает группы полей ввода маркировки в контейнере.
     * @param {Object} config - Конфигурация полей
     * @param {Array} config.fields - Массив конфигураций полей
     * @param {number} config.quantity - Количество групп полей (количество товаров)
     * @param {Object} [config.metaData={}] - Данные для заполнения полей
     * @example
     * // Создает поля для 2 товаров
     * createFields({
     *     fields: [{name: 'marking_field', placeholder: 'Введите код'}],
     *     quantity: 2,
     *     metaData: {marking_field_1: '123', marking_field_2: '456'}
     * });
     */
    function createFields(config) {
        const { fields, quantity = 1, metaData = {} } = config;
        elements.fieldsContainer.empty();

        for (let i = 0; i < quantity; i++) {
            const groupIndex = i + 1;
            elements.fieldsContainer.append(
                createFieldGroupHTML(fields, groupIndex, metaData)
            );
        }
    }

    /**
     * Сохраняет текущие значения полей в состоянии.
     * @param {string} itemId - ID товара
     * @param {Object} [metaData={}] - Метаданные для заполнения
     * @param {boolean} [saveOriginal=false] - Сохранять оригинальные значения
     * @example
     * // Сохраняет текущие значения полей
     * saveCurrentFields('123', {}, true);
     */
    function saveCurrentFields(itemId, metaData = {}, saveOriginal = false) {
        if (!itemId) return;

        state.markingData[itemId] = state.markingData[itemId] || {};

        elements.form.find('input[type="text"]').each(function() {
            const name = $(this).attr('name');
            state.markingData[itemId][name] = $(this).val();
        });

        if (saveOriginal) {
            state.originalValues = {
                fields: {},
                itemId: itemId
            };

            elements.form.find('input[type="text"]').each(function() {
                const input = $(this);
                const name = input.attr('name');
                state.originalValues.fields[name] = metaData[name] || input.val();
            });
        }
    }

    /**
     * Возвращает ID текущего товара в попапе.
     * @returns {string|null} ID товара или null если не установлен
     * @example
     * // Получает ID текущего товара
     * const itemId = getCurrentItemId();
     */
    function getCurrentItemId() {
        return elements.form.data('itemId');
    }

    /**
     * Очищает сообщение в попапе.
     * @example
     * // Очищает все сообщения
     * clearPopupMessage();
     */
    function clearPopupMessage() {
        elements.messageContainer.hide().text('').removeClass("error success");
    }

    /**
     * Показывает сообщение в попапе.
     * @param {string} message - Текст сообщения
     * @param {string} [status='error'] - Статус сообщения ('error' или 'success')
     * @example
     * // Показывает сообщение об ошибке
     * showPopupMessage('Ошибка сохранения');
     * // Показывает успешное сообщение
     * showPopupMessage('Данные сохранены', 'success');
     */
    function showPopupMessage(message, status = 'error') {
        elements.messageContainer.show().html(message).addClass(status);
    }

    /**
     * Обновляет состояние кнопок в подвале попапа в зависимости от наличия изменений.
     * @param {boolean} [clearMessage=true] - Флаг очистки сообщения
     * @example
     * // Обновляет состояние кнопок после изменения полей
     * updateButtonsState();
     */
    function updateButtonsState(clearMessage = true) {
        if (hasChanges()) {
            enableFooterButtons();
        } else {
            if (clearMessage) clearPopupMessage();
            disableSaveButton();
            if (!hasFilledFields(state.originalValues)) {
                disableClearButton();
            }
        }
    }

    /**
     * Блокирует или разблокирует кнопки в подвале попапа.
     * @param {boolean} disabled - Блокировать кнопки
     * @example
     * // Блокирует кнопки
     * toggleFooterButtons(true);
     * // Разблокирует кнопки
     * toggleFooterButtons(false);
     */
    function toggleFooterButtons(disabled) {
        if (disabled) {
            disableFooterButtons();
        } else {
            enableFooterButtons();
            disablePreloadSaveButton();
        }
    }

    /**
     * Блокирует все кнопки в подвале попапа.
     * @example
     * // Блокирует кнопки сохранения и очистки
     * disableFooterButtons();
     */
    function disableFooterButtons() {
        disableSaveButton();
        disableClearButton();
    }

    /**
     * Разблокирует все кнопки в подвале попапа.
     * @example
     * // Разблокирует кнопки сохранения и очистки
     * enableFooterButtons();
     */
    function enableFooterButtons() {
        enableSaveButton();
        enableClearButton();
    }

    /**
     * Блокирует кнопку сохранения.
     * @example
     * // Делает кнопку сохранения неактивной
     * disableSaveButton();
     */
    function disableSaveButton() {
        elements.btnSave.prop('disabled', true);
    }

    /**
     * Разблокирует кнопку сохранения.
     * @example
     * // Активирует кнопку сохранения
     * enableSaveButton();
     */
    function enableSaveButton() {
        elements.btnSave.prop('disabled', false);
    }

    /**
     * Включает прелоадер кнопки сохранения.
     * @example
     * // Переключает содержимое кнопки сохранения
     * enablePreloadSaveButton();
     */
    function enablePreloadSaveButton() {
        elements.btnSave.html('<span class="btn-loader"></span>');
    }

    /**
     * Выключает прелоадер кнопки сохранения.
     * @example
     * // Переключает содержимое кнопки сохранения
     * disablePreloadSaveButton();
     */
    function disablePreloadSaveButton() {
        elements.btnSave.html('Сохранить');
    }

    /**
     * Блокирует кнопку очистки.
     * @example
     * // Делает кнопку очистки неактивной
     * disableClearButton();
     */
    function disableClearButton() {
        elements.btnClear.prop('disabled', true);
    }

    /**
     * Разблокирует кнопку очистки.
     * @example
     * // Активирует кнопку очистки
     * enableClearButton();
     */
    function enableClearButton() {
        elements.btnClear.prop('disabled', false);
    }

    /**
     * Подсвечивает поле ввода указанным цветом.
     * @param {string|jQuery} field - Поле ввода (селектор или jQuery объект)
     * @param {string} [color='red'] - Цвет подсветки ('red' или 'green')
     * @param {boolean} [highlight=true] - Включить или выключить подсветку
     * @example
     * // Подсвечивает поле красным
     * highlightField('#marking_field_1');
     * // Подсвечивает поле зеленым
     * highlightField(inputElement, 'green');
     * // Убирает подсветку
     * highlightField('#marking_field_1', 'red', false);
     */
    function highlightField(field, color = 'red', highlight = true) {
        const input = typeof field === 'string'
            ? elements.form.find(`input[name="${field}"]`)
            : $(field);

        input.removeClass('red green');

        if (highlight) {
            input.addClass(color);
        } else {
            input.closest('.field-group').find('.field-error').remove();
        }
    }

    /**
     * Обновляет статус кнопки маркировки для конкретного товара.
     * Изменяет иконку в зависимости от состояния заполнения полей.
     * @param {string} itemId - ID товара
     * @example
     * // Обновляет статус кнопки после сохранения
     * updateButtonStatus('123');
     */
    function updateButtonStatus(itemId) {
        const button = $(`#yookassa-marking-button-${itemId}`);
        const icon = button.find('.yookassa-mark-code-icon');
        const data = state.markingData[itemId] || {};

        const values = Object.values(data).filter(val => val && val.trim() !== '');
        const allFields = Object.keys(data);
        const allFilled = allFields.every(key => data[key] && data[key].trim() !== '');

        icon.removeClass('new not-filled filled');

        if (values.length === 0) {
            icon.addClass('new');
        } else if (allFilled) {
            icon.addClass('filled');
        } else {
            icon.addClass('not-filled');
        }
    }

    /**
     * Очищает валидацию всех полей формы.
     * @example
     * // Сбрасывает подсветку всех полей
     * clearValidation();
     */
    function clearValidation() {
        elements.form.find('input').each(function() {
            const input = $(this);
            const inputName = input.attr('name');

            const isInvalidField = state.invalidFields.some(item => item === inputName);
            if (!isInvalidField) {
                highlightField(input, 'gray', false);
            }
        });
    }

    /**
     * Валидирует поля формы маркировки согласно правилам.
     * @param {Object} data - Данные полей
     * @param {Object} rules - Правила валидации
     * @returns {Object} Результат валидации с флагами ошибок и сообщениями
     * @example
     * // Проверяет поля на соответствие правилам
     * const result = validateFields(
     *     {marking_field_1: '123456', marking_field_2: '987654'},
     *     {marking_field: {pattern: '/^[0-9]{6}$/'}}
     * );
     * if (result.hasErrors) {
     *     console.error(result.errorMessage);
     * }
     */
    function validateFields(data, rules) {
        clearValidation();

        const result = {
            emptyFields: [],
            invalidFields: [],
            duplicates: {},
            denominatorExceeded: {},
            denominator: 0,
            hasErrors: false,
            errorMessage: ''
        };

        const fieldsByValue = {};
        let denominatorFieldGroup = null;

        // Сначала находим группу полей, для которой определен denominator
        Object.keys(rules).forEach(baseFieldName => {
            const fieldRules = rules[baseFieldName];
            if (!denominatorFieldGroup && fieldRules.denominator) {
                denominatorFieldGroup = baseFieldName;
                result.denominator = parseInt(fieldRules.denominator, 10);
            }
        });

        Object.entries(data).forEach(([key, value]) => {
            const baseFieldName = key.replace(/_\d+$/, '');
            const fieldRules = rules[baseFieldName] || {};
            if (Object.keys(fieldRules).length === 0) return;

            const itemNumber = key.match(/_(\d+)$/)?.[1] || '?';
            const fieldName = `Товар #${itemNumber}`;

            if (fieldRules.isEmpty && !value) {
                result.emptyFields.push(fieldName);
                return;
            }

            if (fieldRules.pattern && value) {
                const regex = new RegExp(fieldRules.pattern.slice(1, -1));
                if (!regex.test(value)) {
                    result.invalidFields.push(fieldName);
                    highlightField(key);
                    return;
                }
            }

            if (value) {
                fieldsByValue[baseFieldName] = fieldsByValue[baseFieldName] || {};
                fieldsByValue[baseFieldName][value] = fieldsByValue[baseFieldName][value] || [];
                fieldsByValue[baseFieldName][value].push({ key, fieldName });
            }
        });

        Object.entries(fieldsByValue).forEach(([fieldName, valuesMap]) => {
            const fieldRules = rules[fieldName] || {};

            // Применяем denominator только к выбранной группе полей
            if (fieldName === denominatorFieldGroup) {
                Object.values(valuesMap).forEach(fields => {
                    fields.forEach(({key}) => highlightField(key, 'gray', false));

                    if (fields.length > result.denominator) {
                        fields.slice(0, result.denominator).forEach(({key}) => {
                            highlightField(key, 'green');
                        });

                        const exceededFields = fields.slice(result.denominator);
                        exceededFields.forEach(({key}) => {
                            highlightField(key);
                            result.denominatorExceeded[key] = true;
                        });
                    }
                });
            }

            if (fieldRules.isDuplicate) {
                Object.values(valuesMap)
                    .filter(fields => fields.length > 1)
                    .flat()
                    .forEach(({key, fieldName: errorFieldName}) => {
                        highlightField(key);
                        if (!result.denominatorExceeded[key]) {
                            result.duplicates[errorFieldName] = true;
                        }
                    });
            }
        });

        // Формируем общее сообщение об ошибках
        const errorMessages = [];

        if (result.emptyFields.length) {
            elements.warningContainer.show();
        }

        if (result.invalidFields.length) {
            errorMessages.push(`Неверный формат маркировки: ${result.invalidFields.join(', ')}. Смените язык клавиатуры и отсканируйте код снова`);
        }

        if (Object.keys(result.denominatorExceeded).length) {
            errorMessages.push(
                `Слишком много товаров из одной упаковки. Максимум — ${result.denominator}. ` +
                `Отсканируйте ${pluralizeProducts(Object.keys(result.denominatorExceeded).length)} из другой упаковки`
            );
        }

        if (Object.keys(result.duplicates).length) {
            errorMessages.push(`Одинаковый код маркировки: ${Object.keys(result.duplicates).join(', ')}. Исправьте, пожалуйста`);
        }

        result.hasErrors = errorMessages.length > 0;
        result.errorMessage = errorMessages.length > 1
            ? "Ошибки:<br>" + errorMessages.map((msg) => `- ${msg}`).join('<br>')
            : errorMessages.map((msg) => `${msg}`).join('<br>');

        return result;
    }

    /**
     * Очищает поля ввода маркировки.
     * @param {jQuery} [specificInput=null] - Конкретное поле для очистки
     * @param {boolean} [checkChanges=false] - Проверять изменения после очистки
     * @example
     * // Очищает все поля
     * clearMarkingFields();
     * // Очищает конкретное поле
     * clearMarkingFields($('#marking_field_1'), true);
     */
    function clearMarkingFields(specificInput = null, checkChanges = false) {
        const fields = specificInput
            ? $(specificInput)
            : elements.form.find('input[type="text"]');

        fields.each(function() {
            $(this).val('');
            highlightField($(this), 'gray', false);
        });

        if (specificInput && specificInput.length) {
            const name = specificInput.attr('name');
            const itemId = getCurrentItemId();
            if (itemId && name && state.markingData[itemId]) {
                state.markingData[itemId][name] = '';
            }
        }

        if (checkChanges) {
            updateButtonsState();
        }
    }

    /**
     * Проверяет, есть ли несохраненные изменения в форме.
     * @returns {boolean} true если есть изменения, иначе false
     * @example
     * // Проверяет наличие изменений перед закрытием попапа
     * if (hasChanges()) {
     *     alert('Есть несохраненные изменения!');
     * }
     */
    function hasChanges() {
        const itemId = elements.form.data('itemId');
        if (!itemId || itemId !== state.originalValues.itemId) return false;

        let changed = false;

        elements.form.find('input[type="text"]').each(function() {
            const input = $(this);
            const name = input.attr('name');
            const currentValue = input.val();

            if (state.originalValues.fields[name] !== undefined &&
                state.originalValues.fields[name] !== currentValue) {
                changed = true;
                return false;
            }
        });

        return changed;
    }

    /**
     * Проверяет, есть ли заполненные поля в оригинальных значениях.
     * @param {Object} originalValues - Оригинальные значения полей
     * @returns {boolean} true если есть заполненные поля, иначе false
     * @example
     * // Проверяет наличие заполненных полей
     * const hasFilled = hasFilledFields(state.originalValues);
     */
    function hasFilledFields(originalValues) {
        if (!originalValues.fields || Object.keys(originalValues.fields).length === 0) {
            return false;
        }

        return Object.values(originalValues.fields).some(
            value => value !== undefined && value !== null && value !== ""
        );
    }

    /**
     * Формирует фразу с количеством товаров в правильном склонении.
     * @param {number} count - Количество товаров
     * @returns {string} Строка с правильным склонением
     * @example
     * // Возвращает "оставшийся 1 товар"
     * pluralizeProducts(1);
     * // Возвращает "оставшиеся 2 товара"
     * pluralizeProducts(2);
     * // Возвращает "оставшихся 5 товаров"
     * pluralizeProducts(5);
     */
    function pluralizeProducts(count) {
        const cases = [2, 0, 1, 1, 1, 2];
        const forms = ['товар', 'товара', 'товаров'];
        const remaining = ['оставшийся', 'оставшиеся', 'оставшихся'];

        const index = (count % 100 > 4 && count % 100 < 20)
            ? 2
            : cases[Math.min(count % 10, 5)];

        return `${remaining[index]} ${count} ${forms[index]}`;
    }

    /**
     * Сохраняет данные маркировки на сервере через AJAX.
     * @param {string} itemId - ID товара
     * @returns {Promise<Object>} Promise с ответом сервера
     * @example
     * // Отправляет данные на сервер
     * try {
     *     const response = await saveMarkingData('123');
     *     console.log('Данные сохранены:', response);
     * } catch (error) {
     *     console.error('Ошибка сохранения:', error);
     * }
     */
    function saveMarkingData(itemId) {
        const originalText = elements.btnSave.text();
        toggleFooterButtons(true);

        return $.ajax({
            url: ajax_object.ajax_url,
            method: 'POST',
            dataType: 'json',
            data: prepareSaveData(state.markingData[itemId], state.validateData)
        }).always(() => {
            toggleFooterButtons(false, originalText);
        });
    }

    /**
     * Подготавливает данные для отправки на сервер.
     * Группирует поля по типам и формирует структуру, ожидаемую сервером.
     * @param {Object} dataToSend - Данные полей маркировки
     * @param {Object} validateData - Правила валидации
     * @returns {Object} Подготовленные данные для AJAX-запроса
     * @example
     * // Подготавливает данные перед отправкой
     * const data = prepareSaveData(
     *     {marking_field_1: '123456', marking_field_2: '654321'},
     *     {marking_field: {pattern: '/^[0-9]{6}$/'}}
     * );
     * // Возвращает: {action: 'save_marking_meta', _marking_fields: {marking_field_1: '123456', marking_field_2: '654321'}, ...}
     */
    function prepareSaveData(dataToSend, validateData) {
        const groupedData = {};

        Object.entries(dataToSend).forEach(([key, value]) => {
            const baseFieldName = key.replace(/_\d+$/, '');
            if (validateData[baseFieldName]) {
                groupedData[baseFieldName] = groupedData[baseFieldName] || {};
                groupedData[baseFieldName][key] = value;
            }
        });

        const ajaxData = {
            action: 'save_marking_meta',
            orderItemId: getCurrentItemId(),
            security: ajax_object.nonce_save_marking_meta
        };

        // Добавляем данные полей с префиксом _ и именем поля во множественном числе
        Object.entries(groupedData).forEach(([fieldName, values]) => {
            const ajaxFieldName = `_${fieldName}s`;
            ajaxData[ajaxFieldName] = values;
        });

        return ajaxData;
    }

    /**
     * Обрабатывает ошибку валидации полей.
     * @param {string} message - Сообщение об ошибке
     * @example
     * // Показывает сообщение об ошибке валидации
     * handleValidationError('Неверный формат кода');
     */
    function handleValidationError(message) {
        showPopupMessage(message);
        enableFooterButtons();
        disablePreloadSaveButton();
    }

    /**
     * Обрабатывает успешное сохранение данных маркировки.
     * Обновляет интерфейс и показывает сообщение об успехе.
     * @param {Object} response - Ответ сервера
     * @param {string} itemId - ID товара
     * @example
     * // Обрабатывает успешный ответ от сервера
     * handleSaveSuccess({data: {message: 'Успешно сохранено'}}, '123');
     */
    function handleSaveSuccess(response, itemId) {
        state.invalidFields.forEach(field => highlightField(field, 'gray', false));
        state.invalidFields = [];
        updateButtonStatus(itemId);
        showPopupMessage(response.data.message, 'success');
        saveCurrentFields(itemId, state.markingData[itemId], true);
        updateButtonsState(false);
        disablePreloadSaveButton();
    }

    /**
     * Обрабатывает ошибку при сохранении данных маркировки.
     * Показывает сообщение об ошибке и подсвечивает проблемные поля.
     * @param {Object} error - Объект ошибки
     * @example
     * // Обрабатывает ошибку сохранения
     * handleSaveError({
     *     responseJSON: {
     *         data: {
     *             message: 'Ошибка валидации',
     *             fields: {marking_field_1: 'Недопустимый формат'}
     *         }
     *     }
     * });
     */
    function handleSaveError(error) {
        if (error.responseJSON?.data?.type === 'validation_error') {
            state.invalidFields = error.responseJSON.data?.fields
                ? Object.keys(error.responseJSON.data.fields)
                : [];

            state.invalidFields.forEach(field => highlightField(field));
            showValidationErrors(error.responseJSON.data.fields);
        }

        showPopupMessage('Не получилось — обновите страницу и добавьте маркировку заново. Если ошибка не уйдёт, напишите нам на cms@yoomoney.ru');
        disablePreloadSaveButton();
        console.error(error);
    }

    /**
     * Показывает ошибки валидации полей.
     * @param {Object} errorsData - Данные об ошибках
     * @example
     * // Показывает ошибки валидации
     * showValidationErrors({
     *     marking_field_1: {expected_type: 'число', actual_type: 'строка'}
     * });
     */
    function showValidationErrors(errorsData) {
        for (const fieldName in errorsData) {
            if (!errorsData.hasOwnProperty(fieldName)) continue;

            const errorInfo = errorsData[fieldName];
            const input = elements.form.find(`input[name="${fieldName}"]`);
            if (!input) continue;

            const fieldGroup = input.closest('.field-group');
            const errorMessage = errorInfo.actual_type === null
                ? `нужно указать ${errorInfo.expected_type}`
                : `указан код ${errorInfo.actual_type}, требуется ${errorInfo.expected_type}`;

            $(fieldGroup).find('.field-error').remove();
            $(fieldGroup).append($('<div class="field-error"></div>').text(errorMessage));
        }
    }

    /**
     * Закрывает попап маркировки.
     * @example
     * // Скрывает попап
     * closePopup();
     */
    function closePopup() {
        elements.popup.hide();
    }

    /**
     * Отменяет несохраненные изменения.
     * @example
     * // Отменяет изменения для текущего товара
     * discardChanges();
     */
    function discardChanges() {
        const itemId = getCurrentItemId();
        if (itemId && state.markingData[itemId]) {
            delete state.markingData[itemId];
        }
    }

    /**
     * Очищает все данные маркировки в попапе.
     * @example
     * // Очищает все поля и сбрасывает состояние
     * clearAllData();
     */
    function clearAllData() {
        clearMarkingFields();
        clearPopupMessage();
        const itemId = getCurrentItemId();
        if (itemId) state.markingData[itemId] = {};
    }

    // Инициализация модуля
    $(document).ready(init);

})(jQuery);
