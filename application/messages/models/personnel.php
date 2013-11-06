<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'name' => array(
        'not_empty' => 'You must provide a name',
        'regex' => 'The name can contain only letters and spaces'
    ),
    'status' => array(
		'exact_length' => 'Must be exactly one character long, either 1 or 0',
		'in_array' => 'Must be either "0" or "1"'
	),
	'title' => array(
        'not_empty' => 'You must provide a title'
    ),
	'active' => array(
		'exact_length' => 'Must be exactly one character long, either 1 or 0',
		'in_array' => 'Must be either "0" or "1"'
	),
	'telephone' => array(
        'not_empty' => 'You must provide a telephone number'
    ),
    'email_address' => array(
        'not_empty' => 'You must provide a title',
        'email' => 'This is not a valid email address'
    ),
);

