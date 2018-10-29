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
      $('#edit-field-currency').on('change', updateCurrencySuffixes);

      // Calculate the fields needed.
      updateInvoiceValues();
      calculateRowsTotal();
      checkFieldConditions();
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
    Object.keys(drupalSettings.dependentFields).forEach(function (field) {
      checkDependentField(field.replace(/_/g, '-'), drupalSettings.dependentFields[field]);
    });
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

  function updateCurrencySuffixes() {
    var currency = $('#edit-field-currency').val();
    var symbol = drupalSettings.currencyInfo[currency]['symbol'];
    $('input[type="number"]:not([data-drupal-selector*=subform-field-quantity], [data-drupal-selector*=subform-field-discount-percentage])').each(
      function () {
        var element = $(this).siblings('label');
        var current = element.text();
        var original = hasCurrency(element) ? current.substr(0, current.length - 2) : current;
        element.text(original + ' ' + symbol);
      }
    );
  }

  function hasCurrency(element) {
    var hasIt = false;
    var char = element.text().slice(-1);
    Object.keys(drupalSettings.currencyInfo).forEach(function (key) {
      hasIt = hasIt || char == drupalSettings.currencyInfo[key].symbol;
    });
    return hasIt;
  }

  $("#edit-type-of").on("change", function (event) {
    $.ajax({
      url: '/consecutive',
      type: 'POST',
      data: {
        type: $('#edit-type-of').val()
      },
      success: function (response) {
        $('#edit-field-consecutive-number-0-value').val(response);
      }
    });
  });

}(jQuery, drupalSettings));
