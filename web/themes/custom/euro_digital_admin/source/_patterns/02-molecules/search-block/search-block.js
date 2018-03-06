/**
 * @function
 * Provides a function to change on focus appearance for custom forms.
 */

(function ($) {
  Drupal.behaviors.styleSearchBlock = {
    attach: function (context, settings) { // eslint-disable-line no-unused-vars
      var searchBlock = $('#search-block-form .form-search');
      if (searchBlock.length > 0) {
        searchBlock.on('hover click', function () {
          $('#search-block-form').toggleClass('on-focus');
        });
      }
    }
  };
}(jQuery));
