<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @since 1.0
 * @author Ando Roots <ando@roots.ee>
 */
class ACL_Model_Permission extends ORM {

	protected $_has_many = array(
		'roles'=> array('through'=> 'permissions_roles')
	);
}