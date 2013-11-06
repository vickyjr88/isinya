<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing task problems table
 *
 * @version 01 - Hezron Obuchele 2013-02-11
 *
 * PHP version 5
 */
class Model_TicketNote extends ORM {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_task_problems';
	
	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'task_problem_id';
	
	/**
	 * Model's relationships
	 * @array
	 */
	protected $_belongs_to = array(
					'task' => array('model' => 'task', 'foreign_key' => 'task_id'),
					);

	/**
	 * Setup validation rules
	 *
	 * @return array
	 */
	public function rules() {
		// TODO: See if there are any rules to be added
		return array(
			'task_id' => array( array('not_empty'), array('numeric')),
			'description' => array( array('not_empty')),
			'timestamp' => array( array('not_empty'), array('date')),
		);
	}

	/**
	 * Checks if the specified Task Problem ID is of type INT and exists in the database
	 *
	 * @param	int	$task_problem_id  Database id of the task problem to be looked up
	 * @return	bool
	 */
	public static function is_valid_task_problem($task_problem_id) {

		return (!is_object($task_problem_id) AND intval($task_problem_id) > 0) ? self::factory('taskproblem', intval($task_problem_id)) -> loaded : false;
	}

}
