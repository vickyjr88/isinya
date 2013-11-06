<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'task_id' => array(
        'not_empty' => 'You must select a task',
        'numeric' => 'You must select a task'
    ),
    'description' => array(
        'not_empty' => 'You must provide a description'
    ),
    'timestamp' => array(
        'not_empty' => 'You must provide a timestamp',
        'date' => 'This is not a valid date format',
	),
);

