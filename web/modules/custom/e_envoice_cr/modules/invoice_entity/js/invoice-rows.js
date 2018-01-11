/**
 * @file
 * Provides the js functionality to the module
 */

/**
 * Implements Drupal.behaviors
 */
(function ($, drupalSettings) {
  'use strict';
  Drupal.behaviors.geneteFields = {
    attach: function(context, settings) {
      // Calculate the fields needed.
      calculateValues();
      calculateRowsTotal();

      function calculateValues() {
        // Check if it's possible to calculate the current fields.
        var max = getRows();
        for (var i = 0; i < max; i++) {
          var quantity = $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-cantidad-0-value]').val();
          var price = $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-preciounitario-0-value]').val();
          if (quantity != '' && price != '') {
            var totalA = quantity * price;
            $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-montototal-0-value]').val(totalA);
            var tax_id = $('select[data-drupal-selector=edit-field-filas-' + i + '-subform-field-impuesto]').val();
            var tax = parseFloat(searchTax(tax_id));
            var totalWithTax = totalA + ((tax/100)*totalA);
            $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-subtotal-0-value]').val(totalWithTax);
            $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-monto-total-linea-0-value]').val(totalWithTax);
          }
        }
        // When there is updates on the fields.
        $('input[id*="preciounitario"]').on('change', function () {
          var max = getRows();
          for (var i = 0; i < max; i++) {
            var quantity = $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-cantidad-0-value]').val();
            var price = $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-preciounitario-0-value]').val();
            if (quantity != '' && price != '') {
              var totalA = quantity * price;
              $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-montototal-0-value]').val(totalA);
              var tax_id = $('select[data-drupal-selector=edit-field-filas-' + i + '-subform-field-impuesto]').val();
              var tax = parseFloat(searchTax(tax_id));
              var totalWithTax = totalA + ((tax/100)*totalA);
              $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-subtotal-0-value]').val(totalWithTax);
              $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-monto-total-linea-0-value]').val(totalWithTax);
            }
          }
          calculateRowsTotal();
        });

        // When the tax changes.
        for (var i = 0; i < max; i++) {
          $('select[data-drupal-selector=edit-field-filas-' + i + '-subform-field-impuesto]').on('change', function () {
            for (var i = 0; i < max; i++) {
              var quantity = $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-cantidad-0-value]').val();
              var price = $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-preciounitario-0-value]').val();
              if (quantity != '' && price != '') {
                var totalA = quantity * price;
                $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-montototal-0-value]').val(totalA);
                var tax_id = $('select[data-drupal-selector=edit-field-filas-' + i + '-subform-field-impuesto]').val();
                var tax = parseFloat(searchTax(tax_id));
                var totalWithTax = totalA + ((tax/100)*totalA);
                $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-subtotal-0-value]').val(totalWithTax);
                $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-monto-total-linea-0-value]').val(totalWithTax);
              }
            }
            calculateRowsTotal();
          });
        }
      }

      // Gets the tax percentage
      function searchTax(id) {
        if (id == '_none') {
          return 0;
        } else {
          var taxs = drupalSettings.taxsObject;
          for (var i = 0; i < taxs.length; i++) {
            if (taxs[""+i].id == id) {
              return taxs[""+i].percentage;
            }
          }
        }
      }

      // Calculate the general rows totals.
      function calculateRowsTotal() {
        var mt = $('input[id*="montototal"]');
        var st = $('input[id*="subtotal"]');
        var mtl = $('input[id*="monto-total-linea"]');
        var rows = mt.length;
        var totalSale = 0, totalTax = 0, totalInvoice = 0;
        for (var j = 0; j < rows; j++) {
          if ($(mt[j]).length > 0) {
            totalSale = totalSale + parseFloat($(mt[j]).val());
            totalTax = totalTax + parseFloat($(st[j]).val() - $(mt[j]).val());
            totalInvoice = totalInvoice + parseFloat($(mtl[j]).val());
          }
        }
        $('#edit-field-total-ventaneta-0-value').val(totalSale);
        $('#edit-field-total-impuesto-0-value').val(totalTax);
        $('#edit-field-totalcomprobante-0-value').val(totalInvoice);
      }

      // Returns the rows quantity.
      function getRows() {
        var index = 0;
        while ($('input[data-drupal-selector=edit-field-filas-' + index + '-subform-field-preciounitario-0-value]').length > 0) {
          index++;
        }
        return index;
      }
    }
  };
}(jQuery, drupalSettings));