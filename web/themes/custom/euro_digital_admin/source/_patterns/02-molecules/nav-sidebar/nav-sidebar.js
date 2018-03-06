/**
 * @function
 * Provides functions for every checkbox: custom style.
 */

(function ($) {
  Drupal.behaviors.sidebarAnimations = {
    attach: function (context, settings) { // eslint-disable-line no-unused-vars
      var nestedMenu = $('.nested');
      // Add a class for list item containing another list
      if (nestedMenu.length > 0) {
        nestedMenu.parent().addClass('has-children');
      }
      $('.has-children').click(function () {
      });
    }
  };
}(jQuery));

