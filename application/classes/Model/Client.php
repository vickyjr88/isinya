<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing clients table
 *
 * @version 01 - Hezron Obuchele 2013-02-11
 *
 * PHP version 5
 */
class Model_Client extends Model_Base {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_clients';
	
	/**
	* Model's primary key name
	 * @string
	 */	
	protected $_primary_key = 'client_id';
	protected $_belongs_to = array('user_info'=>array('model'=>'User','foreign_key'=>'user_id'));
	/**
	 * Model's relationships
	 * @array
	 */
	/*protected $_has_many = array(
							'job_tickets' => array('model' => 'ticket', 'foreign_key' => 'client_id')
							);*/
							
	/**
	 * Setup validation rules
	 *
	 * @return array
	 */
	public function rules() {
		// TODO: See if there are any rules to be added
		return array(
			'client_name' => array(array('not_empty'), array('regex', array(':value', '/^[a-zA-Z ]*$/'))),
			'client_contact_person' => array(array('regex', array(':value', '/^[a-zA-Z ]*$/'))),
			'client_telephone' => array(array('not_empty')),
			'client_email_address' => array(array('not_empty'), array('email')),
			'client_postal_address' => array(array('not_empty')),
		
		);
	}

	/**
	 * Checks if the specified Client ID is of type INT and exists in the database
	 *
	 * @param	int	$client_id  Database id of the client to be looked up
	 * @return	bool
	 */
	/*public static function is_valid_client($client_id) {
		return (!is_object($client_id) AND intval($client_id) > 0) ? self::factory('client', intval($client_id)) -> loaded : false;
	}*/
	public function get_role(){
		$role = $this->user_info->roles->where('id','!=',1)->find();
		return intval($role->id);
	}
	public function get_user_type(){
		$role = $this->user_info->roles->where('id','!=',1)->find();
		$user_type = ORM::factory('Role',$role->id);
		return $user_type->name;
	}

}
