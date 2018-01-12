<?php
namespace Drupal\e_invoice_cr;

use DOMDocument;

/**
 * .
 */
class XMLGenerator {

  /**
   * {@inheritdoc}
   */
  public function generateInvoiceXML($general_data, $client, $emitter, $rows) {
    $client_zip_code = $client->field_direccion_->getValue();
    $settings = \Drupal::config('e_invoice_cr.settings');
    $currency = $settings->get('currency');
    $total_services = 0;
    $total_items = 0;

    // Build the xml code.
    $xml_doc = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $xml_doc .= '<FacturaElectronica xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica" xmlns:ns2="http://www.w3.org/2000/09/xmldsig#">' . "\n";
    $xml_doc .= '<Clave>' . $general_data['key'] . '</Clave>' . "\n";
    $xml_doc .= '<NumeroConsecutivo>' . $general_data['consecutive'] . '</NumeroConsecutivo>' . "\n";
    $xml_doc .= '<FechaEmision>' . $general_data['date'] . '</FechaEmision>' . "\n";
    $xml_doc .= '<Emisor>' . "\n"; // start 'Emisor'
    $xml_doc .= '<Nombre>' . $emitter['name'] . '</Nombre>' . "\n";
    $xml_doc .= '<Identificacion>' . "\n";
    $xml_doc .= '<Tipo>' . $emitter['id']['type'] . '</Tipo>' . "\n";
    $xml_doc .= '<Numero>' . $emitter['id']['number'] . '</Numero>' . "\n";
    $xml_doc .= '</Identificacion>' . "\n";
    $xml_doc .= '<NombreComercial>' . $emitter['commercialName'] . '</NombreComercial>' . "\n";
    $xml_doc .= '<Ubicacion>' . "\n";
    $xml_doc .= '<Provincia>' . $emitter['address']['data1'] . '</Provincia>' . "\n";
    $xml_doc .= '<Canton>' . $emitter['address']['data2'] . '</Canton>' . "\n";
    $xml_doc .= '<Distrito>' . $emitter['address']['data3'] . '</Distrito>' . "\n";
    $xml_doc .= '<Barrio>' . $emitter['address']['data4'] . '</Barrio>' . "\n";
    $xml_doc .= '<OtrasSenas>' . $emitter['address']['data5'] . '</OtrasSenas>' . "\n";
    $xml_doc .= '</Ubicacion>' . "\n";
    $xml_doc .= '<Telefono>' . "\n";
    $xml_doc .= '<CodigoPais>' . $emitter['phone']['code'] . '</CodigoPais>' . "\n";
    $xml_doc .= '<NumTelefono>' . $emitter['phone']['number'] . '</NumTelefono>' . "\n";
    $xml_doc .= '</Telefono>' . "\n";
    $xml_doc .= '<Fax>' . "\n";
    $xml_doc .= '<CodigoPais>' . $emitter['fax']['code'] . '</CodigoPais>' . "\n";
    $xml_doc .= '<NumTelefono>' . $emitter['fax']['number'] . '</NumTelefono>' . "\n";
    $xml_doc .= '</Fax>' . "\n";
    $xml_doc .= '<CorreoElectronico>' . $emitter['email'] . '</CorreoElectronico>' . "\n";
    $xml_doc .= '</Emisor>' . "\n";
    $xml_doc .= '<Receptor>' . "\n"; // starts 'Receptor'
    $xml_doc .= '<Nombre>' . $client->get('name')->value . '</Nombre>' . "\n";
    $xml_doc .= '<Identificacion>' . "\n";
    $xml_doc .= '<Tipo>' . $client->get('field_tipo_de_identificacion')->value . '</Tipo>' . "\n";
    $xml_doc .= '<Numero>' . $client->get('field_intensificacion')->value . '</Numero>' . "\n";
    $xml_doc .= '</Identificacion>' . "\n";
    $xml_doc .= '<IdentificacionExtranjero>' . $client->get('field_intensificacion_ex')->value . '</IdentificacionExtranjero>' . "\n";
    $xml_doc .= '<NombreComercial>' . $client->get('field_nombrecomercial')->value . '</NombreComercial>' . "\n";
    $xml_doc .= '<Ubicacion>' . "\n";
    $xml_doc .= '<Provincia>' . substr($client_zip_code[0]['zipcode'], 0, 1) . '</Provincia>' . "\n";
    $xml_doc .= '<Canton>' . substr($client_zip_code[0]['zipcode'], 1, 2) . '</Canton>' . "\n";
    $xml_doc .= '<Distrito>' . substr($client_zip_code[0]['zipcode'], 3, 5) . '</Distrito>' . "\n";
    $xml_doc .= '<Barrio>01</Barrio>' . "\n";// no supported at the moment (*)
    $xml_doc .= '<OtrasSenas>' . $client_zip_code[0]['additionalinfo'] . '</OtrasSenas>' . "\n";
    $xml_doc .= '</Ubicacion>' . "\n";
    $xml_doc .= '<Telefono>' . "\n";
    $xml_doc .= '<CodigoPais>' . substr($client->get('field_telefono')->value, 0, 3) . '</CodigoPais>' . "\n";
    $xml_doc .= '<NumTelefono>' . substr($client->get('field_telefono')->value, 3 ) . '</NumTelefono>' . "\n";
    $xml_doc .= '</Telefono>' . "\n";
    $xml_doc .= '<Fax>' . "\n";
    $xml_doc .= '<CodigoPais>' . substr($client->get('field_fax')->value, 0, 3) . '</CodigoPais>' . "\n";
    $xml_doc .= '<NumTelefono>' . substr($client->get('field_fax')->value, 4 ) . '</NumTelefono>' . "\n";
    $xml_doc .= '</Fax>' . "\n";
    $xml_doc .= '<CorreoElectronico>' . $emitter['email'] . '</CorreoElectronico>' . "\n";
    $xml_doc .= '</Receptor>' . "\n";
    $xml_doc .= '<CondicionVenta>' . $general_data['condition'] . '</CondicionVenta>' . "\n";
    $xml_doc .= '<PlazoCredito>' . $general_data['p_credit'] . '</PlazoCredito>' . "\n";
    $xml_doc .= '<MedioPago>' . $general_data['pay_type'] . '</MedioPago>' . "\n";
    $xml_doc .= '<DetalleServicio>' . "\n";
    // Print the rows in here.
    foreach ($rows as $index => $item) {
      if (is_numeric ($index)) {
        $count = $index + 1;
        $values = $item['subform'];
        $discount = $values['field_add_discount']['value'] ? $values['field_row_discount'][0]['value'] : 0;
        $discount_reason = $values['field_add_discount']['value'] ? $values['field_discount_reason'][0]['value'] : "";
        $tax = (float)($values['field_subtotal'][0]['value']) * (float)('0.' . $values['field_descuento'][0]['value']);
        // Continue building the xml.
        $xml_doc .= '<LineaDetalle>' . "\n";
        $xml_doc .= '<NumeroLinea>' . $count . '</NumeroLinea>' . "\n";
        $xml_doc .= '<Codigo>' . "\n";
        $xml_doc .= '<Tipo>' . $values['field_tipo'][0]['value'] . '</Tipo>' . "\n";
        $xml_doc .= '<Codigo>' . $values['field_codigo'][0]['value'] . '</Codigo>' . "\n";
        $xml_doc .= '</Codigo>' . "\n";
        $xml_doc .= '<Cantidad>' . $values['field_cantidad'][0]['value'] . '</Cantidad>' . "\n";
        $xml_doc .= '<UnidadMedida></UnidadMedida>' . "\n"; // No supported at the moment (*).
        $xml_doc .= '<UnidadMedidaComercial></UnidadMedidaComercial>' . "\n";
        $xml_doc .= '<Detalle>' . $values['field_detalle'][0]['value'] . '</Detalle>' . "\n";
        $xml_doc .= '<PrecioUnitario>' . $values['field_preciounitario'][0]['value'] . '</PrecioUnitario>' . "\n";
        $xml_doc .= '<MontoTotal>' . $values['field_montototal'][0]['value'] . '</MontoTotal>' . "\n";
        $xml_doc .= '<MontoDescuento>0</MontoDescuento>' . "\n"; // No supported at the moment (*).
        $xml_doc .= '<NaturalezaDescuento>'. $discount_reason .'</NaturalezaDescuento>' . "\n"; // No supported at the moment (*).
        $xml_doc .= '<SubTotal>' . $values['field_subtotal'][0]['value'] . '</SubTotal>' . "\n";
        $xml_doc .= '<Impuesto>' . "\n";
        $xml_doc .= '<Codigo>01</Codigo>' . "\n"; // No supported at the moment (*).
        $xml_doc .= '<Tarifa>' . $values['field_descuento'][0]['value'] . '</Tarifa>' . "\n";
        $xml_doc .= '<Monto>' . $tax . '</Monto>' . "\n";
        $xml_doc .= '<Exoneracion>' . "\n"; // No supported at the moment (*).
        $xml_doc .= '<TipoDocumento>04</TipoDocumento>' . "\n"; // No supported at the moment (*).
        $xml_doc .= '<NumeroDocumento>XXYZ</NumeroDocumento>' . "\n"; // No supported at the moment (*).
        $xml_doc .= '<NombreInstitucion>Los Patos S.A.</NombreInstitucion>' . "\n"; // No supported at the moment (*).
        $xml_doc .= '<FechaEmision>2001-12-17T09:30:47Z</FechaEmision>' . "\n"; // No supported at the moment (*).
        $xml_doc .= '<MontoImpuesto>0</MontoImpuesto>' . "\n"; // No supported at the moment (*).
        $xml_doc .= '<PorcentajeCompra>0</PorcentajeCompra>' . "\n"; // No supported at the moment (*).
        $xml_doc .= '</Exoneracion>' . "\n"; // No supported at the moment (*).
        $xml_doc .= '</Impuesto>' . "\n";
        $xml_doc .= '<MontoTotalLinea>' . $values['field_monto_total_linea'][0]['value'] . '</MontoTotalLinea>' . "\n";
        $xml_doc .= '</LineaDetalle>' . "\n";
        $total_items = $total_items + (int)$values['field_cantidad'][0]['value'];
        $total_services++;
      }
    }
    $xml_doc .= '</DetalleServicio>' . "\n";
    $xml_doc .= '<ResumenFactura>' . "\n";
    $xml_doc .= '<CodigoMoneda>' . $currency . '</CodigoMoneda>' . "\n";
    $xml_doc .= '<TipoCambio>0</TipoCambio>' . "\n"; // No supported at the moment (*).
    $xml_doc .= '<TotalServGravados>' . $total_services . '</TotalServGravados>' . "\n";
    $xml_doc .= '<TotalServExentos>0</TotalServExentos>' . "\n"; // No supported at the moment (*).
    $xml_doc .= '<TotalMercanciasGravadas>' . $total_items . '</TotalMercanciasGravadas>' . "\n";
    $xml_doc .= '<TotalMercanciasExentas>0</TotalMercanciasExentas>' . "\n"; // No supported at the moment (*).
    $xml_doc .= '<TotalGravado>' . $total_items . '</TotalGravado>' . "\n";
    $xml_doc .= '<TotalExento>0</TotalExento>' . "\n"; // No supported at the moment (*).
    $xml_doc .= '<TotalVenta>' . $general_data['t_sale'] . '</TotalVenta>' . "\n";
    $xml_doc .= '<TotalDescuentos>0</TotalDescuentos>' . "\n"; // No supported at the moment (*).
    $xml_doc .= '<TotalVentaNeta>' . $general_data['t_sale'] . '</TotalVentaNeta>' . "\n";
    $xml_doc .= '<TotalImpuesto>' . $general_data['t_tax'] . '</TotalImpuesto>' . "\n";
    $xml_doc .= '<TotalComprobante>' . $general_data['t_invoice'] . '</TotalComprobante>' . "\n";
    $xml_doc .= '</ResumenFactura>' . "\n";
    $xml_doc .= '<InformacionReferencia>' . "\n";
    $xml_doc .= '<TipoDoc>01</TipoDoc>' . "\n"; // 01 because it's an invoice.
    $xml_doc .= '<Numero>' . $general_data['key'] . '</Numero>' . "\n";
    $xml_doc .= '<FechaEmision>' . $general_data['date'] . '</FechaEmision>' . "\n";
    $xml_doc .= '<Codigo>02</Codigo>' . "\n";
    $xml_doc .= '<Razon>a</Razon>' . "\n";
    $xml_doc .= '</InformacionReferencia>' . "\n";
    $xml_doc .= '<Normativa>' . "\n";
    $xml_doc .= '<NumeroResolucion>DGT-R-48-2016</NumeroResolucion>' . "\n";
    $xml_doc .= '<FechaResolucion>12-12-2016 08:08:12</FechaResolucion>' . "\n";
    $xml_doc .= '</Normativa>' . "\n";
    $xml_doc .= '<Otros>' . "\n";
    $xml_doc .= '<OtroTexto></OtroTexto>' . "\n";
    $xml_doc .= '</Otros>' . "\n";
    $xml_doc .= '</FacturaElectronica>' . "\n";

    // Create the xml document.
    $doc = new DOMDocument();
    $doc->loadXML($xml_doc);
    return $doc;
  }
}
