<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'equipment_name' => array(
        'not_empty' => 'You must provide a name.',
    ),
    'purchase_date' => array(
        'date' => 'This is not a valid date format.',
    ),
    'production_capacity' => array(
        'not_empty' => 'You must specify the production capacity.',
    ),
    'serial_number' => array(
        'not_empty' => 'You must specify the serial number.',
    ),
    'equipment_purchase_date' => array(
    	'not_empty' => 'You must Enter a Purchase Date.',
        'date' => 'This is not a valid date format.',
    ),
);

