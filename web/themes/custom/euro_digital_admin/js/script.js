/**
 * @function
 * This file provides  generic functions to the website.
 */

(function ($) {
  Drupal.behaviors.genericEuro = {
    attach: function (context, settings) { // eslint-disable-line no-unused-vars
      var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
      var  isSafari = navigator.userAgent.indexOf('Safari') !== -1 && navigator.userAgent.indexOf('Chrome') === -1;
      if (iOS) {
        $('body').addClass('is-ios');
      }
      if (isSafari) {
        $('body').addClass('is-safari');
      }
    }
  };
}(jQuery));
