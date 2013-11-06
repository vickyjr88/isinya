<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing personnel notes table
 *
 * @version 01 - Hezron Obuchele 2013-02-11
 *
 * PHP version 5
 */
class Model_PersonnelNote extends ORM {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_personnel_notes';
	
	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'personnel_note_id';
	
	/**
	 * Model's relationships
	 * @array
	 */
	protected $_belongs_to = array(
					'personnel' => array('model' => 'personnel', 'foreign_key' => 'personnel_id'),
					);

	/**
	 * Setup validation rules
	 *
	 * @return array
	 */
	public function rules() {
		// TODO: See if there are any rules to be added
		return array(
			'personnel_id' => array( array('not_empty'), array('numeric')),
			'description' => array( array('not_empty')),
			'timestamp' => array( array('not_empty'), array('date')),
		);
	}

	/**
	 * Checks if the specified Personnel Note ID is of type INT and exists in the database
	 *
	 * @param	int	$personnel_note_id  Database id of the personnel note to be looked up
	 * @return	bool
	 */
	public static function is_valid_personnel_note($personnel_note_id) {

		return (!is_object($personnel_note_id) AND intval($personnel_note_id) > 0) ? self::factory('personnelnote', intval($personnel_note_id)) -> loaded : false;
	}

}
