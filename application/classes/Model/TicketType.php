<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing ticket types table
 *
 * @version 01 - Hezron Obuchele 2013-02-11
 *
 * PHP version 5
 */
class Model_TicketType extends ORM {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_ticket_types';
	
	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'job_type_id';
	
	/**
	 * Model's relationships
	 * @array
	 */
	protected $_has_many = array(
					'tickets' => array('model' => 'tickets', 'foreign_key' => 'job_type')
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
		);
	}

	/**
	 * Checks if the specified TicketType ID is of type INT and exists in the database
	 *
	 * @param	int	$ticket_id  Database id of the ticket type to be looked up
	 * @return	bool
	 */
	public static function is_valid_ticket_type($ticket_type_id) {

		return (!is_object($ticket_type_id) AND intval($ticket_type_id) > 0) ? self::factory('tickettype', intval($ticket_type_id)) -> loaded : false;
	}

}
