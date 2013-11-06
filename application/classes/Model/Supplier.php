<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing suppliers table
 *
 * PHP version 5.3
 *
 * @category  Models
 * @package   Efficiency_Pro
 * @author    Hezron Obuchele <hezron4k@gmail.com>
 * @author    Joseph Bosire <kashboss@gmail.com>
 * @author    Victor Koech <kipmasi@gmail.com>
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.0.5
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
class Model_Supplier extends Model_Base
{

	/**
	 * Model's table
	 * @var string
	 */
	protected $_table_name = 'fpro_suppliers';

	/**
	 * Model's primary key name
	 * @var string
	 */
	protected $_primary_key = 'supplier_id';

	/**
	 * Model's has many relationships
	 * @var array
	 */
	protected $_has_many = array(
		'supplies'      => array(
			'model'   => 'Supply',
			'through' => 'fpro_supply_purchases'
			),
		'supply_orders' => array(
			'model'       => 'SupplyPurchase',
			'foreign_key' => 'supplier_id'
			)
		);

	/**
	 * Model's belongs to relationships
	 * @var array
	 */
	protected $_belongs_to = array(
		'user_info' => array(
			'model'       => 'User',
			'foreign_key' => 'user_id'
			)
		);


	/**
	 * Setup validation rules
	 *
	 * @return array
	 */
	public function rules() {
		// TODO: See if there are any rules to be added
		return array(
			'supplier_code' => array(array('not_empty'), array('regex', array(':value', '/^[a-zA-Z0-9]*$/'))),
			'supplier_name' => array(array('not_empty'), array('regex', array(':value', '/^[a-zA-Z ]*$/'))),
			'supplier_contact_person' => array(array('regex', array(':value', '/^[a-zA-Z ]*$/'))),
			'supplier_contact_title' => array(array('not_empty')),
			'supplier_cellphone' => array(array('not_empty')),
			'supplier_business_phone' => array(array('not_empty')),
			'supplier_business_phone_ext' => array(array('not_empty'), array('numeric')),
			'supplier_order_email' => array(array('not_empty'), array('email')),
			'supplier_sales_email' => array(array('not_empty'), array('email')),
			'supplier_postal_code' => array(array('not_empty'), array('numeric')),
			'supplier_city' => array(array('not_empty')),
			'supplier_state_province' => array(array('not_empty')),
			'supplier_street_address' => array(array('not_empty')),
			'supplier_description' => array(array('not_empty')),
			'supplier_website' => array(array('not_empty')),
		);
	}


	/**
	 * Filter suppliers list
	 *
	 * @param string $search_field Field to perform search on
	 * @param string $search_value Value to search for
	 *
	 * @return void
	 */
	public function filter_suppliers_list($search_field, $search_value) {
		// get total records before any filtering
		$count_model = clone $this;
		$this->_total_count = $count_model->count_all();
		// TODO: Ensure all columns to be returned in the final resultset are in this list
		// e.g. merge all referenced/joined models columns
		$table_columns = $this->_get_table_columns(array($this->object_name()));// ORM::$_column_cache[$this->object_name()];
		// Use buit-in filter across multiple columns
		$this->_search_list($search_field, $search_value, $table_columns);
		// OR perform other custom logic here
		return $table_columns;
	}


	/**
	 * Get list of columns from supplier table
	 *
	 * @return array
	 */
	public function get_supplier_list_columns() {
		// TODO: Ensure all columns to be returned in the final resultset are in this list
		// e.g. merge all referenced/joined models columns
		$table_columns = $this->_get_table_columns(array($this->object_name()));
		return $table_columns;
	}


	/**
	 * Get supplier's supply items
	 *
	 * @return array
	 */
	public function get_supply_items() {
		return $this->supplies->group_by('supply_id')->find_all();
	}


	/**
	 * Get number of supplier's supply items
	 *
	 * @return integer
	 */
	public function get_supply_item_count() {
		return count($this->supplies->group_by('supply_id')->find_all()) + 1;
	}


	/**
	 * Get total number of supplier's supply items
	 *
	 * @return integer
	 */
	public function get_supplies_subtotal() {
		$supplies = $this->supplies->group_by('supply_id')->find_all();
		$subtotal = 0;
		foreach ($supplies as $supply) {
			$subtotal += $supply->total_quantity;
		}

		return $subtotal;
	}


}
