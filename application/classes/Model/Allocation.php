<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing sssp_allocations table
 *
 * @version 01 - Joseph Bosire 2013-06-08
 *
 * PHP version 5
 */
class Model_Allocation extends Model_Base {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'frpo_allocations';

	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'allocation_id';

	public static function get_inventory_allocations($supply_allocation_id) {
		$query = DB::query(Database::SELECT, 'SELECT  frpo_allocations.*, `fpro_supplies`.supply_name, `fpro_supplies`.supply_id, `fpro_supplies`.product_code , `fpro_supply_shelves`.supply_shelf_name, `fpro_supply_locations`.supply_location_name'.
				' FROM `frpo_allocations` INNER JOIN `fpro_supply_shelves_supply_purchases` ON '.
				'`fpro_supply_shelves_supply_purchases`.`supply_shelves_supply_purchases_id` = `frpo_allocations`.`sssp_id` '.
				'INNER JOIN `fpro_supply_purchases` ON `fpro_supply_purchases`.`supply_purchase_id` = `fpro_supply_shelves_supply_purchases`.`supply_purchase_id` '.
				'INNER JOIN `fpro_supplies` ON `fpro_supplies`.`supply_id` =  `fpro_supply_purchases`.`supply_id` '.
				'INNER JOIN `fpro_supply_shelves` ON `fpro_supply_shelves`.`supply_shelf_id` =  `fpro_supply_shelves_supply_purchases`.`supply_shelf_id` '.
				'INNER JOIN `fpro_supply_locations` ON `fpro_supply_locations`.`supply_location_id` =  `fpro_supply_shelves`.`supply_location_id` '.
				'WHERE `supply_allocation_id` = :supply_allocation_id');
		$query->parameters(array(
			':supply_allocation_id' => $supply_allocation_id,
		));
		$result = $query->execute();
		return $result->as_array();
	}
}
