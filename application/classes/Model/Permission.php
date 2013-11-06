<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing permissions table
 *
 * @version 02 - Joseph Bosire 2013-06-28
 
 * PHP version 5
 */
class Model_Permission extends ACL_Model_Permission {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'permissions';
	
	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'id';
	
	
}
