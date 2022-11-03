export default function initPassViewSwitcher(element) {
    const showPassButtons = element.querySelectorAll('.show-pass');

    if (showPassButtons.length) {
        showPassButtons.forEach((btn) => {
            btn.onclick = (e) => {
                e.preventDefault();
                const inp = e.target.previousElementSibling;

                if (inp) {
                    if (inp.type === 'password') {
                        inp.type = 'text';
                    } else {
                        inp.type = 'password';
                    }
                }
            };
        });
    }
}