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
    attach: function (context, settings) { // eslint-disable-line no-unused-vars
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
      var PushMenu = function (options) {
        this.options = options;
        this.init();
      };

      PushMenu.prototype.init = function () {
        if (this.options.expandOnHover
          || ($('body').is(Selector.mini + Selector.layoutFixed))) {
          this.expandOnHover();
          $('body').addClass(ClassName.expandFeature);
        }

        $(Selector.contentWrapper).click(function () {
          // Enable hide menu when clicking on the content-wrapper on small screens
          if ($(window).width() <= this.options.collapseScreenSize && $('body')
              .hasClass(ClassName.open)) {
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
          $('body').removeClass(ClassName.collapsed)
            .trigger($.Event(events.expanded));
        } else {
          $('body').addClass(ClassName.open)
            .trigger($.Event(events.expanded));
        }
      };

      PushMenu.prototype.close = function () {
        var windowWidth = $(window).width();
        if (windowWidth > this.options.collapseScreenSize) {
          $('body').addClass(ClassName.collapsed)
            .trigger($.Event(events.collapsed));
        } else {
          $('body').removeClass(ClassName.open + ' ' + ClassName.collapsed) // eslint-disable-line prefer-template
            .trigger($.Event(events.collapsed));
        }
      };

      PushMenu.prototype.expandOnHover = function () {
        $(Selector.mainSidebar).hover(function () {
          if ($('body').is(Selector.mini + Selector.collapsed)
            && $(window).width() > this.options.collapseScreenSize) {
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
          $('body').removeClass(ClassName.collapsed)
            .addClass(ClassName.expanded);
        }, this.options.expandTransitionDelay);
      };

      PushMenu.prototype.collapse = function () {
        setTimeout(function () {
          $('body').removeClass(ClassName.expanded)
            .addClass(ClassName.collapsed);
        }, this.options.expandTransitionDelay);
      };

      // PushMenu Plugin Definition
      // ==========================
      function Plugin(option) {
        return this.each(function () {
          var $this = $(this);
          var data = $this.data(dataKey);

          if (!data) {
            var options = $.extend({}, Default, $this.data(), typeof option === 'object' && option); // eslint-disable-line vars-on-top
            $this.data(dataKey, (data = new PushMenu(options)));
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
}(jQuery));

