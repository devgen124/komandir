const quantityEl = document.querySelectorAll('div.product-quantity');

export default function initQuantityCounter() {

    if (quantityEl.length) {
        quantityEl.forEach((el) => {
            const input = el.querySelector('[type="number"]');
            const minus = el.querySelector('.product-quantity-minus');
            const plus = el.querySelector('.product-quantity-plus');
        
            minus.onclick = (e) => {
                e.preventDefault();

                if (input.value == 0) {
                    input.value = 0;
                } else {
                    input.value--;
                    $(input).trigger('input');
                }
            };
        
            plus.onclick = (e) => { 
                e.preventDefault();
                input.value++;
                $(input).trigger('input');
            };

        });
    }
}