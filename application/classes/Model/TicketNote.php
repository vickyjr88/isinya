<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing ticket notes table
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
	protected $_table_name = 'fpro_ticket_notes';
	
	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'ticket_note_id';
	
	/**
	 * Model's relationships
	 * @array
	 */
	protected $_belongs_to = array(
					'ticket' => array('model' => 'ticket', 'foreign_key' => 'ticket_id'),
					);

	/**
	 * Setup validation rules
	 *
	 * @return array
	 */
	public function rules() {
		// TODO: See if there are any rules to be added
		return array(
			'ticket_id' => array( array('not_empty'), array('numeric')),
			'description' => array( array('not_empty')),
			'timestamp' => array( array('not_empty'), array('date')),
		);
	}

	/**
	 * Checks if the specified Ticket Note ID is of type INT and exists in the database
	 *
	 * @param	int	$ticket_note_id  Database id of the ticket note to be looked up
	 * @return	bool
	 */
	public static function is_valid_ticket_note($ticket_note_id) {

		return (!is_object($ticket_note_id) AND intval($ticket_note_id) > 0) ? self::factory('ticketnote', intval($ticket_note_id)) -> loaded : false;
	}

}
