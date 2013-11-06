<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'personnel_id' => array(
        'not_empty' => 'You must select a personnel',
        'numeric' => 'You must select a personnel'
    ),
    'description' => array(
        'not_empty' => 'You must provide a description'
    ),
    'timestamp' => array(
        'not_empty' => 'You must provide a timestamp for this note',
        'date' => 'This is not a valid date format',
    ),
);

