# Invoice CR

[![Build Status](https://travis-ci.org/ParallelDevs/e_invoice_cr.svg?branch=develop)](https://travis-ci.org/ParallelDevs/e_invoice_cr)

## SUMMARY

This is a module to do the electronic billing for Costa Rican regulations only.
It solves the signature process, it lets us to create electronic documents and
send them to the verifications API, generates the XML documents,
email integrations, customer management, and taxes management.

## REQUIREMENTS

* Composer

* Important: You need to set up Swiftmailer module in order to have the invoice
email module working properly.

* Java Runtime Environment (JRE) installed on your server.

* PHP 7

## THEMING
At the moment, you can only overwrite the invoice form styles
if you have a custom admin theme, you just need to add
on yourtheme.libraries.yml a library called "e-invoice-cr-form"
and the module will load those styles instead of default.

## INSTALLATION
You must enable the following modules: e_invoice_cr, customer_entity,
tax_entity, invoice_entity, invoice_email. Once done, you must fill
the configuration form at admin/e-invoice-cr/settings.


See spanish documentation
[here](https://docs.google.com/document/d/1SNvUe5eaaEs76PW49B9JeJ-v2NW09Kf-aqb1LaPj9yE/edit?usp=sharing).

## INSTALLING WITH COMPOSER

1- Edit your composer.json to add the repo
```json
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/ParallelDevs/e_invoice_cr"
        },
    ],
``` 
2- Run `composer require paralleldevs/e_invoice_cr`

----------------

If you need more support feel free to write
to info@paralleldevs.com
