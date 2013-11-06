<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'description' => array(
        'not_empty' => 'You must provide a description'
    ),
    'ticket_id' => array(
        'not_empty' => 'You must select a client',
        'numeric' => 'You must select a client'
    ),
    'start_time' => array(
        'date' => 'This is not a valid date format',
    ),
    'end_time' => array(
        'date' => 'This is not a valid date format',
    ),
    'can_do_with_other_jobs' => array(
		'exact_length' => 'Must be exactly one character long, either 1 or 0',
		'in_array' => 'Must be either "0" or "1"'
	),
	'personnel_id' => array(
        'not_empty' => 'You must select a personnel',
        'numeric' => 'This is not a valid number'
    ),
	'equipment_id' => array(
		'not_empty' => 'You must select an equipent',
        'numeric' => 'This is not a valid number'
    ),
    'repeat' => array(
        'numeric' => 'This is not a valid number'
    ),
    'repeat_until' => array(
        'date' => 'This is not a valid date format',
    ),
);

