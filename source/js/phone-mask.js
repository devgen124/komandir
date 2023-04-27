import IMask from 'imask';

export default function phoneMask() {
	const phoneInputs = document.querySelectorAll('.phone-mask');

	if (phoneInputs) {
		phoneInputs.forEach(input => {
			let mask = IMask(input, {
				mask: '+{7} 000 000 00 00'
			});
		});
	}
}
