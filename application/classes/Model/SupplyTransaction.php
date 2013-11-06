<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing supplies table
 *
 * @version 01 - Joseph Bosire 2013-06-08
 *
 * PHP version 5
 */
class Model_SupplyTransaction extends Model_Base {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_supply_transactions';

	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'id';

	protected $_belongs_to = array(
		'personnel' => array('model' => 'Personnel', 'foreign_key' => 'personel_id'),
		'supply_transaction_type' => array('model' => 'SupplyTransactionType', 'foreign_key' => 'supply_transactions_type_id'),
	);

	public static $addition_type_id = 1;
	public static $move_type_id = 2;
	public static $shrink_type_id = 3;
	public static $allocation_type_id = 4;

	public function get_supplies_transactions_list($search_field, $search_value){
		// TODO: Ensure all columns to be returned in the final resultset are in this list e.g. merge all referenced/joined models columns
		$table_columns = $this->_get_table_columns(array($this->object_name()));// ORM::$_column_cache[$this->object_name()];

		$this->_search_list($search_field, $search_value, $table_columns);
		//var_dump($columns);exit;

		// perform other custom logic here

		return $table_columns;
	}

	public function get_transactions($supply_id) {
		$query = DB::query(Database::SELECT, 'SELECT fpro_supply_transactions.*, fpro_personnel.*, fpro_supply_transactions_type.name '.
				'FROM `fpro_supply_transactions` JOIN `fpro_supply_transactions_type` '.
				'ON (`fpro_supply_transactions`.`supply_transactions_type_id` = `fpro_supply_transactions_type`.`id`) '.
				'JOIN `fpro_personnel` ON (`fpro_personnel`.`user_id` = `fpro_supply_transactions`.`user_id`) '.
				'JOIN `fpro_supply_transactions_supplies` ON (`fpro_supply_transactions_supplies`.`supply_transaction_id` = `fpro_supply_transactions`.`id`) '.
				'WHERE `fpro_supply_transactions_supplies`.`supply_id` = :supply_id ORDER BY fpro_supply_transactions.id ASC');
		
		$query->parameters(array(
			':supply_id' => $supply_id,
		));
		$result = $query->execute();
		return $result->as_array();
	}

	public static function get_transactions_by_type($transaction_type) {
		$query = DB::query(Database::SELECT, 'SELECT fpro_supply_transactions.*, fpro_personnel.*, fpro_supply_transactions_type.name '.
				'FROM `fpro_supply_transactions` JOIN `fpro_supply_transactions_type` '.
				'ON (`fpro_supply_transactions`.`supply_transactions_type_id` = `fpro_supply_transactions_type`.`id`) '.
				'JOIN `fpro_personnel` ON (`fpro_personnel`.`user_id` = `fpro_supply_transactions`.`user_id`) '.
				'JOIN `fpro_supply_transactions_supplies` ON (`fpro_supply_transactions_supplies`.`supply_transaction_id` = `fpro_supply_transactions`.`id`) '.
				'WHERE `fpro_supply_transactions`.`supply_transactions_type_id` = :transaction_type ORDER BY fpro_supply_transactions.id DESC');
		$query->parameters(array(
			':transaction_type' => $transaction_type,
		));
		$result = $query->execute();
		return $result->as_array();
	}

	public static function get_pull_summary() {
		$query = DB::query(Database::SELECT, 'SELECT
				IF(recipient_client.client_name is null, recipient_personnel.personnel_name, recipient_client.client_name) as recipient_name,
				recipient_client.*, recipient_personnel.*, frpo_allocations.*, fpro_supply_transactions.*, `fpro_supplies`.*, fpro_personnel.*
				FROM `frpo_allocations` INNER JOIN `fpro_supply_shelves_supply_purchases`
				ON `fpro_supply_shelves_supply_purchases`.`supply_shelves_supply_purchases_id` = `frpo_allocations`.`sssp_id`
				INNER JOIN `fpro_supplies` ON `fpro_supplies`.`supply_id` = `fpro_supply_shelves_supply_purchases`.`supply_id`
				INNER JOIN fpro_supply_allocations ON fpro_supply_allocations.supply_allocation_id = frpo_allocations.supply_allocation_id
				LEFT JOIN fpro_personnel as recipient_personnel ON recipient_personnel.personnel_id = fpro_supply_allocations.personnel_id
				LEFT JOIN fpro_clients as recipient_client ON recipient_client.client_id = fpro_supply_allocations.client_id
				INNER JOIN fpro_supply_transactions ON fpro_supply_transactions.fk_key_field_id = frpo_allocations.supply_allocation_id
				JOIN `fpro_personnel` ON ( `fpro_personnel`.`user_id` = `fpro_supply_transactions`.`user_id` )
				WHERE supply_transactions_type_id = 4 GROUP BY allocation_id ORDER BY frpo_allocations.allocation_id DESC');
		$result = $query->execute();
		return $result->as_array();
	}

	public static function record_transaction($supply_id, $transaction_type, $rec_id, $personnel_id, $details, $amount) {
		$SupplyTransaction = ORM::factory('SupplyTransaction');
		$SupplyTransaction->supply_transactions_type_id = $transaction_type;
		$SupplyTransaction->fk_key_field_id = $rec_id;
		$SupplyTransaction->user_id = $personnel_id;
		$SupplyTransaction->time = Date('Y-m-d H:i:s');
		$SupplyTransaction->details = $details;
		$SupplyTransaction->amount = $amount;
		$SupplyTransaction->save();
		$supplyTransactionId = $SupplyTransaction->id;

		if (is_array($supply_id)) {
			foreach ($supply_id as $s_id) {
				$SupplyTransactionSupply = ORM::factory('SupplyTransactionSupply');
				$SupplyTransactionSupply->supply_transaction_id = $supplyTransactionId;
				$SupplyTransactionSupply->supply_id = $s_id;
				$SupplyTransactionSupply->save();
			}
		} else {
			$SupplyTransactionSupply = ORM::factory('SupplyTransactionSupply');
			$SupplyTransactionSupply->supply_transaction_id = $supplyTransactionId;
			$SupplyTransactionSupply->supply_id = $supply_id;
			$SupplyTransactionSupply->save();
		}

		return $supplyTransactionId;
	}
}
