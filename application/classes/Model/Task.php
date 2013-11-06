<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing tasks table
 *
 * @version 01 - Hezron Obuchele 2013-02-11
 *
 * PHP version 5
 */
class Model_Task extends ORM {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_tasks';
	
	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'task_id';
	
	/**
	 * Model's relationships
	 * @array
	 */
	protected $_belongs_to = array(
					'job_ticket' => array('model' => 'ticket', 'foreign_key' => 'ticket_id'),
					'personnel' => array('model' => 'personnel', 'foreign_key' => 'personnel_id'),
					'equipment' => array('model' => 'equipment', 'foreign_key' => 'equipment_id')
					);
	protected $_has_many = array(
					'problems' => array('model' => 'taskproblem', 'foreign_key' => 'task_id')
					);

	/**
	 * Setup validation rules
	 *
	 * @return array
	 */
	public function rules() {
		// TODO: See if there are any rules to be added
		return array(
			'description' => array(array('not_empty')),
			'ticket_id' => array(array('not_empty'), array('numeric')),
			'start_time' => array( array('date')),
			'end_time' => array( array('date')),
			'can_do_with_other_jobs' => array(
				array('exact_length', array(':value', 1)),
				array('in_array', array(':value', array('0', '1')))
				),
			'personnel_id' => array(array('not_empty'), array('numeric')),
			'equipment_id' => array(array('not_empty'), array('numeric')),
			'repeat' => array(array('numeric')),
			'repeat_until' => array( array('date'))
		);
	}

	/**
	 * Checks if the specified Task ID is of type INT and exists in the database
	 *
	 * @param	int	$task_id  Database id of the task to be looked up
	 * @return	bool
	 */
	public static function is_valid_task($task_id) {

		return (!is_object($task_id) AND intval($task_id) > 0) ? self::factory('task', intval($task_id)) -> loaded : false;
	}

}
