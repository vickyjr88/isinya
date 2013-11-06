<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model Personnel that links to personnel t able
 *
 * PHP version 5.3
 *
 * @category  Model
 * @package   Efficiency_Pro
 * @author    Joseph Bosire <kashboss@gmail.com>
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.0.2
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
class Model_Personnel extends Model_Base {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_personnel';
	
	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'personnel_id';
	
	/**
	 * Model's relationships
	 * @array
	 */
	protected $_has_many = array(
				'notes' => array('model' => 'PersonnelNote', 'foreign_key' => 'personnel_id'),
				'tasks' => array('model' => 'Task', 'foreign_key' => 'personnel_id'),
				//'training_sessions' => array('model' => 'Training', 'through' => 'fpro_training', 'foreign_key' => 'personnel_id'),
				);
	protected $_belongs_to = array('user_info'=>array('model'=>'User','foreign_key'=>'user_id'));

	/**
	 * Setup validation rules
	 *
	 * @return array
	 */
	public function rules() {
		// TODO: See if there are any rules to be added
		return array(
			'personnel_name' => array(
				array('not_empty')
				// array('regex', array(':value', '/^[a-zA-Z ]*$/'))
				),
			// 'peronnel_status' => array(
				// array('exact_length', array(':value', 1)),
				// array('in_array', array(':value', array('0', '1')))
				// ),
			// 'peronnel_title' => array(array('not_empty')),
			// 'peronnel_active' => array(
				// array('exact_length', array(':value', 1)),
				// array( 'in_array', array(':value', array('0', '1')))
				// ),
			//'peronnel_telephone' => array(array('not_empty')),
			//'peronnel_email_address' => array(array('not_empty'), array('email')),
		);
	}

	/**
	 * Checks if the specified Personnel ID is of type INT and exists in the database
	 *
	 * @param	int	$personnel_id  Database id of the personnel to be looked up
	 * @return	bool
	 */
	public static function is_valid_personnel($personnel_id) {

		return (!is_object($personnel_id) AND intval($personnel_id) > 0) ? self::factory('personnel', intval($personnel_id)) -> loaded : false;
	}
	public function filter_personnel_list($search_field, $search_value){
		// TODO: Ensure all columns to be returned in the final resultset are in this list e.g. merge all referenced/joined models columns
		$table_columns = $this->_get_table_columns(array($this->object_name()));// ORM::$_column_cache[$this->object_name()];
		// Use buit-in filter across multiple columns
		$this->_search_list($search_field, $search_value, $table_columns);
		// OR perform other custom logic here
	}
	
	public function get_personnel_list_columns() {
		// TODO: Ensure all columns to be returned in the final resultset are in this list e.g. merge all referenced/joined models columns
		$table_columns = $this->_get_table_columns(array($this->object_name()));// ORM::$_column_cache[$this->object_name()];
		return $table_columns ;
	}
	public function get_role(){
		$role = $this->user_info->roles->where('id','!=',1)->find();
		return intval($role->id);
	}
	public function get_user_type(){
		$role = $this->user_info->roles->where('id','!=',1)->find();
		$user_type = ORM::factory('Role',$role->id);
		return $user_type->name;
	}

	public function get_personnel_avatar() {
		if(Helper_File::check_file_exists(DOCROOT.'assets/avatars/',$this->personnel_avatar)) {
			return $this->personnel_avatar;
		}
		return FALSE;
	}

}
