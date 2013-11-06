<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'ticket_id' => array(
        'not_empty' => 'You must select a ticket',
        'numeric' => 'You must select a ticket'
    ),
    'description' => array(
        'not_empty' => 'You must provide a description'
    ),
    'timestamp' => array(
        'not_empty' => 'You must provide a timestamp for this note',
        'date' => 'This is not a valid date format',
    ),
);

