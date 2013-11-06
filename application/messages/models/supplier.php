<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'supplier_code' => array(
        'not_empty' => 'You must provide a product code',
        'regex' => 'Invalid product code'
    ),
    'supplier_name' => array(
        'not_empty' => 'You must provide a name',
        'regex' => 'The name can contain only letters and spaces'
    ),
    'supplier_contact_person' => array(
        'not_empty' => 'You must provide a contact person\'s name',
        'regex' => 'The contact person\'s name can contain only letters and spaces'
    ),
    /*'status' => array(
            'exact_length' => 'Must be exactly one character long, either 1 or 0',
            'in_array' => 'Must be either "0" or "1"'
    ),*/
	'supplier_contact_title' => array(
        'not_empty' => 'You must provide a title'
    ),
	/*'active' => array(
		'exact_length' => 'Must be exactly one character long, either 1 or 0',
		'in_array' => 'Must be either "0" or "1"'
	),*/
	'supplier_cellphone' => array(
        'not_empty' => 'You must provide a mobile phone number'
    ),
    'supplier_business_phone' => array(
        'not_empty' => 'You must provide a telephone number'
    ),
    'supplier_business_phone_ext' => array(
        'not_empty' => 'You must provide a telephone extension',
        'numeric' => 'Invalid telephone extension'
    ),
    'supplier_order_email' => array(
        'not_empty' => 'You must provide an email address',
        'email' => 'Invalid email address'
    ),
    'supplier_sales_email' => array(
        'not_empty' => 'You must provide an email address',
        'email' => 'Invalid email address'
    ),
    'supplier_postal_code' => array(
        'not_empty' => 'You must provide a postal code',
        'numeric' => 'Invalid postal code'
    ),
    'supplier_city' => array(
        'not_empty' => 'You must provide a city'
    ),
    'supplier_state_province' => array(
        'not_empty' => 'You must provide a state/province'
    ),
    'supplier_street_address' => array(
        'not_empty' => 'You must provide a street name'
    ),
    'supplier_description' => array(
        'not_empty' => 'You must provide a description/notes'
    ),
    'supplier_website' => array(
        'not_empty' => 'You must provide a website'
    ),
);

