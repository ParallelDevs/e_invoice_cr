'use strict';

/**
 * @function
 * This file provides  generic functions to the website.
 */

(function ($) {
  Drupal.behaviors.genericEuro = {
    attach: function attach(context, settings) {
      // eslint-disable-line no-unused-vars
      var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
      var isSafari = navigator.userAgent.indexOf('Safari') !== -1 && navigator.userAgent.indexOf('Chrome') === -1;
      if (iOS) {
        $('body').addClass('is-ios');
      }
      if (isSafari) {
        $('body').addClass('is-safari');
      }
    }
  };
})(jQuery);
'use strict';

/**
 * @function
 * Provides functions for every checkbox: custom style.
 */

(function ($) {
  Drupal.behaviors.sidebarAnimations = {
    attach: function attach(context, settings) {
      // eslint-disable-line no-unused-vars
      var nestedMenu = $('.nested');
      // Add a class for list item containing another list
      if (nestedMenu.length > 0) {
        nestedMenu.parent().addClass('has-children');
      }
      $('.has-children').click(function () {});
    }
  };
})(jQuery);
'use strict';

/**
 * @function
 * Provides a function to change on focus appearance for custom forms.
 */

(function ($) {
  Drupal.behaviors.styleSearchBlock = {
    attach: function attach(context, settings) {
      // eslint-disable-line no-unused-vars
      var searchBlock = $('#search-block-form .form-search');
      if (searchBlock.length > 0) {
        searchBlock.on('hover click', function () {
          $('#search-block-form').toggleClass('on-focus');
        });
      }
    }
  };
})(jQuery);
'use strict';

/**
 * @function
 * Provides a function for every select: custom style.
 */

(function ($) {
  Drupal.behaviors.styleSelectInput = {
    attach: function attach(context, settings) {
      // eslint-disable-line no-unused-vars
      var selectItem = $('select');
      if (selectItem.length > 0) {
        selectItem.select2({
          placeholder: 'Select an option'
        });
      }
    }
  };
})(jQuery);
'use strict';

(function ($) {
  Drupal.behaviors.styleTextArea = {
    attach: function attach(context, settings) {
      // eslint-disable-line no-unused-vars
      $('textarea').focus(function () {
        $(this).addClass('on-focus');
      });
      $('textarea').focusout(function () {
        $(this).removeClass('on-focus');
      });
    }
  };
})(jQuery);
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

/* PushMenu()
 * ==========
 * Adds the push menu functionality to the sidebar.
 *
 * @usage: $('.btn').PushMenu(options)
 *          or add [data-toggle="push-menu"] to any button
 *          Pass any option as data-option="value"
 */

(function ($) {
  Drupal.behaviors.sidebarCanvasOff = {
    attach: function attach(context, settings) {
      // eslint-disable-line no-unused-vars
      'use strict';

      var dataKey = 'lte.PushMenu';

      var Default = {
        collapseScreenSize: 767,
        expandOnHover: false,
        expandTransitionDelay: 200
      };

      var Selector = {
        collapsed: '.sidebar-collapse',
        open: '.sidebar-open',
        mainSidebar: '.main-sidebar',
        contentWrapper: '.content-wrapper',
        searchInput: '.sidebar-form .form-control',
        button: '[data-toggle="push-menu"]',
        mini: '.sidebar-mini',
        expanded: '.sidebar-expanded-on-hover',
        layoutFixed: '.fixed'
      };

      var ClassName = {
        collapsed: 'sidebar-collapse',
        open: 'sidebar-open',
        mini: 'sidebar-mini',
        expanded: 'sidebar-expanded-on-hover',
        expandFeature: 'sidebar-mini-expand-feature',
        layoutFixed: 'fixed'
      };

      var events = {
        expanded: 'expanded.PushMenu',
        collapsed: 'collapsed.PushMenu'
      };

      // PushMenu Class Definition
      // =========================
      var PushMenu = function PushMenu(options) {
        this.options = options;
        this.init();
      };

      PushMenu.prototype.init = function () {
        if (this.options.expandOnHover || $('body').is(Selector.mini + Selector.layoutFixed)) {
          this.expandOnHover();
          $('body').addClass(ClassName.expandFeature);
        }

        $(Selector.contentWrapper).click(function () {
          // Enable hide menu when clicking on the content-wrapper on small screens
          if ($(window).width() <= this.options.collapseScreenSize && $('body').hasClass(ClassName.open)) {
            this.close();
          }
        }.bind(this));

        // __Fix for android devices
        $(Selector.searchInput).click(function (e) {
          e.stopPropagation();
        });
      };

      PushMenu.prototype.toggle = function () {
        var windowWidth = $(window).width();
        var isOpen = !$('body').hasClass(ClassName.collapsed);

        if (windowWidth <= this.options.collapseScreenSize) {
          isOpen = $('body').hasClass(ClassName.open);
        }

        if (!isOpen) {
          this.open();
        } else {
          this.close();
        }
      };

      PushMenu.prototype.open = function () {
        var windowWidth = $(window).width();

        if (windowWidth > this.options.collapseScreenSize) {
          $('body').removeClass(ClassName.collapsed).trigger($.events(events.expanded));
        } else {
          $('body').addClass(ClassName.open).trigger($.events(events.expanded));
        }
      };

      PushMenu.prototype.close = function () {
        var windowWidth = $(window).width();
        if (windowWidth > this.options.collapseScreenSize) {
          $('body').addClass(ClassName.collapsed).trigger($.events(events.collapsed));
        } else {
          $('body').removeClass(ClassName.open + ' ' + ClassName.collapsed) // eslint-disable-line prefer-template
          .trigger($.events(events.collapsed));
        }
      };

      PushMenu.prototype.expandOnHover = function () {
        $(Selector.mainSidebar).hover(function () {
          if ($('body').is(Selector.mini + Selector.collapsed) && $(window).width() > this.options.collapseScreenSize) {
            this.expand();
          }
        }.bind(this), function () {
          if ($('body').is(Selector.expanded)) {
            this.collapse();
          }
        }.bind(this));
      };

      PushMenu.prototype.expand = function () {
        setTimeout(function () {
          $('body').removeClass(ClassName.collapsed).addClass(ClassName.expanded);
        }, this.options.expandTransitionDelay);
      };

      PushMenu.prototype.collapse = function () {
        setTimeout(function () {
          $('body').removeClass(ClassName.expanded).addClass(ClassName.collapsed);
        }, this.options.expandTransitionDelay);
      };

      // PushMenu Plugin Definition
      // ==========================
      function Plugin(option) {
        return this.each(function () {
          var $this = $(this);
          var data = $this.data(dataKey);

          if (!data) {
            var options = $.extend({}, Default, $this.data(), (typeof option === 'undefined' ? 'undefined' : _typeof(option)) === 'object' && option); // eslint-disable-line vars-on-top
            $this.data(dataKey, data = new PushMenu(options));
          }

          if (option === 'toggle') data.toggle();
        });
      }

      var old = $.fn.PushMenu; // eslint-disable-line vars-on-top

      $.fn.PushMenu = Plugin;
      $.fn.PushMenu.Constructor = PushMenu;

      // No Conflict Mode
      // ================
      $.fn.PushMenu.noConflict = function () {
        $.fn.PushMenu = old;
        return this;
      };

      // Data API
      // ========
      $(document).on('click', Selector.button, function (e) {
        e.preventDefault();
        Plugin.call($(this), 'toggle');
      });
      $(window).on('load', function () {
        Plugin.call($(Selector.button));
      });
    }
  };
})(jQuery);
//# sourceMappingURL=script.js.map
