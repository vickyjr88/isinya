<?php defined('SYSPATH') or die('No direct script access.');
class Model_Role extends ACL_Model_Role {
public function get_role_permissions(){
		$permissions = $this->permissions->find_all();
		
		return $permissions;
	}
// $permissions = ORM::factory('Permission')->join('permission_roles')
		// ->on('id','=','permission_id')->where('role_id','=',$role_id)->find_all();
}