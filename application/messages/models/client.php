<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'name' => array(
        'not_empty' => 'You must provide a name',
        'regex' => 'The name can contain only letters and spaces'
    ),
    'contact_person' => array(
        'regex' => 'The contact\'s name can contain only letters and spaces'
    ),
    'telephone' => array(
        'not_empty' => 'You must provide a telephone number'
    ),
    'email_address' => array(
        'not_empty' => 'You must provide a title',
        'email' => 'This is not a valid email address'
    ),
    'postal_address' => array(
        'not_empty' => 'You must provide a postal address'
    ),
);

