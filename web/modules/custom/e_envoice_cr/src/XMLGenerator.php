<?php

namespace Drupal\e_invoice_cr;

use DOMDocument;
use Drupal\customer_entity\Entity\CustomerEntity;
use Drupal\invoice_entity\Entity\InvoiceEntityInterface;
use Drupal\tax_entity\Entity\TaxEntity;
use Drupal\invoice_entity\Entity\InvoiceEntity;

/**
 * Generated a invoice XML document.
 */
class XMLGenerator {

  /**
   * Function to generate the xml document.
   *
   * @return \DOMDocument
   *   The complete xml to send.
   */
  public function generateXmlByEntity(InvoiceEntity $entity) {
    $type_of = $entity->get('type_of')->value;
    $tagname = InvoiceEntityInterface::DOCUMENTATIONINFO[$type_of]['xmltag'];
    $xmlns = InvoiceEntityInterface::DOCUMENTATIONINFO[$type_of]['xmlns'];

    $xml_text = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>";
    $xml_text .= "<" . $tagname . " xmlns=\"" . $xmlns . "\" xmlns:ns2=\"http://www.w3.org/2000/09/xmldsig#\">\n";

    $xml_text .= $this->generateHeaderXml($entity);
    $xml_text .= $this->generateDetailXml($entity);
    $xml_text .= $this->generateSummaryXml($entity);
    $xml_text .= $this->generateReferenceInfoXml($entity);
    $xml_text .= $this->generateCurrentRegulationXml();
    $xml_text .= "</" . $tagname . ">\n";

    // Create the xml document.
    $doc = new DOMDocument();
    $doc->loadXML($xml_text);
    return $doc;
  }

  /**
   * Generate the header for the xml document like Hacienda ask for it.
   *
   * @return string
   *   Return the header xml as a text.
   */
  private function generateHeaderXml(InvoiceEntity $entity) {
    $payment_method = $entity->get('field_payment_method')->getValue();
    $xml_doc = "\t<Clave>" . $entity->get('field_numeric_key')->value . "</Clave>\n";
    $xml_doc .= "\t<NumeroConsecutivo>" . $entity->get('field_consecutive_number')->value . "</NumeroConsecutivo>\n";
    $xml_doc .= "\t<FechaEmision>" . $this->formatDateForXml($entity->get('field_invoice_date')->value) . "</FechaEmision>\n";

    // Add the "emisor" information.
    $xml_doc .= $this->generateEmisorXml();
    // Add the "receptor" information.
    $xml_doc .= $this->generateReceptorXml($entity);

    // More header information.
    $xml_doc .= "\t<CondicionVenta>" . $entity->get('field_sale_condition')->value . "</CondicionVenta>\n";
    $xml_doc .= "\t<PlazoCredito>" . $entity->get('field_credit_term')->value . "</PlazoCredito>\n";

    if ($payment_method != NULL & !empty($payment_method)) {
      foreach ($payment_method as $method) {
        $xml_doc .= "\t<MedioPago>" . $method['value'] . "</MedioPago>\n";
      }
    }

    return $xml_doc;
  }

  /**
   * Generate the detail part for the xml document like Hacienda ask for it.
   *
   * @return string
   *   Return the detail xml as a text.
   */
  private function generateDetailXml(InvoiceEntity $entity) {
    $rows = $entity->get('field_rows')->getValue();
    $xml_doc = "\t<DetalleServicio>\n";

    foreach ($rows as $index => $item) {
      if (is_numeric($index)) {
        $xml_doc .= $this->generateRowXml($index, $item);
      }
    }
    $xml_doc .= "\t</DetalleServicio>\n";

    return $xml_doc;
  }

  /**
   * Generate the summary the xml document like Hacienda ask for it.
   *
   * @return string
   *   Return the summary xml as a text.
   */
  private function generateSummaryXml(InvoiceEntity $entity) {
    $settings = \Drupal::config('e_invoice_cr.settings');
    $currency = $settings->get('currency');
    $rows = $entity->get('field_rows')->getValue();
    $total_services = 0;
    $total_prod = 0;
    $total_serv_with_tax = 0;
    $total_serv_without_tax = 0;
    $total_prod_with_tax = 0;
    $total_prod_without_tax = 0;

    foreach ($rows as $row) {
      $values = $row['subform'];
      $tax_id = $values['field_row_tax'][0]['target_id'];
      $tax = TaxEntity::load($tax_id);

      if ($tax !== NULL) {
        $tax_mount = ($tax->get('field_tax_percentage')->value / 100) * $values['field_subtotal'][0]['value'];
      }
      // Save the ones with tax.
      if ($tax_mount !== "" && $tax_mount > 0 && $tax_mount !== NULL) {
        if ($values['field_tipo'][0]['value'] === "01") {
          $total_prod_with_tax = $total_prod_with_tax + (int) round($values['field_total_amount'][0]['value'], 5);
          $total_prod++;
        }
        else {
          $total_serv_with_tax = $total_serv_with_tax + (int) round($values['field_total_amount'][0]['value'], 5);
          $total_services++;
        }
      }
      else {
        // Save the ones without tax.
        if ($values['field_tipo'][0]['value'] === "01") {
          $total_prod_without_tax = $total_prod_without_tax + (int) round($values['field_total_amount'][0]['value'], 5);
          $total_services++;
        }
        else {
          $total_serv_without_tax = $total_serv_without_tax + (int) round($values['field_total_amount'][0]['value'], 5);
          $total_services++;
        }
      }
    }
    $total_with_tax = $total_serv_with_tax + $total_prod_with_tax;
    $total_without_tax = $total_serv_without_tax + $total_prod_without_tax;
    $total_sale = $total_with_tax + $total_without_tax;

    $xml_doc = "\t<ResumenFactura>\n";
    $xml_doc .= "\t\t<CodigoMoneda>" . strtoupper($currency) . "</CodigoMoneda>\n";
    // No supported at the moment (*).
    $xml_doc .= "\t\t<TipoCambio>0</TipoCambio>\n";
    $xml_doc .= "\t\t<TotalServGravados>" . $total_serv_with_tax . "</TotalServGravados>\n";
    $xml_doc .= "\t\t<TotalServExentos>" . $total_serv_without_tax . "</TotalServExentos>\n";
    $xml_doc .= "\t\t<TotalMercanciasGravadas>" . $total_prod_with_tax . "</TotalMercanciasGravadas>\n";
    $xml_doc .= "\t\t<TotalMercanciasExentas>" . $total_prod_without_tax . "</TotalMercanciasExentas>\n";
    $xml_doc .= "\t\t<TotalGravado>" . $total_with_tax . "</TotalGravado>\n";
    $xml_doc .= "\t\t<TotalExento>" . $total_without_tax . "</TotalExento>\n";
    $xml_doc .= "\t\t<TotalVenta>" . $total_sale . "</TotalVenta>\n";
    $xml_doc .= "\t\t<TotalDescuentos>" . $entity->get('field_total_discount')->value . "</TotalDescuentos>\n";
    $xml_doc .= "\t\t<TotalVentaNeta>" . $entity->get('field_net_sale')->value . "</TotalVentaNeta>\n";
    $xml_doc .= "\t\t<TotalImpuesto>" . $entity->get('field_total_tax')->value . "</TotalImpuesto>\n";
    $xml_doc .= "\t\t<TotalComprobante>" . $entity->get('field_total_invoice')->value . "</TotalComprobante>\n";
    $xml_doc .= "\t</ResumenFactura>\n";

    return $xml_doc;
  }

  /**
   * Generate the reference part for the xml document like Hacienda ask for it.
   *
   * @return string
   *   Return the reference information xml section as a text.
   */
  private function generateReferenceInfoXml(InvoiceEntity $entity) {
    $type_of = $entity->get('ref_type_of')->value;
    $xml_doc = "";
    if ($type_of != NULL) {
      $xml_doc .= "\t<InformacionReferencia>\n";
      $xml_doc .= "\t\t<TipoDoc>" . InvoiceEntityInterface::DOCUMENTATIONINFO[$type_of]['code'] . "</TipoDoc>\n";
      $xml_doc .= "\t\t<Numero>" . $entity->get('ref_doc_key')->value . "</Numero>\n";
      $xml_doc .= "\t\t<FechaEmision>" . $this->formatDateForXml($entity->get('ref_date')->value) . "</FechaEmision>\n";
      $xml_doc .= "\t\t<Codigo>" . $entity->get('ref_code')->value . "</Codigo>\n";
      $xml_doc .= "\t\t<Razon>" . $entity->get('ref_reason')->value . "</Razon>\n";
      $xml_doc .= "\t</InformacionReferencia>\n";
    }
    return $xml_doc;
  }

  /**
   * Generate the regulation for the xml document like Hacienda ask for it.
   *
   * @return string
   *   Return the current regulation xml section as a text.
   */
  private function generateCurrentRegulationXml() {
    $xml_doc = "\t<Normativa>\n";
    $xml_doc .= "\t\t<NumeroResolucion>DGT-R-48-2016</NumeroResolucion>\n";
    $xml_doc .= "\t\t<FechaResolucion>12-12-2016 08:08:12</FechaResolucion>\n";
    $xml_doc .= "\t</Normativa>\n";
    $xml_doc .= "\t<Otros>\n";
    $xml_doc .= "\t\t<OtroTexto></OtroTexto>\n";
    $xml_doc .= "\t</Otros>\n";

    return $xml_doc;
  }

  /**
   * Generate the "emisor" xml structure like Hacienda ask for it.
   *
   * @return string
   *   Return the "emisor" in a string variable.
   */
  private function generateEmisorXml() {
    $settings = \Drupal::config('e_invoice_cr.settings');
    $province_code = substr($settings->get('postal_code'), 0, 1);
    $canton_code = substr($settings->get('postal_code'), 1, 2);
    $district_code = substr($settings->get('postal_code'), 3, 5);
    $town_code = '01';

    $country_code = substr($settings->get('phone'), 0, 3);
    $phone_number = substr($settings->get('phone'), 3);
    $fax_code = substr($settings->get('fax'), 0, 3);
    $fax_number = substr($settings->get('fax'), 3);

    $xml_doc = "\t<Emisor>\n";
    $xml_doc .= "\t\t<Nombre>" . $settings->get('name') . "</Nombre>\n";
    $xml_doc .= "\t\t<Identificacion>\n";
    $xml_doc .= "\t\t\t<Tipo>" . $settings->get('id_type') . "</Tipo>\n";
    $xml_doc .= "\t\t\t<Numero>" . $settings->get('id') . "</Numero>\n";
    $xml_doc .= "\t\t</Identificacion>\n";
    $xml_doc .= "\t\t<NombreComercial>" . $settings->get('commercial_name') . "</NombreComercial>\n";
    $xml_doc .= "\t\t<Ubicacion>\n";
    $xml_doc .= "\t\t\t<Provincia>" . $province_code . "</Provincia>\n";
    $xml_doc .= "\t\t\t<Canton>" . $canton_code . "</Canton>\n";
    $xml_doc .= "\t\t\t<Distrito>" . $district_code . "</Distrito>\n";
    $xml_doc .= "\t\t\t<Barrio>" . $town_code . "</Barrio>\n";
    $xml_doc .= "\t\t\t<OtrasSenas>" . $settings->get('address') . "</OtrasSenas>\n";
    $xml_doc .= "\t\t</Ubicacion>\n";
    $xml_doc .= "\t\t<Telefono>\n";
    $xml_doc .= "\t\t\t<CodigoPais>" . $country_code . "</CodigoPais>\n";
    $xml_doc .= "\t\t\t<NumTelefono>" . $phone_number . "</NumTelefono>\n";
    $xml_doc .= "\t\t</Telefono>\n";
    if (!is_null($fax_code) && $fax_code !== "") {
      $xml_doc .= "\t\t<Fax>\n";
      $xml_doc .= "\t\t\t<CodigoPais>" . $fax_code . "</CodigoPais>\n";
      $xml_doc .= "\t\t\t<NumTelefono>" . $fax_number . "</NumTelefono>\n";
      $xml_doc .= "\t\t</Fax>\n";
    }
    $xml_doc .= "\t\t<CorreoElectronico>" . $settings->get('email') . "</CorreoElectronico>\n";
    $xml_doc .= "\t</Emisor>\n";

    return $xml_doc;
  }

  /**
   * Generate the "receptor" xml structure like Hacienda ask for it.
   *
   * @return string
   *   Return the "receptor" in a string variable.
   */
  private function generateReceptorXml(InvoiceEntity $entity) {
    $client_id = $entity->get('field_client')->target_id;
    $client = CustomerEntity::load($client_id);
    $client_zip_code = $client->field_address->getValue();

    $xml_doc = "\t<Receptor>\n";
    $xml_doc .= "\t\t<Nombre>" . $client->get('name')->value . "</Nombre>\n";
    $xml_doc .= "\t\t<Identificacion>\n";
    $xml_doc .= "\t\t\t<Tipo>" . $client->get('field_type_id')->value . "</Tipo>\n";
    $xml_doc .= "\t\t\t<Numero>" . $client->get('field_customer_id')->value . "</Numero>\n";
    $xml_doc .= "\t\t</Identificacion>\n";
    $xml_doc .= "\t\t<IdentificacionExtranjero>" . $client->get('field_customer_foreign_id')->value . "</IdentificacionExtranjero>\n";
    $xml_doc .= "\t\t<NombreComercial>" . $client->get('field_commercial_name')->value . "</NombreComercial>\n";
    $xml_doc .= "\t\t<Ubicacion>\n";
    $xml_doc .= "\t\t\t<Provincia>" . substr($client_zip_code[0]['zipcode'], 0, 1) . "</Provincia>\n";
    $xml_doc .= "\t\t\t<Canton>" . substr($client_zip_code[0]['zipcode'], 1, 2) . "</Canton>\n";
    $xml_doc .= "\t\t\t<Distrito>" . substr($client_zip_code[0]['zipcode'], 3, 5) . "</Distrito>\n";
    // No supported at the moment (*)
    $xml_doc .= "\t\t\t<Barrio>01</Barrio>\n";
    $xml_doc .= "\t\t\t<OtrasSenas>" . $client_zip_code[0]['additionalinfo'] . "</OtrasSenas>\n";
    $xml_doc .= "\t\t</Ubicacion>\n";
    $xml_doc .= "\t\t<Telefono>\n";
    $xml_doc .= "\t\t\t<CodigoPais>" . substr($client->get('field_phone')->value, 0, 3) . "</CodigoPais>\n";
    $xml_doc .= "\t\t\t<NumTelefono>" . substr($client->get('field_phone')->value, 3) . "</NumTelefono>\n";
    $xml_doc .= "\t\t</Telefono>\n";
    if (!is_null($client->get('field_fax')->value) && $client->get('field_fax')->value !== "") {
      $xml_doc .= "\t\t<Fax>\n";
      $xml_doc .= "\t\t\t<CodigoPais>" . substr($client->get('field_fax')->value, 0, 3) . "</CodigoPais>\n";
      $xml_doc .= "\t\t\t<NumTelefono>" . substr($client->get('field_fax')->value, 3) . "</NumTelefono>\n";
      $xml_doc .= "\t\t</Fax>\n";
    }
    $xml_doc .= "\t\t<CorreoElectronico>" . $client->get('field_email')->value . "</CorreoElectronico>\n";
    $xml_doc .= "\t</Receptor>\n";

    return $xml_doc;
  }

  /**
   * Generate the xml structure for a invoice row.
   *
   * @return string
   *   Return the xml text from a row line of the invoice.
   */
  private function generateRowXml($index, array $row) {
    $count = $index + 1;
    $values = $row['subform'];
    $discount = $values['field_add_discount']['value'] ? $values['field_row_discount'][0]['value'] : 0;
    $discount_reason = $values['field_add_discount']['value'] ? $values['field_discount_reason'][0]['value'] : "";
    $tax_id = $values['field_row_tax'][0]['target_id'];
    $entity_manager = \Drupal::entityManager();
    $tax = $entity_manager->getStorage('tax_entity')->load($tax_id);
    if ($tax !== NULL) {
      $tax_mount = ($tax->get('field_tax_percentage')->value / 100) * $values['field_subtotal'][0]['value'];
    }
    // Continue building the xml.
    $xml_doc = "\t\t<LineaDetalle>\n";
    $xml_doc .= "\t\t\t<NumeroLinea>" . $count . "</NumeroLinea>\n";
    $xml_doc .= "\t\t\t<Codigo>\n";
    $xml_doc .= "\t\t\t\t<Tipo>" . $values['field_code_type'][0]['value'] . "</Tipo>\n";
    $xml_doc .= "\t\t\t\t<Codigo>" . $values['field_code'][0]['value'] . "</Codigo>\n";
    $xml_doc .= "\t\t\t</Codigo>\n";
    $xml_doc .= "\t\t\t<Cantidad>" . $values['field_quantity'][0]['value'] . "</Cantidad>\n";
    $xml_doc .= "\t\t\t<UnidadMedida>" . $values['field_unit_measure'][0]['value'] . "</UnidadMedida>\n";
    $xml_doc .= "\t\t\t<UnidadMedidaComercial>" . $values['field_another_unit_measure'][0]['value'] . "</UnidadMedidaComercial>\n";
    $xml_doc .= "\t\t\t<Detalle>" . $values['field_detail'][0]['value'] . "</Detalle>\n";
    $xml_doc .= "\t\t\t<PrecioUnitario>" . $values['field_preciounitario'][0]['value'] . "</PrecioUnitario>\n";
    $xml_doc .= "\t\t\t<MontoTotal>" . $values['field_total_amount'][0]['value'] . "</MontoTotal>\n";
    if (!is_null($discount) && $discount > 0) {
      $xml_doc .= "\t\t\t<MontoDescuento>" . $discount . "</MontoDescuento>\n";
      $xml_doc .= "\t\t\t<NaturalezaDescuento>" . $discount_reason . "</NaturalezaDescuento>\n";
    }
    $xml_doc .= "\t\t\t<SubTotal>" . round($values['field_subtotal'][0]['value'], 5) . "</SubTotal>\n";
    if ($tax !== NULL) {
      if ($tax->get('field_tax_percentage')->value > 0) {
        $xml_doc .= "\t\t\t<Impuesto>\n";
        $xml_doc .= "\t\t\t\t<Codigo>" . $tax->get('field_tax_type')->value . "</Codigo>\n";
        $xml_doc .= "\t\t\t\t<Tarifa>" . $tax->get('field_tax_percentage')->value . "</Tarifa>\n";
        $xml_doc .= "\t\t\t\t<Monto>" . $tax_mount . '</Monto>' . "\n";

        // Here exonerations data.
        if ($tax->get("exoneration")->value) {
          $xml_doc .= $this->addExonerationXml($tax, $tax_mount);
        }
        $xml_doc .= "\t\t\t</Impuesto>\n";
      }
    }
    $xml_doc .= "\t\t\t<MontoTotalLinea>" . round($values['field_line_total_amount'][0]['value'], 5) . "</MontoTotalLinea>\n";
    $xml_doc .= "\t\t</LineaDetalle>\n";

    return $xml_doc;
  }

  /**
   * Generate the xml structure for a exoneration.
   *
   * @param \Drupal\tax_entity\Entity\TaxEntity $tax
   *   The tax entity whom own the exoneration.
   * @param float $tax_amount
   *   The taxonomy current amount.
   *
   * @return string
   *   Return the exoneration xml structure.
   */
  private function addExonerationXml(TaxEntity $tax, $tax_amount) {
    $amount = $tax_amount * ($tax->get('ex_percentage')->value / 100);

    $xml_text = str_repeat("\t", 4) . "<Exoneracion>\n";
    $xml_text .= str_repeat("\t", 5) . "<TipoDocumento>" . $tax->get('ex_document_type')->value . "</TipoDocumento>\n";
    $xml_text .= str_repeat("\t", 5) . "<NumeroDocumento>" . $tax->get('ex_document_number')->value . "</NumeroDocumento>\n";
    $xml_text .= str_repeat("\t", 5) . "<NombreInstitucion>" . $tax->get('ex_institution')->value . "</NombreInstitucion>\n";
    $xml_text .= str_repeat("\t", 5) . "<FechaEmision>" . $this->formatDateForXml($tax->get('ex_date')->value) . "</FechaEmision>\n";
    $xml_text .= str_repeat("\t", 5) . "<MontoImpuesto>" . $amount . "</MontoImpuesto>\n";
    $xml_text .= str_repeat("\t", 5) . "<PorcentajeCompra>" . $tax->get('ex_percentage')->value . "</PorcentajeCompra>\n";
    $xml_text .= str_repeat("\t", 4) . "</Exoneracion>\n";

    return $xml_text;
  }

  /**
   * Function to return the date on the format that is hacienda asking for.
   *
   * @param string $date_text
   *   String with the date.
   *
   * @return string
   *   Return format date.
   */
  private function formatDateForXml($date_text) {
    $date_object = strtotime($date_text);
    return \Drupal::service('date.formatter')->format($date_object, 'date_text', 'c');
  }

}
