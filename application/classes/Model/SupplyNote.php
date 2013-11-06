<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing supply locations table
 *
 * @version 01 - Joseph Bosire 2013-06-08
 *
 * PHP version 5
 */
class Model_SupplyNote extends Model_Base {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_supply_notes';
	
	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'supply_note_id';
	
	// /**
	 // * Model's relationships
	 // * @array
	 // */
	protected $_belongs_to = array('supply' => array('model' => 'Supply', 'foreign_key' => 'supply_id'));
	public function get_supply_item_comments($search_field, $search_value){
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
			'supply_note_description' => array(array('not_empty')),
		);
	}
			
					
	
	
}
