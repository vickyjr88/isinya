<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing supply allocations table
 *
 * @version 01 - Joseph Bosire 2013-06-08
 *
 * PHP version 5
 */
class Model_SupplyAllocation extends Model_Base {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_supply_allocations';

	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'supply_allocation_id';
}

