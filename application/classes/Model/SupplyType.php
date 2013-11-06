<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing supply types table
 *
 * @version 01 - Joseph Bosire 2013-06-08
 *
 * PHP version 5
 */
class Model_SupplyType extends Model_Base {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_supply_types';

	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'supply_type_id';

	/**
	 *Has Many relationship
	 */
	protected $_has_many = array('supplies'=>array('Model'=>'Supply','foreign_key'=>'supply_type_id'));

	public function get_supply_supply_types_list($search_field, $search_value){
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
			'supply_type_name' => array(array('not_empty')),
		);
	}

	/*
	* Function to return the subtotal of quantity in hand for
	* all supply items within a certain category
	* @return int $subtotal
	*/
	public function get_supplies_subtotal() {
		$supplies = $this->supplies->where('total_quantity','>',0)->find_all();
		$subtotal = 0;
		foreach ($supplies as $supply) {
			$subtotal += $supply->total_quantity;
		}
		return $subtotal;
	}

	/*
	* Function to return the total number of supply items within a certain category
	* @return int $total
	*/
	public function get_no_supply_items(){
		return count($this->supplies->where('total_quantity','>',0)->find_all())+1;
	}

	/*
	* Function to return the list of supply items within a certain category
	* @return int $total
	*/
	public function get_supply_items(){
		return $this->supplies->where('total_quantity','>',0)->find_all();
	}
}
