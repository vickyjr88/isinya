<?php

	class Model_SupplyShrink extends Model_Base {
		/**
		 * Model's table
		 * @string */
		protected $_table_name = 'fpro_supply_shrink';

		/**
		 * Model's primary key name
		 * @string
		 */
		protected $_primary_key = 'supplies_shrink_id';

		public static function _MARKED_FOR_REMOVAL_get_item_id($purchase_id){
			 $query=DB::query(Database::SELECT,'SELECT fpro_supplies.supply_id FROM fpro_supplies
					JOIN fpro_supply_purchases ON fpro_supplies.supply_id = fpro_supply_purchases.supply_id
					JOIN fpro_supply_shelves_supply_purchases ON fpro_supply_shelves_supply_purchases.supply_purchase_id = fpro_supply_purchases.supply_purchase_id
					WHERE fpro_supply_shelves_supply_purchases.supply_shelves_supply_purchases_id='.$purchase_id)->execute();
			foreach($query as $q){
				$id=$q['supply_id'];
			}
			return $id;
		}
	}
