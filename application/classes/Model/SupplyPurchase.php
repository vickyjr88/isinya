<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing supply purchases table
 *
 * @version 01 - Joseph Bosire 2013-06-08
 *
 * PHP version 5
 */
class Model_SupplyPurchase extends Model_Base {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_supply_purchases';

	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'supply_purchase_id';

	/**
	* Model's relationships
	 * @array
	 */
	protected $_has_one = array(
			'supply' => array('model' => 'Supply', 'foreign_key' => 'supply_id')
		);

	/**
	 * Setup validation rules
	 *
	 * @return array
	 */
	public function rules() {
		return array(
			'supply_id' => array(array('not_empty'), array('numeric')),
			'supplier_id' => array(array('not_empty'), array('numeric')),
			'package_type_id' => array(array('not_empty'), array('numeric')),
			'supply_purchased_quantity' => array(array('not_empty'), array('numeric')),
			'quantity_per_package' => array(array('not_empty'), array('numeric')),
			'supply_purchase_date' => array(array('not_empty'), array('date')),
			'cost_per_package' => array(array('not_empty'), array('numeric')),
			'cost_per_unit' => array(array('not_empty'), array('numeric')),
		);
	}


	public function get_supply_purchases_list($search_field, $search_value){
		// TODO: Ensure all columns to be returned in the final resultset are in this list e.g. merge all referenced/joined models columns
		$table_columns = $this->_get_table_columns(array($this->object_name()));// ORM::$_column_cache[$this->object_name()];

		$this->_search_list($search_field, $search_value, $table_columns);

		// perform other custom logic here

		return $table_columns;
	}
}
