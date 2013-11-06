<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing supply shelves supply purchases table
 *
 * @version 01 - Joseph Bosire 2013-06-08
 *
 * PHP version 5
 */
class Model_SupplyShelfSupplyPurchase extends Model_Base {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_supply_shelves_supply_purchases';

	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'supply_shelves_supply_purchases_id';

	protected $_belongs_to = array(
		'purchase' => array('model' => 'SupplyPurchase', 'foreign_key' => 'supply_purchase_id'),
		'supply' => array('model' => 'Supply', 'foreign_key' => 'supply_id'),
	);

	public static function get_supply_id($sssp_id) {
		$query = DB::query(Database::SELECT, 'SELECT `supply_id` FROM fpro_supply_shelves_supply_purchases WHERE `supply_shelves_supply_purchases_id` = :sssp_id');
		$query->parameters(array(
			':sssp_id' => $sssp_id
		));
		$result = $query->execute()->as_array();
		if (!empty($result[0]['supply_id'])) {
			return $result[0]['supply_id'];
		} else {
			return null;
		}
	}
}
