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
      // when there is updates on the fields
      $('input[id*="unit-price"]').on('keyup change', updateInvoiceValues);
      $('input[id*="quantity"]').on('keyup change', updateInvoiceValues);
      //$('input[id*="disscount"]').on('keyup change', updateInvoiceValues);
      $('input[id*="discount-percentage"]').on('keyup change', updateInvoiceValues);
      $('input[id*="field-adddis"]').on('keyup change', updateInvoiceValues);
      $('select[id*="subform-field-row-tax"]').on('keyup change', updateInvoiceValues);
      $('#edit-type-of').on('change', checkFieldConditions);

      // Calculate the fields needed.
      updateInvoiceValues();
      calculateRowsTotal();
    }
  };

  function updateInvoiceValues() {
    var max = getRows();
    for (var i = 0; i < max; i++) {
      var quantity = $('input[data-drupal-selector=edit-field-rows-' + i + '-subform-field-quantity-0-value]').val();
      var price = $('input[data-drupal-selector=edit-field-rows-' + i + '-subform-field-unit-price-0-value]').val();
      var adddis = $('#field-adddis-' + i).is(':checked');
      var discount = $('input[data-drupal-selector=edit-field-rows-' + i + '-subform-field-discount-percentage-0-value]').val();
      discount = discount == '' || discount == NaN || !adddis ? 0 : discount;
      if (quantity != '' && price != '') {
        var totalA = quantity * price;
        $('input[data-drupal-selector=edit-field-rows-' + i + '-subform-field-total-amount-0-value]').val(totalA);
        var tax_id = $('select[data-drupal-selector=edit-field-rows-' + i + '-subform-field-row-tax]').val();
        var discount = totalA * (discount / 100);
        $('input[data-drupal-selector=edit-field-rows-' + i + '-subform-field-row-discount-0-value]').val(discount);
        var subTotal = totalA - discount;
        $('input[data-drupal-selector=edit-field-rows-' + i + '-subform-field-subtotal-0-value]').val(subTotal);
        var totalWithTax = moduleNumberFormat(subTotal + calcTax(tax_id, subTotal));
        $('input[data-drupal-selector=edit-field-rows-' + i + '-subform-field-line-total-amount-0-value]').val(totalWithTax);
      }
    }
    calculateRowsTotal();
  }

  function calcTax(id, total) {
    var tax = drupalSettings.taxsObject[id];
    if (tax != null && tax != undefined) {
      return total * (tax['tax_percentage'] / 100);
    }
    return 0;
  }

  function moduleNumberFormat(value) {
    return parseFloat(parseFloat(value).toFixed(5));
  }

  // Calculate the general rows totals.
  function calculateRowsTotal() {
    var mt = $('input[id*="field-total-amount"]');
    var rd = $('input[id*="row-discount"]');
    var st = $('input[id*="subtotal"]');
    var mtl = $('input[id*="line-total-amount"]');//******
    var rows = mt.length;
    var totalSale = 0, totalDis = 0, totalTax = 0, totalInvoice = 0;
    for (var j = 0; j < rows; j++) {
      if ($(mt[j]).length > 0) {
        totalSale += moduleNumberFormat($(mt[j]).val() - $(rd[j]).val());
        totalDis += moduleNumberFormat($(rd[j]).val());
        totalTax += moduleNumberFormat($(mtl[j]).val() - $(st[j]).val());
      }
    }
    totalInvoice = totalSale + totalTax;
    console.log(`Total Sale: ${totalSale}, TotalDis ${totalDis}, TotalTax: ${totalTax}, TotalInvoice: ${totalInvoice}`);
    $('#edit-field-net-sale-0-value').val(totalSale);
    $('#edit-field-total-discount-0-value').val(totalDis);
    $('#edit-field-total-tax-0-value').val(totalTax);
    $('#edit-field-total-invoice-0-value').val(totalInvoice);
  }

  // Returns the rows quantity.
  function getRows() {
    var index = 0;
    while ($('input[data-drupal-selector=edit-field-rows-' + index + '-subform-field-unit-price-0-value]').length > 0) {
      index++;
    }
    return index;
  }

  function checkFieldConditions() {
    checkDependentField('field-payment-method', ['FE', 'TE']);
    checkDependentField('ref-type-of', ['NC', 'ND']);
    checkDependentField('ref-doc-key', ['NC', 'ND']);
    checkDependentField('ref-date', ['NC', 'ND']);
    checkDependentField('ref-code', ['NC', 'ND']);
    checkDependentField('ref-reason', ['NC', 'ND']);
  }

  function checkDependentField(name, types) {
    var classElement = '.field--name-' + name;
    var parent = $(classElement);
    var element = parent.hasClass('field--widget-options-buttons') ? parent.find('legend') : parent.find('label');
    var type = $('#edit-type-of').val();
    var isRequired = types.includes(type);

    if (isRequired && !element.hasClass('form-required')) {
      element.addClass('form-required');
    }
    if (!isRequired && element.hasClass('form-required')) {
      element.removeClass('form-required');
    }
  }

}(jQuery, drupalSettings));
