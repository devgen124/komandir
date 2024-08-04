import Select from "select-custom";

export default function customizeSelects() {

	initSelects();

	$(document).on('berocket_ajax_products_loaded', initSelects);

	function initSelects() {
		const selects = document.querySelectorAll('select:not(.country_select, .state_select)');

		if (selects.length) {
			selects.forEach((el) => {
				const select = new Select(el, {
					optionBuilder: undefined,
					panelItem: {position: '', item: '', className: ''},
					changeOpenerText: true,
					multipleSelectionOnSingleClick: false,
					multipleSelectOpenerText: {labels: false, array: false},
					allowPanelClick: false,
					openOnHover: false,
					closeOnMouseleave: false,
					wrapDataAttributes: false,
					openerLabel: false,
				});
				select.init();
			});
		}
	}
}
