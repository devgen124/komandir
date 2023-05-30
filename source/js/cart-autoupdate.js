import { debounce } from "debounce";
import initQuantityCounter from "./cart-quantity-counter.js";

export default function initCartAutoUpdate() {

	const updateCounterHandler = () => {
		initQuantityCounter((input) => {
			$(input).trigger('change');
		});
	};

	$(() => {
		updateCounterHandler();

		$(document.body).on('updated_wc_div', () => {
			updateCounterHandler();
		});

		$(document).on('change', 'input.qty', debounce(() => {
			$("[name='update_cart']").trigger("click");
		}, 600));
	});
}
