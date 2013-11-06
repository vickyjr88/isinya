<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing resources table
 *
 * @version 02 - Joseph Bosire 2013-06-28
 
 * PHP version 5
 */
class Model_Resource extends Model_Base {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'resources';
	
	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'resource_id';
	
	/**
	 * Model's relationships
	 * @array
	 */
	protected $_has_many = array(
				'permissions' => array('model' => 'Permission', 'foreign_key' => 'resource_id')
				);
	
	public function get_resources(){
		return $this->find_all();
	}
	public function get_permissions(){
		$permissions = $this->permissions->find_all();
		foreach ($permissions as $permission) {
			$permission->resource_permission = strtolower($permission->resource_permission);
		}
		return $permissions;
	}
	


}
