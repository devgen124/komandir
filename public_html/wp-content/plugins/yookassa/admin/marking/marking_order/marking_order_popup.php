<?php
?>

<div id="yookassa-marking-popup-overlay" style="display:none;">
    <div id="yookassa-marking-popup">
        <div id="yookassa-marking-popup-header">
            <h2 id="yookassa-marking-popup-title"></h2>
            <button type="button" id="yookassa-marking-close-btn" class="button" aria-label="<?= __('Закрыть', 'yookassa'); ?>">&times;</button>
        </div>
        <div class="yookassa-preloader" style="display:none;"></div>
        <form id="yookassa-marking-popup-form">
            <p id="yookassa-marking-popup-warning" style="display:none;"><?= __('Заполните пустые поля: за продажу товара без маркировки можно получить штраф', 'yookassa'); ?></p>
            <div id="yookassa-marking-popup-fields"></div>

            <div id="yookassa-marking-popup-footer">
                <p id="yookassa-marking-popup-message" style="display:none;"></p>
                <div id="yookassa-marking-popup-footer-block">
                    <img id="yookassa-marking-popup-logo" src="<?= YooKassa::$pluginUrl . 'assets/images/kassa.png'; ?>"  alt="yookassa-logo"/>
                    <div id="yookassa-marking-popup-buttons">
                        <button type="button" id="yookassa-marking-save-btn" class="button button-primary"><?= __('Сохранить', 'yookassa'); ?></button>
                        <button type="button" id="yookassa-marking-clear-btn" class="button"><?= __('Очистить все', 'yookassa'); ?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
