<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing equipment notes table
 *
 * PHP version 5.3
 *
 * @category  Models
 * @package   Efficiency_Pro
 * @author    Hezron Obuchele <hezron4k@gmail.com>
 * @author    Joseph Bosire <kashboss@gmail.com>
 * @author    Brian Mwadime <brianmwadime@gmail.com>
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.0.5
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
class Model_EquipmentNote extends Model_Base
{
	
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_equipment_notes';
	
	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'equipment_note_id';
	
	/**
	 * Model's relationships
	 * @array
	 */
	protected $_belongs_to = array(
		'equipment' => array(
		'model' => 'equipment',
		'foreign_key' => 'equipment_id',
							),
									);
	

	/**
	 * Setup validation rules
	 *
	 * @return array
	 */
	public function rules() {
		// TODO: See if there are any rules to be added
		return array(
			'equipment_note_description' => array(array('not_empty')),
					);
		
	}
	
	
	/**
	 * Function to get Equipment Items comments
	 * 
	 * @param string $search_field Field to perform search on
	 * @param string $search_value Value to search for
	 * @return Comments list
	 */
	public function get_equipment_item_comments($search_field, $search_value) {
		// TODO: Ensure all columns to be returned in the final resultset are in this list 
		// e.g. merge all referenced/joined models columns
		$table_columns = $this->_get_table_columns(array($this->object_name()));
		$this->_search_list($search_field, $search_value, $table_columns);
		// perform other custom logic here		
		return $table_columns;
	}
	

	/**
	 * Checks if the specified Equipment Note ID is of type INT and exists in the database
	 *
	 * @param int $equipment_note_id Database id of the equipment note to be looked up
	 * @return bool
	 * 
	 */
	public static function is_valid_equipment_note($equipment_note_id) {

		return (!is_object($equipment_note_id) and intval($equipment_note_id) > 0) ? self::factory('equipmentnote', intval($equipment_note_id))->loaded : false;
	}
	

}
