const $attrsBlocks = $('.woo-attributes');
const isMobile = document.documentElement.clientWidth < 576;

export default function initAttrMobileToggler() {
    $(function () {
        if ($attrsBlocks.length && isMobile) {
            $attrsBlocks.each((i, attrs) => {
                const $heading = $(attrs).find('.woo-attributes-heading');
                const $cont = $(attrs).find('.woo-attributes-inner');
                const $hide = $(attrs).find('.woo-attributes-hide');
        
                $heading.on('click', () => {
                    $heading.addClass('woo-attributes-heading-active');
                    $cont.slideDown();
                });
        
                $hide.on('click', () => {
                    $heading.removeClass('woo-attributes-heading-active');
                    $cont.slideUp();
                });
            });
        }
    });
}