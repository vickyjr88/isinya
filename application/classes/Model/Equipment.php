<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing equpment table
 *
 * PHP version 5.3
 *
 * @category  Models
 * @package   Efficiency_Pro
 * @author    Hezron Obuchele <hezron4k@gmail.com>
 * @author    Joseph Bosire <kashboss@gmail.com>
 * @author    Victor Koech <kipmasi@gmail.com>
 * @author    Brian Mwadime <brianmwadime@gmail.com>
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.0.5
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
class Model_Equipment extends Model_Base 
{
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_equipment';
	
	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'equipment_id';
	
	/**
	 * Model's relationships
	 * @array
	 */
	protected $_has_many = array(
		'comments' => array(
			'model'   => 'EquipmentNote',
			'foreign_key' => 'equipment_id',
		),
		'tasks' => array(
			'model' => 'task', 
			'foreign_key' => 'equipment_id'
		),
		'training_sessions' => array(
			'model' => 'training', 
			'through' => 'fpro_training', 
			'foreign_key' => 'equipment_id'
		),
	);

	/**
	 * Validation Rules for Equipment module
	 *
	 * @return	array of messages for fields with errors
	 */
	public function rules() {
		// TODO: See if there are any rules to be added
		return array(
			'equipment_name' => array(array('not_empty')),
			'equipment_purchase_date' => array(array('date')),
			'production_capacity' => array(array('not_empty')),
			'serial_number' => array(array('not_empty')),
			'equipment_avatar' => array(array('not_empty')),
			'equipment_purchase_date' => array(
				array('not_empty'),
				array('date')
			)
		);
		
	}
	
	
	/**
	 * Checks if the specified Equipment ID is of type INT and exists in the database
	 *
	 * @param string $searchfield  Database field to search
	 * @param String $search_value  Database value being searched for
	 * @return bool
	 */
	public function get_equipment_list($search_field, $search_value) {
		// TODO: Ensure all columns to be returned in the final resultset are in this list e.g. merge all referenced/joined models columns
		$table_columns = $this->_get_table_columns(array($this->object_name()));// ORM::$_column_cache[$this->object_name()];

		$this->_search_list($search_field, $search_value, $table_columns);
		// var_dump($columns);exit;
		// perform other custom logic here
		return $table_columns;
	}
	

	/**
	 * Checks if the specified Equipment ID is of type INT and exists in the database
	 *
	 * @param int $equipment_id  Database id of the equipment to be looked up
	 * @return	bool
	 */
	public static function is_valid_equipment($equipment_id) {

		return (!is_object($equipment_id) AND intval($equipment_id) > 0) ? self::factory('equipment', intval($equipment_id)) -> loaded : false;
	}
	

}
