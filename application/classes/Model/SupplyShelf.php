<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing supply shelves table
 *
 * @version 01 - Joseph Bosire 2013-06-08
 *
 * PHP version 5
 */
class Model_SupplyShelf extends Model_Base {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_supply_shelves';

	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'supply_shelf_id';

	protected $_belongs_to = array(
		'location' => array('model' => 'SupplyLocation', 'foreign_key' => 'supply_location_id'),
	);
	protected $_has_many = array(
			'sssp' => array('model' => 'SupplyShelfSupplyPurchase', 'foreign_key' => 'supply_shelf_id'),
		);

	public function get_shelves_list($search_field, $search_value) {
		// TODO: Ensure all columns to be returned in the final resultset are in this list e.g. merge all referenced/joined models columns
		$table_columns = $this->_get_table_columns(array($this->object_name()));// ORM::$_column_cache[$this->object_name()];

		$this->_search_list($search_field, $search_value, $table_columns);
		//var_dump($columns);exit;

		// perform other custom logic here

		return $table_columns;
	}
	
	public static function get_shelf_list_summary($search, $limit_count, $limit_offset) {
		$sql = 'SELECT SUM( fpro_supply_shelves_supply_purchases.supply_current_count ) AS shelf_current_count, '.
				'fpro_supply_locations.supply_location_name, fpro_supply_shelf_types.supply_shelf_type_name, fpro_supply_shelves . * '.
				'FROM fpro_supply_shelves '.
				'INNER JOIN fpro_supply_shelves_supply_purchases ON '.
				'fpro_supply_shelves_supply_purchases.supply_shelf_id = fpro_supply_shelves.supply_shelf_id '.
				'INNER JOIN fpro_supply_locations ON fpro_supply_locations.supply_location_id = fpro_supply_shelves.supply_location_id '.
				'INNER JOIN fpro_supply_shelf_types ON fpro_supply_shelf_types.supply_shelf_type_id = fpro_supply_shelves.supply_shelf_type_id '.
				'WHERE fpro_supply_shelves_supply_purchases.supply_current_count > 0 ';
		if (!empty($search)) {
			$sql .= 'AND fpro_supply_shelves.supply_shelf_name LIKE "%'.$search.'%" '.
					'OR fpro_supply_locations.supply_location_name LIKE "%'.$search.'%" ';
		}
		
		$sql .= 'GROUP BY fpro_supply_shelves_supply_purchases.supply_shelf_id ';
		
		if (is_numeric($limit_count) && is_numeric($limit_offset)) {
			$sql .= 'LIMIT '.$limit_offset.', '.$limit_count;
		}
		$query = DB::query(Database::SELECT, $sql);
		$result = $query->execute();
		return $result;
	}

	public static function get_shelf_locations_for_user($user_id) {
		return DB::select('*')->from('fpro_supply_shelves')->join('fpro_inventory_staff_locations')
				->on('fpro_supply_shelves.supply_location_id', '=', 'fpro_inventory_staff_locations.supply_location_id')
				->join('fpro_supply_locations')
				->on('fpro_supply_locations.supply_location_id', '=', 'fpro_inventory_staff_locations.supply_location_id')
				->where('fpro_inventory_staff_locations.user_id', '=', $user_id)
				->execute();
	}
	
	public static function get_shelf_locations() {
		return DB::select('*')->from('fpro_supply_shelves')->join('fpro_inventory_staff_locations')
				->on('fpro_supply_shelves.supply_location_id', '=', 'fpro_inventory_staff_locations.supply_location_id')
				->join('fpro_supply_locations')
				->on('fpro_supply_locations.supply_location_id', '=', 'fpro_inventory_staff_locations.supply_location_id')
				->execute();
	}
	
	public static function get_put_shelves() {
		return DB::select('*')->from('fpro_supply_shelves')
				->where('fpro_supply_shelves.supply_shelf_type_id', '=', '1')
				->execute();
	}

	public static function get_grouped_locations_for_supplyitem($supply_id) {
		$query = DB::query(Database::SELECT, 'SELECT SUM(supply_current_count) as supply_qty_on_hand, sum(`fpro_supply_shelves_supply_purchases`.`supply_current_count`) as shelf_qty, `fpro_supply_shelves_supply_purchases`.*, `fpro_supply_shelves`.*, `fpro_supply_locations`.`supply_location_name` '.
				'FROM `fpro_supply_shelves_supply_purchases` JOIN `fpro_supply_shelves` ON (`fpro_supply_shelves`.`supply_shelf_id` = `fpro_supply_shelves_supply_purchases`.`supply_shelf_id`) '.
				'JOIN `fpro_supply_locations` ON (`fpro_supply_shelves`.`supply_location_id` = `fpro_supply_locations`.`supply_location_id`) '.
				'WHERE `fpro_supply_shelves_supply_purchases`.`supply_id` = :supply_id '.
				'AND `fpro_supply_shelves_supply_purchases`.`supply_current_count` > 0 '.
				'GROUP BY `fpro_supply_shelves`.`supply_shelf_id`');
		$query->parameters(array(
			':supply_id' => $supply_id,
		));
		$result = $query->execute();
		return $result->as_array();
	}

	public static function get_aggregate_shelf_location_for_supplyitem($supply_id, $shelf_id) {
		$query = DB::query(Database::SELECT, 'SELECT SUM(supply_current_count) as supply_qty_on_hand, `fpro_supply_shelves_supply_purchases`.*, `fpro_supply_shelves`.*, `fpro_supply_locations`.`supply_location_name` '.
				'FROM `fpro_supply_shelves_supply_purchases` JOIN `fpro_supply_shelves` ON (`fpro_supply_shelves`.`supply_shelf_id` = `fpro_supply_shelves_supply_purchases`.`supply_shelf_id`) '.
				'JOIN `fpro_supply_locations` ON (`fpro_supply_shelves`.`supply_location_id` = `fpro_supply_locations`.`supply_location_id`) '.
				'WHERE `fpro_supply_shelves_supply_purchases`.`supply_id` = :supply_id '.
				'AND `fpro_supply_shelves_supply_purchases`.`supply_shelf_id` = :shelf_id '.
				'GROUP BY `fpro_supply_shelves_supply_purchases`.`supply_shelf_id`');
		$query->parameters(array(
			':supply_id' => $supply_id,
			':shelf_id' => $shelf_id,
		));
		$result = $query->execute();
		return $result->as_array();
	}

	public static function get_all_locations_for_supplyitem($supply_id, $shelf_id) {
		$query = DB::query(Database::SELECT, 'SELECT `fpro_supply_shelves_supply_purchases`.*, `fpro_supply_shelves`.* FROM '.
				'`fpro_supply_shelves_supply_purchases` JOIN `fpro_supply_shelves` ON (`fpro_supply_shelves`.`supply_shelf_id` = `fpro_supply_shelves_supply_purchases`.`supply_shelf_id`) '.
				'WHERE `fpro_supply_shelves_supply_purchases`.`supply_id` = :supply_id '.
				'AND `fpro_supply_shelves_supply_purchases`.`supply_shelf_id` = :shelf_id '.
				'AND `fpro_supply_shelves_supply_purchases`.`supply_current_count` > 0');
		$query->parameters(array(
			':supply_id' => $supply_id,
			':shelf_id' => $shelf_id,
		));
		$result = $query->execute();
		return $result->as_array();
	}

	public static function get_locations_for_supplyitem_on_shelf($supply_id, $shelf_id) {
		$query = DB::query(Database::SELECT, 'SELECT SUM(supply_current_count) as supply_qty_on_hand, `fpro_supply_shelves_supply_purchases`.*, `fpro_supply_shelves`.* '.
				'FROM `fpro_supply_shelves_supply_purchases` JOIN `fpro_supply_shelves` ON (`fpro_supply_shelves`.`supply_shelf_id` = `fpro_supply_shelves_supply_purchases`.`supply_shelf_id`) '.
				'WHERE `fpro_supply_shelves_supply_purchases`.`supply_id` = :supply_id '.
				'AND `fpro_supply_shelves_supply_purchases`.`supply_shelf_id` = :shelf_id '.
				' AND `fpro_supply_shelves_supply_purchases`.`supply_current_count` > 0 '.
				'GROUP BY `fpro_supply_shelves_supply_purchases`.`supply_shelf_id`');
		$query->parameters(array(
			':supply_id' => $supply_id,
			':shelf_id' => $shelf_id,
		));
		$result = $query->execute();
		return $result->as_array();
	}

	public function get_location_name() {
		return $this->location->supply_location_name;
	}

	public function get_supply_items($shelf_id = null) {
		if ($shelf_id == null) $shelf_id = $this->supply_shelf_id;
		$query = DB::query(Database::SELECT, 'SELECT '.
				'sum(fpro_supply_shelves_supply_purchases.supply_current_count) as shelf_qty, fpro_supplies.*, fpro_supply_shelves.*, '.
				'IF ((CONVERT( target_level, SIGNED ) - CONVERT( total_quantity, SIGNED )) < 0, 0, (CONVERT( target_level, SIGNED ) - CONVERT( total_quantity, SIGNED ))) as reorder_amount '.
				'FROM fpro_supply_shelves_supply_purchases INNER JOIN fpro_supply_shelves '.
				'ON  (`fpro_supply_shelves`.`supply_shelf_id` = `fpro_supply_shelves_supply_purchases`.`supply_shelf_id`) '.
				'INNER JOIN fpro_supplies ON fpro_supplies.supply_id = fpro_supply_shelves_supply_purchases.supply_id '.
				'WHERE `fpro_supply_shelves`.`supply_shelf_id` = :shelf_id '.
				'AND fpro_supply_shelves_supply_purchases.supply_current_count > 0 '.
				'GROUP BY fpro_supplies.supply_id');
		$query->parameters(array(
			':shelf_id' => $shelf_id,
		));
		$result = $query->execute();
		return $result->as_array();
	}

	public function get_supplies_subtotal() {
		$shelf_id = $this->supply_shelf_id;
		$query = DB::query(Database::SELECT, 'SELECT DISTINCT fpro_supply_shelves_supply_purchases.supply_id, '.
				'SUM(total_quantity) FROM fpro_supply_shelves_supply_purchases '.
				'INNER JOIN fpro_supply_shelves '.
				'ON  (`fpro_supply_shelves`.`supply_shelf_id` = `fpro_supply_shelves_supply_purchases`.`supply_shelf_id`) '.
				'INNER JOIN fpro_supplies ON fpro_supplies.supply_id = fpro_supply_shelves_supply_purchases.supply_id '.
				'WHERE `fpro_supply_shelves`.`supply_shelf_id` = :shelf_id '.
				'AND fpro_supply_shelves_supply_purchases.supply_current_count > 0 LIMIT 1');
		$query->parameters(array(
			':shelf_id' => $shelf_id,
		));
		$result = $query->execute();
		$dt = $result->as_array();
		if (empty($dt)) {
			return 0;
		} else {
			return $dt[0]['SUM(total_quantity)'];
		}
	}

	public function get_supply_item_count() {
		$shelf_id = $this->supply_shelf_id;
		$query = DB::query(Database::SELECT, 'SELECT DISTINCT fpro_supply_shelves_supply_purchases.supply_id, '.
				'COUNT(*) FROM fpro_supply_shelves_supply_purchases '.
				'INNER JOIN fpro_supply_shelves '.
				'ON  (`fpro_supply_shelves`.`supply_shelf_id` = `fpro_supply_shelves_supply_purchases`.`supply_shelf_id`) '.
				'INNER JOIN fpro_supplies ON fpro_supplies.supply_id = fpro_supply_shelves_supply_purchases.supply_id '.
				'WHERE `fpro_supply_shelves`.`supply_shelf_id` = :shelf_id '.
				'AND fpro_supply_shelves_supply_purchases.supply_current_count > 0 LIMIT 1');
		$query->parameters(array(
			':shelf_id' => $shelf_id,
		));
		$result = $query->execute();
		$dt = $result->as_array();
		if (empty($dt)) {
			return 1;
		} else {
			return $dt[0]['COUNT(*)'] + 1;
		}
	}
	
	public static function get_shelf_supply_items() {
		$query = DB::query(Database::SELECT, 'SELECT fpro_supplies . * , fpro_supply_shelves . * ,
				concat(substr(supply_name, 1, 23), "...") as short_supply_name 
				FROM fpro_supply_shelves_supply_purchases
				INNER JOIN fpro_supply_shelves ON (  `fpro_supply_shelves`.`supply_shelf_id` =  `fpro_supply_shelves_supply_purchases`.`supply_shelf_id` ) 
				INNER JOIN fpro_supplies ON fpro_supplies.supply_id = fpro_supply_shelves_supply_purchases.supply_id
				WHERE fpro_supply_shelves_supply_purchases.supply_current_count >0
				GROUP BY fpro_supplies.supply_id
				LIMIT 0 , 30');
		$result = $query->execute();
		return $result->as_array();
	}

	public function get_supply_item_count_for_location() {
		$location_id = $this->supply_location_id;
		$query = DB::query(Database::SELECT, 'SELECT DISTINCT fpro_supply_shelves_supply_purchases.supply_id, '.
				'COUNT(*) FROM fpro_supply_shelves_supply_purchases '.
				'INNER JOIN fpro_supply_shelves '.
				'ON  (`fpro_supply_shelves`.`supply_shelf_id` = `fpro_supply_shelves_supply_purchases`.`supply_shelf_id`) '.
				'INNER JOIN fpro_supplies ON fpro_supplies.supply_id = fpro_supply_shelves_supply_purchases.supply_id '.
				'WHERE `fpro_supply_shelves`.`supply_location_id` = :location_id '.
				'AND fpro_supply_shelves_supply_purchases.supply_current_count > 0 LIMIT 1');
		$query->parameters(array(
			':location_id' => $location_id,
		));
		$result = $query->execute();
		$dt = $result->as_array();
		if (empty($dt)) {
			return 1;
		} else {
			return $dt[0]['COUNT(*)'] + 1;
		}
	}

}
