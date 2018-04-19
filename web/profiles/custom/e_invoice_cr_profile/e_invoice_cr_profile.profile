<?php

/**
 * @file
 * Enables modules and site configuration for the e-invoice-cr profile.
 */

// Add any custom code here, like hook implementations.

// Add any custom code here, like hook implementations.
function e_invoice_cr_profile_preprocess_install_page(&$variables){
    $a = 0;
    $variables['site_name'] = "ParallelDevs -  Factura Electrónica CR" ;
    $variables['site_version'] = "1.0" ;
}
