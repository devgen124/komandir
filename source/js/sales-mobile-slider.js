import {tns} from 'tiny-slider/src/tiny-slider.js';

const isMobile = document.documentElement.clientWidth < 992;
const container = document.querySelector('.sales-inner .products');

export default function salesSlider() {
	if (isMobile && container) {
		let slider = tns({
			container: '.sales-inner .products',
			controlsText: [
				'', ''
			],
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
