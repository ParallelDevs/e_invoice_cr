<?php
namespace Drupal\e_invoice_cr;


/**
 * .
 */
class XMLGenerator {

  /**
   * {@inheritdoc}
   */
  public function generateInvoiceXML($general_data, $client, $emitter, $rows) {
    $zip_code = $client->values['field_direccion_']['x-default'][0]['zipcode'];
    $as = $client->get('field_direccion_');
    // build the xml code
    $xml_doc = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $xml_doc .= '<FacturaElectronica xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica" xmlns:ns2="http://www.w3.org/2000/09/xmldsig#">';
    $xml_doc .= '<Clave>' . $general_data['key'] . '</Clave>';
    $xml_doc .= '<NumeroConsecutivo>' . $general_data['consecutive'] . '</NumeroConsecutivo>';
    $xml_doc .= '<FechaEmision>' . $general_data['date'] . '</FechaEmision>';
    $xml_doc .= '<Emisor>'; // start 'Emisor'
    $xml_doc .= '<Nombre>' . $emitter['name'] . '</Nombre>';
    $xml_doc .= '<Identificacion>';
    $xml_doc .= '<Tipo>' . $emitter['id']['type'] . '</Tipo>';
    $xml_doc .= '<Numero>' . $emitter['id']['number'] . '</Numero>';
    $xml_doc .= '</Identificacion>';
    $xml_doc .= '<NombreComercial>' . $emitter['commercialName'] . '</NombreComercial>';
    $xml_doc .= '<Ubicacion>';
    $xml_doc .= '<Provincia>' . $emitter['address']['data1'] . '</Provincia>';
    $xml_doc .= '<Canton>' . $emitter['address']['data2'] . '</Canton>';
    $xml_doc .= '<Distrito>' . $emitter['address']['data3'] . '</Distrito>';
    $xml_doc .= '<Barrio>' . $emitter['address']['data4'] . '</Barrio>';
    $xml_doc .= '<OtrasSenas>' . $emitter['address']['data5'] . '</OtrasSenas>';
    $xml_doc .= '</Ubicacion>';
    $xml_doc .= '<Telefono>';
    $xml_doc .= '<CodigoPais>' . $emitter['phone']['code'] . '</CodigoPais>';
    $xml_doc .= '<NumTelefono>' . $emitter['phone']['number'] . '</NumTelefono>';
    $xml_doc .= '</Telefono>';
    $xml_doc .= '<Fax>';
    $xml_doc .= '<CodigoPais>' . $emitter['fax']['code'] . '</CodigoPais>';
    $xml_doc .= '<NumTelefono>' . $emitter['fax']['number'] . '</NumTelefono>';
    $xml_doc .= '</Fax>';
    $xml_doc .= '<CorreoElectronico>' . $emitter['email'] . '</CorreoElectronico>';
    $xml_doc .= '</Emisor>';
    $xml_doc .= '<Receptor>'; // starts 'Receptor'
    $xml_doc .= '<Nombre>' . $client->get('name')->value . '</Nombre>';
    $xml_doc .= '<Identificacion>';
    $xml_doc .= '<Tipo>' . $client->get('field_tipo_de_identificacion')->value . '</Tipo>';
    $xml_doc .= '<Numero>' . $client->get('field_intensificacion')->value . '</Numero>';
    $xml_doc .= '</Identificacion>';
    $xml_doc .= '<IdentificacionExtranjero>' . $client->get('field_intensificacion_ex')->value . '</IdentificacionExtranjero>';
    $xml_doc .= '<NombreComercial>' . $client->get('field_nombrecomercial')->value . '</NombreComercial>';
    $xml_doc .= '<Ubicacion>';
    $xml_doc .= '<Provincia>' . $emitter['address']['data1'] . '</Provincia>';
    $xml_doc .= '<Canton>' . $emitter['address']['data2'] . '</Canton>';
    $xml_doc .= '<Distrito>' . $emitter['address']['data3'] . '</Distrito>';
    $xml_doc .= '<Barrio>' . $emitter['address']['data4'] . '</Barrio>';
    $xml_doc .= '<OtrasSenas>' . $emitter['address']['data5'] . '</OtrasSenas>';
    $xml_doc .= '</Ubicacion>';
    $xml_doc .= '<Telefono>';
    $xml_doc .= '<CodigoPais>' . substr($client->get('field_telefono')->value, 0, 3) . '</CodigoPais>';
    $xml_doc .= '<NumTelefono>' . substr($client->get('field_telefono')->value, 4 ) . '</NumTelefono>';
    $xml_doc .= '</Telefono>';
    $xml_doc .= '<Fax>';
    $xml_doc .= '<CodigoPais>' . substr($client->get('field_fax')->value, 0, 3) . '</CodigoPais>';
    $xml_doc .= '<NumTelefono>' . substr($client->get('field_fax')->value, 4 ) . '</NumTelefono>';
    $xml_doc .= '</Fax>';
    $xml_doc .= '<CorreoElectronico>' . $emitter['email'] . '</CorreoElectronico>';
    $xml_doc .= '</Receptor>';

    $AS = $client->get('field_direccion_');
  }
}