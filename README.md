# SUMMARY

This is a module to do the electronic billing for Costa Rican regulations only.
It solves the signature process, it lets us to create electronic documents and
send them to the verifications API, generates the XML documents,
email integrations, customer management, and taxes management.

# REQUIREMENTS

* Composer

Important: You need to set up Swiftmailer module in order to have the invoice
email module working properly.

# THEMING
At the moment, you can only overwrite the invoice form styles
if you have a custom admin theme, you just need to add
on yourtheme.libraries.yml a library called "e-invoice-cr-form"
and the module will load those styles instead of default.

# INSTALLATION
You must enable the following modules: e_invoice_cr, customer_entity,
tax_entity, invoice_entity, invoice_email. Once done, you must fill
the configuration form at admin/e-invoice-cr/settings.

See spanish documentation
[here](https://www.paralleldevs.com/blog/gu%C3%ADa-r%C3%A1pida-para-usar-y-configurar-el-m%C3%B3dulo-factura-electr%C3%B3nica-cr-e-invoice-cr).

If you need more support feel free to write
to info@paralleldevs.com
