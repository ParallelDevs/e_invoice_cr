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
    $total_items_with_tax = 0;
    $total_items_without_tax = 0;

    // Build the xml code.
    $xml_doc = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>";
    $xml_doc .= "<FacturaElectronica xmlns=\"https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica\" xmlns:ns2=\"http://www.w3.org/2000/09/xmldsig#\">" . "\n";
    $xml_doc .= "\t<Clave>" . $general_data['key'] . "</Clave>" . "\n";
    $xml_doc .= "\t<NumeroConsecutivo>" . $general_data['consecutive'] . "</NumeroConsecutivo>" . "\n";
    $xml_doc .= "\t<FechaEmision>" . $general_data['date'] . "</FechaEmision>" . "\n";
    $xml_doc .= "\t<Emisor>" . "\n"; // start 'Emisor'
    $xml_doc .= "\t\t<Nombre>" . $emitter['name'] . "</Nombre>" . "\n";
    $xml_doc .= "\t\t<Identificacion>" . "\n";
    $xml_doc .= "\t\t\t<Tipo>" . $emitter['id']['type'] . "</Tipo>" . "\n";
    $xml_doc .= "\t\t\t<Numero>" . $emitter['id']['number'] . "</Numero>" . "\n";
    $xml_doc .= "\t\t</Identificacion>" . "\n";
    $xml_doc .= "\t\t<NombreComercial>" . $emitter['commercialName'] . "</NombreComercial>" . "\n";
    $xml_doc .= "\t\t<Ubicacion>" . "\n";
    $xml_doc .= "\t\t\t<Provincia>" . $emitter['address']['data1'] . "</Provincia>" . "\n";
    $xml_doc .= "\t\t\t<Canton>" . $emitter['address']['data2'] . "</Canton>" . "\n";
    $xml_doc .= "\t\t\t<Distrito>" . $emitter['address']['data3'] . "</Distrito>" . "\n";
    $xml_doc .= "\t\t\t<Barrio>" . $emitter['address']['data4'] . "</Barrio>" . "\n";
    $xml_doc .= "\t\t\t<OtrasSenas>" . $emitter['address']['data5'] . "</OtrasSenas>" . "\n";
    $xml_doc .= "\t\t</Ubicacion>" . "\n";
    $xml_doc .= "\t\t<Telefono>" . "\n";
    $xml_doc .= "\t\t\t<CodigoPais>" . $emitter['phone']['code'] . "</CodigoPais>" . "\n";
    $xml_doc .= "\t\t\t<NumTelefono>" . $emitter['phone']['number'] . "</NumTelefono>" . "\n";
    $xml_doc .= "\t\t</Telefono>" . "\n";
    $xml_doc .= "\t\t<Fax>" . "\n";
    $xml_doc .= "\t\t\t<CodigoPais>" . $emitter['fax']['code'] . "</CodigoPais>" . "\n";
    $xml_doc .= "\t\t\t<NumTelefono>" . $emitter['fax']['number'] . "</NumTelefono>" . "\n";
    $xml_doc .= "\t\t</Fax>" . "\n";
    $xml_doc .= "\t\t<CorreoElectronico>" . $emitter['email'] . "</CorreoElectronico>" . "\n";
    $xml_doc .= "\t</Emisor>" . "\n";
    $xml_doc .= "\t<Receptor>" . "\n"; // starts 'Receptor'
    $xml_doc .= "\t\t<Nombre>" . $client->get('name')->value . "</Nombre>" . "\n";
    $xml_doc .= "\t\t<Identificacion>" . "\n";
    $xml_doc .= "\t\t\t<Tipo>" . $client->get('field_tipo_de_identificacion')->value . "</Tipo>" . "\n";
    $xml_doc .= "\t\t\t<Numero>" . $client->get('field_intensificacion')->value . "</Numero>" . "\n";
    $xml_doc .= "\t\t</Identificacion>" . "\n";
    $xml_doc .= "\t\t<IdentificacionExtranjero>" . $client->get('field_intensificacion_ex')->value . "</IdentificacionExtranjero>" . "\n";
    $xml_doc .= "\t\t<NombreComercial>" . $client->get('field_nombrecomercial')->value . "</NombreComercial>" . "\n";
    $xml_doc .= "\t\t<Ubicacion>" . "\n";
    $xml_doc .= "\t\t\t<Provincia>" . substr($client_zip_code[0]['zipcode'], 0, 1) . "</Provincia>" . "\n";
    $xml_doc .= "\t\t\t<Canton>" . substr($client_zip_code[0]['zipcode'], 1, 2) . "</Canton>" . "\n";
    $xml_doc .= "\t\t\t<Distrito>" . substr($client_zip_code[0]['zipcode'], 3, 5) . "</Distrito>" . "\n";
    $xml_doc .= "\t\t\t<Barrio>01</Barrio>" . "\n";// no supported at the moment (*)
    $xml_doc .= "\t\t\t<OtrasSenas>" . $client_zip_code[0]['additionalinfo'] . "</OtrasSenas>" . "\n";
    $xml_doc .= "\t\t</Ubicacion>" . "\n";
    $xml_doc .= "\t\t<Telefono>" . "\n";
    $xml_doc .= "\t\t\t<CodigoPais>" . substr($client->get('field_telefono')->value, 0, 3) . "</CodigoPais>" . "\n";
    $xml_doc .= "\t\t\t<NumTelefono>" . substr($client->get('field_telefono')->value, 3 ) . "</NumTelefono>" . "\n";
    $xml_doc .= "\t\t</Telefono>" . "\n";
    $xml_doc .= "\t\t<Fax>" . "\n";
    $xml_doc .= "\t\t\t<CodigoPais>" . substr($client->get('field_fax')->value, 0, 3) . "</CodigoPais>" . "\n";
    $xml_doc .= "\t\t\t<NumTelefono>" . substr($client->get('field_fax')->value, 3 ) . "</NumTelefono>" . "\n";
    $xml_doc .= "\t\t</Fax>" . "\n";
    $xml_doc .= "\t\t<CorreoElectronico>" . $client->get('field_correo_electronico')->value . "</CorreoElectronico>" . "\n";
    $xml_doc .= "\t</Receptor>" . "\n";
    $xml_doc .= "\t<CondicionVenta>" . $general_data['condition'] . "</CondicionVenta>" . "\n";
    $xml_doc .= "\t<PlazoCredito>" . $general_data['p_credit'] . "</PlazoCredito>" . "\n";
    $xml_doc .= "\t<MedioPago>" . $general_data['pay_type'] . "</MedioPago>" . "\n";
    $xml_doc .= "\t<DetalleServicio>" . "\n";
    // Print the rows in here.
    foreach ($rows as $index => $item) {
      if (is_numeric ($index)) {
        $count = $index + 1;
        $values = $item['subform'];
        $discount = $values['field_add_discount']['value'] ? $values['field_row_discount'][0]['value'] : 0;
        $discount_reason = $values['field_add_discount']['value'] ? $values['field_discount_reason'][0]['value'] : "";
        $tax_id = $values['field_impuesto'][0]['target_id'];
        $entity_manager = \Drupal::entityManager();
        $tax = $entity_manager->getStorage('tax_entity')->load($tax_id);
        $tax_mount = ($tax->get('field_tax_percentage')->value/100)*$values['field_subtotal'][0]['value'];
        // Continue building the xml.
        $xml_doc .= "\t\t<LineaDetalle>" . "\n";
        $xml_doc .= "\t\t\t<NumeroLinea>" . $count . "</NumeroLinea>" . "\n";
        $xml_doc .= "\t\t\t<Codigo>" . "\n";
        $xml_doc .= "\t\t\t\t<Tipo>" . $values['field_tipo'][0]['value'] . "</Tipo>" . "\n";
        $xml_doc .= "\t\t\t\t<Codigo>" . $values['field_codigo'][0]['value'] . "</Codigo>" . "\n";
        $xml_doc .= "\t\t\t</Codigo>" . "\n";
        $xml_doc .= "\t\t\t<Cantidad>" . $values['field_cantidad'][0]['value'] . "</Cantidad>" . "\n";
        $xml_doc .= "\t\t\t<UnidadMedida>" . $values['field_unit_measure'][0]['value'] . "</UnidadMedida>" . "\n";
        $xml_doc .= "\t\t\t<UnidadMedidaComercial>" . $values['field_another_unit_measure'][0]['value'] . "</UnidadMedidaComercial>" . "\n";
        $xml_doc .= "\t\t\t<Detalle>" . $values['field_detalle'][0]['value'] . "</Detalle>" . "\n";
        $xml_doc .= "\t\t\t<PrecioUnitario>" . $values['field_preciounitario'][0]['value'] . "</PrecioUnitario>" . "\n";
        $xml_doc .= "\t\t\t<MontoTotal>" . $values['field_montototal'][0]['value'] . "</MontoTotal>" . "\n";
        $xml_doc .= "\t\t\t<MontoDescuento>" . $discount . "</MontoDescuento>" . "\n";
        $xml_doc .= "\t\t\t<NaturalezaDescuento>". $discount_reason ."</NaturalezaDescuento>" . "\n";
        $xml_doc .= "\t\t\t<SubTotal>" . round($values['field_subtotal'][0]['value'], 5) . "</SubTotal>" . "\n";
        $xml_doc .= "\t\t\t<Impuesto>" . "\n";
        $xml_doc .= "\t\t\t\t<Codigo>" . $tax->get('field_tax_type')->value . "</Codigo>" . "\n";
        $xml_doc .= "\t\t\t\t<Tarifa>" . $tax->get('field_tax_percentage')->value . "</Tarifa>" . "\n";
        $xml_doc .= "\t\t\t\t<Monto>" . $tax_mount . '</Monto>' . "\n";
        $xml_doc .= "\t\t\t\t<Exoneracion>" . "\n"; // No supported at the moment (*).
        $xml_doc .= "\t\t\t\t\t<TipoDocumento>01</TipoDocumento>" . "\n"; // No supported at the moment (*).
        $xml_doc .= "\t\t\t\t\t<NumeroDocumento>01</NumeroDocumento>" . "\n"; // No supported at the moment (*).
        $xml_doc .= "\t\t\t\t\t<NombreInstitucion>asdas</NombreInstitucion>" . "\n"; // No supported at the moment (*).
        $xml_doc .= "\t\t\t\t\t<FechaEmision>2017-11-27T09:55:05-06:00</FechaEmision>" . "\n"; // No supported at the moment (*).
        $xml_doc .= "\t\t\t\t\t<MontoImpuesto>12</MontoImpuesto>" . "\n"; // No supported at the moment (*).
        $xml_doc .= "\t\t\t\t\t<PorcentajeCompra>2</PorcentajeCompra>" . "\n"; // No supported at the moment (*).
        $xml_doc .= "\t\t\t\t</Exoneracion>" . "\n"; // No supported at the moment (*).
        $xml_doc .= "\t\t\t</Impuesto>" . "\n";
        $xml_doc .= "\t\t\t<MontoTotalLinea>" . round($values['field_monto_total_linea'][0]['value'], 5) . "</MontoTotalLinea>" . "\n";
        $xml_doc .= "\t\t</LineaDetalle>" . "\n";
        // Save the ones with tax
        if ($tax_mount !== "" && $tax_mount > 0 && $tax_mount !== NULL) {
          $total_items_with_tax = $total_items_with_tax + (int)$values['field_cantidad'][0]['value'];
          $total_services++;
        } else {
          // Save the ones without tax
          $total_items_without_tax = $total_items_without_tax + (int)$values['field_cantidad'][0]['value'];
          $total_services++;
        }
      }
    }
    $xml_doc .= "\t</DetalleServicio>" . "\n";
    $xml_doc .= "\t<ResumenFactura>" . "\n";
    $xml_doc .= "\t\t<CodigoMoneda>" . strtoupper($currency ) . "</CodigoMoneda>" . "\n";
    $xml_doc .= "\t\t<TipoCambio>0</TipoCambio>" . "\n"; // No supported at the moment (*).
    $xml_doc .= "\t\t<TotalServGravados>" . $total_items_with_tax . "</TotalServGravados>" . "\n";
    $xml_doc .= "\t\t<TotalServExentos>" . $total_items_without_tax . "</TotalServExentos>" . "\n"; // No supported at the moment (*).
    $xml_doc .= "\t\t<TotalMercanciasGravadas>" . $total_items_with_tax . "</TotalMercanciasGravadas>" . "\n";
    $xml_doc .= "\t\t<TotalMercanciasExentas>" . $total_items_without_tax . "</TotalMercanciasExentas>" . "\n"; // No supported at the moment (*).
    $xml_doc .= "\t\t<TotalGravado>" . $total_items_with_tax . "</TotalGravado>" . "\n";
    $xml_doc .= "\t\t<TotalExento>" . $total_items_without_tax . "</TotalExento>" . "\n"; // No supported at the moment (*).
    $xml_doc .= "\t\t<TotalVenta>" . $general_data['t_sale'] . "</TotalVenta>" . "\n";
    $xml_doc .= "\t\t<TotalDescuentos>0</TotalDescuentos>" . "\n"; // No supported at the moment (*).
    $xml_doc .= "\t\t<TotalVentaNeta>" . $general_data['t_sale'] . "</TotalVentaNeta>" . "\n";
    $xml_doc .= "\t\t<TotalImpuesto>" . $general_data['t_tax'] . "</TotalImpuesto>" . "\n";
    $xml_doc .= "\t\t<TotalComprobante>" . $general_data['t_invoice'] . "</TotalComprobante>" . "\n";
    $xml_doc .= "\t</ResumenFactura>" . "\n";
    $xml_doc .= "\t<InformacionReferencia>" . "\n";
    $xml_doc .= "\t\t<TipoDoc>01</TipoDoc>" . "\n"; // 01 because it's an invoice.
    $xml_doc .= "\t\t<Numero>" . $general_data['key'] . "</Numero>" . "\n";
    $xml_doc .= "\t\t<FechaEmision>" . $general_data['date'] . "</FechaEmision>" . "\n";
    $xml_doc .= "\t\t<Codigo>02</Codigo>" . "\n";
    $xml_doc .= "\t\t<Razon>a</Razon>" . "\n";
    $xml_doc .= "\t</InformacionReferencia>" . "\n";
    $xml_doc .= "\t<Normativa>" . "\n";
    $xml_doc .= "\t\t<NumeroResolucion>DGT-R-48-2016</NumeroResolucion>" . "\n";
    $xml_doc .= "\t\t<FechaResolucion>12-12-2016 08:08:12</FechaResolucion>" . "\n";
    $xml_doc .= "\t</Normativa>" . "\n";
    $xml_doc .= "\t<Otros>" . "\n";
    $xml_doc .= "\t\t<OtroTexto></OtroTexto>" . "\n";
    $xml_doc .= "\t</Otros>" . "\n";
    $xml_doc .= "</FacturaElectronica>" . "\n";

    // Create the xml document.
    $doc = new DOMDocument();
    $doc->loadXML($xml_doc);
    return $doc;
  }
}
