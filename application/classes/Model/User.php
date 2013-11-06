<?php defined('SYSPATH') or die('No direct script access.');
class Model_User extends Model_Auth_User {

	//use ACL_Trait_User;

	/**
	 * Relationships that should always be joined
	 * @var array
	 */
	protected $_load_with = array('personnel_info', 'client_info', 'supplier_info');

	/**
	 * A user has many tokens and roles
	 *
	 * @var array Relationhips
	 */
	protected $_has_many = array(
		'user_tokens' => array('model' => 'User_Token'),
		'roles'       => array('model' => 'Role', 'through' => 'roles_users'),
	);

	/**
	 * A User may be a personnel
	 *
	 */
	protected $_has_one = array(
		'personnel_info' => array('model' => 'Personnel', 'foreign_key' => 'user_id'),
		'client_info' => array('model' => 'Client', 'foreign_key' => 'user_id'),
		'supplier_info' => array('model' => 'Supplier', 'foreign_key' => 'user_id')
	);

	/**
	 * Rules for the user model. Because the password is _always_ a hash
	 * when it's set,you need to run an additional not_empty rule in your controller
	 * to make sure you didn't hash an empty string. The password rules
	 * should be enforced outside the model or with a model helper method.
	 *
	 * @return array Rules
	 */
	public function rules()
	{
		return array(
			'username' => array(
				array('not_empty'),
				array('max_length', array(':value', 32)),
				array(array($this, 'unique'), array('username', ':value')),
			),
			'password' => array(
				array('not_empty'),
			),
			'email' => array(
				array('not_empty'),
				array('email'),
				array(array($this, 'unique'), array('email', ':value')),
			),
		);
	}

	/**
	 * Check if the user has the specified permission.
	 *
	 * @since 1.0
	 * @param int|ACL_Model_Permission $permission
	 * @return bool
	 */
	public function can($permission)
	{
		return $this->_check_permission($permission);
	}

	/**
	 * Check if the user has all the specified permissions.
	 *
	 * @since 1.0
	 * @param array $permissions An array of ACL_Model_Permission or Permission PK-s
	 * @return bool True if the user has all the specified permissions
	 */
	public function has_permissions(array $permissions)
	{
		foreach ($permissions as $permission) {
			if (! $this->_check_permission($permission)) {
				return FALSE;
			}
		}
		return TRUE;
	}

	/**
	 * Check if the user has at least one of the specified permissions.
	 * @since 1.0
	 * @param array $permissions An array of ACL_Model_Permission or Permission PK-s
	 * @return bool True if the user had one of the permissions
	 */
	public function has_any_permission(array $permissions)
	{
		foreach ($permissions as $permission) {
			if ($this->_check_permission($permission)) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Check if a user has the specified permission.
	 *
	 * @param int $permission
	 * @throws InvalidArgumentException
	 * @see Model_Permission
	 * @return bool
	 * @since 2.0
	 */
	private function _check_permission($permission)
	{
		if (! is_int($permission) && ! $permission instanceof ACL_Model_Permission) {
			throw new InvalidArgumentException('Expected an instance of ACL_Model_Permission or an integer.');
		}

		// Todo: Do this with one DB::select query
		foreach ($this->roles->find_all() as $role) {
			if ($role->can($permission)) {
				return TRUE;
			}
		}
		return FALSE;
	}
}
