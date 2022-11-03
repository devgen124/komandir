const gridSwitcher = document.querySelector('.grid-switcher');
const productsBlock = document.querySelector('.products');

export default function initGridSwitcher() {
    if (gridSwitcher && productsBlock) {
        const switchButtons = gridSwitcher.querySelectorAll('.grid-view');

        const removeActiveButtons = () => {
            switchButtons.forEach(btn => {
                btn.classList.remove('grid-view--active');
            });
        }

        const restoreActiveButtons = () => {
            const view = localStorage.getItem('grid-view');
            if (view) {
                switchButtons.forEach(btn => {
                    btn.classList.remove('grid-view--active');
    
                    if (view === btn.dataset.grid) {
                        btn.classList.add('grid-view--active');
                        productsBlock.classList.remove('grid', 'list');
                        productsBlock.classList.add(view);
                    }
                });
            }
        }

        restoreActiveButtons();

        switchButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                removeActiveButtons();
                const thisBtn = e.target.closest('.grid-view');
                const view = thisBtn.dataset.grid;
                thisBtn.classList.add('grid-view--active');
                productsBlock.classList.remove('grid', 'list');
                productsBlock.classList.add(view);
                localStorage.setItem('grid-view', view);
            });
        });
    }
}
