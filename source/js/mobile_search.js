

export default function mobileSearch() {
  window.addEventListener('DOMContentLoaded', () => {
    const searchOpen = document.querySelector('.livesearch-mobile-btn');
    const searchPanel = document.querySelector('.livesearch-wrapper');

    if (searchOpen && searchPanel) {
      searchOpen.addEventListener('click', () => {
        searchPanel.classList.add('livesearch-wrapper--opened');
      });

      const searchClose = searchPanel.querySelector('.livesearch-mobile-back');
      searchClose.addEventListener('click', () => {
        searchPanel.classList.remove('livesearch-wrapper--opened');
      });
    }
  }); 
}