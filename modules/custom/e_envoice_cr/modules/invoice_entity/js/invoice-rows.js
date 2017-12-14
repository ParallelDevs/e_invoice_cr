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
      // calculate the fields needed
      calculateValues();
      calculateRowsTotal();

      function calculateValues() {
        // check if it's possible to calculate the current fields
        var max = getRows();
        for (var i = 0; i < max; i++) {
          var quantity = $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-cantidad-0-value]').val();
          var price = $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-preciounitario-0-value]').val();
          if (quantity != '' && price != '') {
            var totalA = quantity * price;
            $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-montototal-0-value]').val(totalA);
            var tax = $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-descuento-0-value]').val();
            tax = '0.' + tax;
            var totalWithTax = totalA + (totalA * tax);
            $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-subtotal-0-value]').val(totalWithTax);
            $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-monto-total-linea-0-value]').val(totalWithTax);
          }
        }
        // when there is updates on the fields
        $('input[id*="preciounitario"]').on('change', function () {
          var max = getRows();
          for (var i = 0; i < max; i++) {
            var quantity = $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-cantidad-0-value]').val();
            var price = $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-preciounitario-0-value]').val();
            if (quantity != '' && price != '') {
              var totalA = quantity * price;
              $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-montototal-0-value]').val(totalA);
              var tax = $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-descuento-0-value]').val();
              tax = '0.' + tax;
              var totalWithTax = totalA + (totalA * tax);
              $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-subtotal-0-value]').val(totalWithTax);
              $('input[data-drupal-selector=edit-field-filas-' + i + '-subform-field-monto-total-linea-0-value]').val(totalWithTax);
            }
          }
          calculateRowsTotal();
        });
      }

      // calculate the general rows totals
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

      // returns the rows quantity
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