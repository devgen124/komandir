export default function initQuantityCounter(triggerEventFunc) {
	const quantityEl = document.querySelectorAll('div.product-quantity');

    if (quantityEl.length) {
        quantityEl.forEach((el) => {
            const input = el.querySelector('[type="number"]');
			const countButtons = el.querySelectorAll('.product-quantity-btn');
			const isMinusBtn = (btn) => btn.classList.contains('product-quantity-minus');
			const isPlusBtn = (btn) => btn.classList.contains('product-quantity-plus');

			countButtons.forEach(btn => {
				btn.onclick = (e) => {
					e.preventDefault();
					if (isMinusBtn(e.target) && input.value > 1) {
						input.value--;
						triggerEventFunc(input);
					} else if (isPlusBtn(e.target)) {
						input.value++;
						triggerEventFunc(input);
					}
				};
			});
        });
    }
}
