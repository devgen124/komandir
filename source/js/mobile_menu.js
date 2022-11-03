

export default function mobileSideMenu() {
  window.addEventListener('DOMContentLoaded', () => {
    const mobileOpen = document.querySelector('.middle-menu-mobile-btn');
    const menu = document.querySelector('.mobile-side-menu');

    if (mobileOpen && menu) {
        mobileOpen.addEventListener('click', () => {
            menu.classList.add('mobile-side-menu--opened');
        });

        const mobileClose = menu.querySelector('.mobile-side-menu-close');
        mobileClose.addEventListener('click', () => {
             menu.classList.remove('mobile-side-menu--opened');
        });
    }
  });
}