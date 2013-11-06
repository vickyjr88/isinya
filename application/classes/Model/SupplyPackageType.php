<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing supply package types table
 *
 * @version 01 - Joseph Bosire 2013-06-08
 *
 * PHP version 5
 */
class Model_SupplyPackageType extends Model_Base {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_supply_package_types';
	
	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'package_type_id';
	
	public function get_package_types_list($search_field, $search_value){
		// TODO: Ensure all columns to be returned in the final resultset are in this list e.g. merge all referenced/joined models columns
		$table_columns = $this->_get_table_columns(array($this->object_name()));// ORM::$_column_cache[$this->object_name()];
		$this->_search_list($search_field, $search_value, $table_columns);
		// perform other custom logic here		
		return $table_columns;
	}
	/**
	 * Setup validation rules
	 *
	 * @return array
	 */
	public function rules() {
		// TODO: See if there are any rules to be added
		return array(
			'package_type_name' => array(array('not_empty')),
		);
	}
	
	
}
