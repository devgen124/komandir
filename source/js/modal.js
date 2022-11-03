export default function modalInit() {
  const $modalLink = $('a[href*="#modal-"]');

  $modalLink && $modalLink.magnificPopup({
    type: 'inline',
    midClick: true,
    callbacks: {
      open: () => {
        $('.wpcf7-tel').mask('+79999999999');
      }
    }
  });

  document.addEventListener( 'wpcf7submit', function( e ) {
    $(e.target).find('p').hide();
    $(e.target.closest('.custom-popup')).find('h2 + p').hide();
  }, false );
}
