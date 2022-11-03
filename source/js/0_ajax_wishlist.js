

export default function ajax_wishlist() {
    $(function() {
        $('.wt-wishlist-button').on('click', function (e) {
            let $btn = $(this);
            setTimeout(() => {
                $.ajax({
                    url: dataObj.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'update_wishlist_count'
                    },
                    success: function (response) {
                        const $wishcount = $('.wishlist-count');
                        if (response == 0) {
                            $wishcount.addClass('wishlist-count-empty');
                        }
                        $wishcount.text(response);

                    }
                });
            }, 1200);
        });
    });
}