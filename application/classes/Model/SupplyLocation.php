<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing supply locations table
 *
 * @version 01 - Joseph Bosire 2013-06-08
 *
 * PHP version 5
 */
class Model_SupplyLocation extends Model_Base {
	/**
	 * Model's relationships
	 * @array
	 */
	protected $_has_many = array(
			'shelf' => array('model' => 'SupplyShelf', 'foreign_key' => 'supply_location_id')
		);

	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_supply_locations';

	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'supply_location_id';

	public function get_supply_location_list($search_field, $search_value){
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
			'supply_location_name' => array(array('not_empty')),
		);
	}

	public function get_supply_shelves($location_id = null) {
		if ($location_id == null) $location_id = $this->supply_location_id;
		return ORM::factory('SupplyShelf')->where('supply_location_id','=', $location_id)->find_all();
	}

	public function get_supply_items($location_id = null) {
		if ($location_id==null) $location_id = $this->supply_location_id;
		$query = DB::query(Database::SELECT, 'SELECT DISTINCT fpro_supply_shelves_supply_purchases.supply_id, '.
				'fpro_supplies.*, fpro_supply_shelves.*, '.
				'IF ((CONVERT( target_level, SIGNED ) - CONVERT( total_quantity, SIGNED )) < 0, 0, (CONVERT( target_level, SIGNED ) - CONVERT( total_quantity, SIGNED ))) as reorder_amount '.
				'FROM fpro_supply_shelves_supply_purchases INNER JOIN fpro_supply_shelves '.
				'ON  (`fpro_supply_shelves`.`supply_shelf_id` = `fpro_supply_shelves_supply_purchases`.`supply_shelf_id`) '.
				'INNER JOIN fpro_supplies ON fpro_supplies.supply_id = fpro_supply_shelves_supply_purchases.supply_id '.
				'WHERE `fpro_supply_shelves`.`supply_location_id` = :location_id '.
				'AND fpro_supply_shelves_supply_purchases.supply_current_count > 0 ORDER BY supply_shelf_name ');
		$query->parameters(array(
			':location_id' => $location_id,
		));
		$result = $query->execute();
		return $result->as_array();
	}

	public function get_supplies_subtotal() {
		$location_id = $this->supply_location_id;
		$query = DB::query(Database::SELECT, 'SELECT DISTINCT fpro_supply_shelves_supply_purchases.supply_id, '.
				'SUM(total_quantity) FROM fpro_supply_shelves_supply_purchases '.
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
			return 0;
		} else {
			return $dt[0]['SUM(total_quantity)'];
		}
	}

	public function get_supply_item_count() {
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

	public function get_report_row_span_height() {
		$location_id = $this->supply_location_id;
		$query = DB::query(Database::SELECT, 'SELECT DISTINCT fpro_supply_shelves_supply_purchases.supply_id, '.
				'COUNT(*) FROM fpro_supply_shelves_supply_purchases '.
				'INNER JOIN fpro_supply_shelves '.
				'ON  (`fpro_supply_shelves`.`supply_shelf_id` = `fpro_supply_shelves_supply_purchases`.`supply_shelf_id`) '.
				'INNER JOIN fpro_supplies ON fpro_supplies.supply_id = fpro_supply_shelves_supply_purchases.supply_id '.
				'WHERE `fpro_supply_shelves`.`supply_location_id` = :location_id '.
				'AND 1 LIMIT 1');
		$query->parameters(array(
			':location_id' => $location_id,
		));
		$result = $query->execute();
		$dt = $result->as_array();
		if (empty($dt)) {
			$item_count = 0;
		} else {
			$item_count = $dt[0]['COUNT(*)'];
		}
		return $item_count + $this->get_non_empty_shelf_count() + 1;
	}

	public function get_non_empty_shelf_count() {
		$location_id = $this->supply_location_id;
		$query = DB::query(Database::SELECT, 'SELECT DISTINCT `fpro_supply_shelves`.`supply_shelf_id`, '.
				'COUNT(*) FROM fpro_supply_shelves_supply_purchases '.
				'INNER JOIN fpro_supply_shelves '.
				'ON  (`fpro_supply_shelves`.`supply_shelf_id` = `fpro_supply_shelves_supply_purchases`.`supply_shelf_id`) '.
				'WHERE `fpro_supply_shelves`.`supply_location_id` = :location_id '.
				' LIMIT 1');
		$query->parameters(array(
			':location_id' => $location_id,
		));
		$result = $query->execute();
		$dt = $result->as_array();
		if (empty($dt)) {
			return 0;
		} else {
			return $dt[0]['COUNT(*)'];
		}
	}
}
