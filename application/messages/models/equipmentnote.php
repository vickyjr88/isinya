<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'equipment_id' => array(
        'not_empty' => 'You must select an equipment',
        'numeric' => 'You must select an equipment'
    ),
    'description' => array(
        'not_empty' => 'You must provide a description'
    ),
    'timestamp' => array(
        'not_empty' => 'You must provide a timestamp for this note',
        'date' => 'This is not a valid date format',
    ),
);

