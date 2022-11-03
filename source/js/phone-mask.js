export default function phoneMask () {
  const $phoneMask = $('.phone-mask');

  if ($phoneMask) {
    $phoneMask.mask('+79999999999');
  }
} 