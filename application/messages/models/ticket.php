<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'description' => array(
        'not_empty' => 'You must provide a description'
    ),
    'client_id' => array(
        'not_empty' => 'You must select a client',
        'numeric' => 'You must select a client'
    ),
    'job_type' => array(
        'not_empty' => 'You must select a job type',
        'numeric' => 'This is not a valid number'
    ),
    'start_date' => array(
        'date' => 'This is not a valid date format',
    ),
    'end_date' => array(
        'date' => 'This is not a valid date format',
    ),
	'repeat' => array(
        'numeric' => 'This is not a valid number'
    ),
    'repeat_until' => array(
        'date' => 'This is not a valid date format',
    ),
);

