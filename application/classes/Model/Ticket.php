<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing tickets table
 *
 * @version 01 - Hezron Obuchele 2013-02-11
 *
 * PHP version 5
 */
class Model_Ticket extends ORM {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_tickets';
	
	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'ticket_id';
	
	/**
	 * Model's relationships
	 * @array
	 */
	protected $_belongs_to = array(
					'client' => array('model' => 'client', 'foreign_key' => 'client_id'),
					'job_type' => array('model' => 'tickettype', 'foreign_key' => 'job_type_id')
					);
	protected $_has_many = array(
					'notes' => array('model' => 'ticketnote', 'foreign_key' => 'ticket_id'),
					'tasks' => array('model' => 'task', 'foreign_key' => 'ticket_id')
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
			'client_id' => array(array('not_empty'), array('numeric')),
			'job_type' => array(array('not_empty'), array('numeric')),
			'start_date' => array( array('date')),
			'end_date' => array( array('date')),
			'repeat' => array(array('numeric')),
			'repeat_until' => array( array('date'))
		);
	}

	/**
	 * Checks if the specified Ticket ID is of type INT and exists in the database
	 *
	 * @param	int	$ticket_id  Database id of the ticket to be looked up
	 * @return	bool
	 */
	public static function is_valid_ticket($ticket_id) {

		return (!is_object($ticket_id) AND intval($ticket_id) > 0) ? self::factory('ticket', intval($ticket_id)) -> loaded : false;
	}

}
