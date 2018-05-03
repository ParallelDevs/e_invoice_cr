/**
 * @function
 * Provides a function for every select: custom style.
 */

(function ($) {
  Drupal.behaviors.styleSelectInput = {
    attach: function (context, settings) { // eslint-disable-line no-unused-vars
      var selectItem = $('select');
      if (selectItem.length > 0) {
        selectItem.select2({
          placeholder: 'Select an option'
        });
      }
    }
  };
}(jQuery));
