import {tns} from 'tiny-slider/src/tiny-slider.js';

const isMobile = document.documentElement.clientWidth < 992;

export default function salesSlider() {
	if (isMobile) {
		let slider = tns({
			container: '.sales-inner .products',
			controls: false,
			nav: false,
			responsive: {
				576: {
					items: 2,
				},
				992: {
					disable: true
				}
			}
		});
	}
}
