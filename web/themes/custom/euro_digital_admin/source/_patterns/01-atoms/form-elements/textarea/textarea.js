(function ($) {
  Drupal.behaviors.styleTextArea = {
    attach: function (context, settings) { // eslint-disable-line no-unused-vars
      $('textarea').focus(function () {
        $(this).addClass('on-focus');
      });
      $('textarea').focusout(function () {
        $(this).removeClass('on-focus');
      });
    }
  };
}(jQuery));
