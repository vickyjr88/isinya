<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing supplies table
 *
 * @version 01 - Joseph Bosire 2013-06-08
 *
 * PHP version 5
 */
class Model_Supply extends Model_Base {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_supplies';

	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'supply_id';

	protected $_belongs_to = array(
		'supply_unit_type' => array('model' => 'SupplyType', 'foreign_key' => 'supply_type_id'),
	);

	protected $_has_many = array(
		'suppliers' => array(
			'model'   => 'Supplier',
			'through' => 'fpro_supply_purchases',
		),
		'comments' => array(
			'model'   => 'SupplyNote',
			'foreign_key' => 'supply_id',
		),
		'supply_orders' => array('model' => 'SupplyPurchase', 'foreign_key' => 'supply_id'),
	);

	public function get_last_purchase() {
		return $this->supply_orders->order_by($this->supply_orders->primary_key(), 'DESC')->find();
	}

	public function find_all_fields($fields) {
		$query = DB::query(Database::SELECT, 'SELECT '.$fields.' FROM fpro_supplies');
		$result = $query->execute();
		return $result->as_array();
	}

	public function update_shelf_quantities_by_sssp_id ($values) {
		if (Arr::is_assoc($values)) {
			foreach ($values as $sssp_id => $qty) {
				$query = DB::query(Database::UPDATE, 'UPDATE fpro_supply_shelves_supply_purchases SET supply_current_count = supply_current_count - :qty WHERE supply_shelves_supply_purchases_id = :sssp_id')
					->parameters(array(
						':sssp_id' => $sssp_id,
						':qty' => $qty
					));
				$r = $query->execute();
				return ($r != 0);
			}
			return true;
		}
		return false;
	}

	public function get_supplies_list($search_field, $search_value) {
		// TODO: Ensure all columns to be returned in the final resultset are in this list e.g. merge all referenced/joined models columns
		$table_columns = $this->_get_table_columns(array($this->object_name()));// ORM::$_column_cache[$this->object_name()];

		$this->_search_list($search_field, $search_value, $table_columns);
		// perform other custom logic here
		return $table_columns;
	}

	public static function get_shelves_totalquantity_per_item($id) {
		return DB::query(Database::SELECT,'SELECT SUM( supply_current_count ) AS total
				FROM fpro_supplies
				JOIN fpro_supply_shelves_supply_purchases ON fpro_supply_shelves_supply_purchases.supply_id = fpro_supplies.supply_id
				WHERE fpro_supplies.supply_id = '.$id)->execute();
	}

	public function get_level_indicator() {
		$level_indicator = 'OK';
		$target = ($this->target_level) ? $this->target_level : $this->total_quantity;
		$percentage = $this->total_quantity / $target * 100;
		if ($this->total_quantity <= 0) {
			$level_indicator = 'OUT OF STOCK';
		} else if($percentage <= $this->percentage_limit) {
			$level_indicator = 'RUNNING LOW';
		} else if($this->total_quantity > $target) {
			$level_indicator = 'OVERSTOCK ';
		}
		return $level_indicator;
	}

	public function get_reorder_amount(){
		$reorder_amount=$this->target_level-$this->total_quantity;
		if($reorder_amount<=0){
			$reorder_amount=0;

		}
		return $reorder_amount;
	}

	public function get_level_indicator_badge() {
		$level_indicator = 'success';
		$target = ($this->target_level)?$this->target_level:$this->total_quantity;
		$percentage = $this->total_quantity/$target * 100;
		if($this->total_quantity <= 0) {
			$level_indicator = 'important';
		} else if($percentage <= $this->percentage_limit) {
			$level_indicator = 'yellow';
		} else if($this->total_quantity > $target) {
			$level_indicator = 'purple ';
		}
		return $level_indicator;
	}

	public static function get_inventory_to_reorder() {
		return DB::query(Database::SELECT,'SELECT '.
				'fpro_supplies.*, (CONVERT( target_level, SIGNED ) - CONVERT( total_quantity, SIGNED )) as reorder_amount, '.
				'IFNULL(GROUP_CONCAT(DISTINCT fpro_suppliers.supplier_name SEPARATOR ", "), "N/A") as supplier_name, '.
				'(total_quantity/target_level*100) as order_level '.
				'FROM fpro_supplies '.
				'LEFT JOIN fpro_supply_purchases ON fpro_supplies.supply_id = fpro_supply_purchases.supply_id '.
				'LEFT JOIN fpro_suppliers ON fpro_suppliers.supplier_id=fpro_supply_purchases.supplier_id  '.
				'WHERE total_quantity<reorder_level '.
				'GROUP BY fpro_supplies.supply_id '.
				'HAVING reorder_amount > 0 '

			)->execute();
	}
}
