const filtersBtn = document.querySelector('.filters-btn');
const filtersSidebar = document.querySelector('.filters-sidebar');
const isMobile = document.documentElement.clientWidth < 576;

export default function initSidebarToggler() {

    togglesidebar();

    $(document).on('berocket_ajax_products_loaded', togglesidebar);

    function togglesidebar() {
        if (filtersBtn && filtersSidebar && isMobile) {
            const toggleSidebarVisibility = () => {
                filtersSidebar.classList.toggle('filters-sidebar-visible');
                document.body.classList.toggle('scroll-lock');
            }
        
            filtersBtn.addEventListener('click', toggleSidebarVisibility);
            const closeBtn = filtersSidebar.querySelector('.filters-close');
            closeBtn.addEventListener('click', toggleSidebarVisibility);
        }
    }
}