<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing supplies table
 *
 * @version 01 - Joseph Bosire 2013-06-08
 *
 * PHP version 5
 */
class Model_SupplyTransactionType extends Model_Base {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_supply_transaction_type';

	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'id';

	protected $_belongs_to = array(
		'personnel' => array('model' => 'Personnel', 'foreign_key' => 'personel_id'),
	);



	public function get_supplies_transactions_list($search_field, $search_value){
		// TODO: Ensure all columns to be returned in the final resultset are in this list e.g. merge all referenced/joined models columns
		$table_columns = $this->_get_table_columns(array($this->object_name()));// ORM::$_column_cache[$this->object_name()];

		$this->_search_list($search_field, $search_value, $table_columns);
		//var_dump($columns);exit;

		// perform other custom logic here

		return $table_columns;
	}

}
