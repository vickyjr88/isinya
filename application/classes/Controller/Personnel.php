<?php defined('SYSPATH') or die('No direct script access.'); 
/**
 * Controller that handles all personnel related requests
 *
 * PHP version 5.3
 *
 * @category  Controllers
 * @package   Efficiency_Pro
 * @author    Joseph Bosire <kashboss@gmail.com>
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.0.5
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
class Controller_Personnel extends Controller_Site
{
	
	protected $role_required = array('login' );

	protected $permission_actions = array(
			'PERSONNEL_VIEW' => array(
					'index','personnel_export_excel_address_book','personnel_address_book'
				),
			'PERSONNEL_EDIT' => array(
					'edit'
				),
			'PERSONNEL_DELETE' => array(
					'delete', 
					'delete_avatar'
				)
		);
		
		
	/**
	 * Function to display index page
	 * @return void
	 * @author Joseph Bosire
	 */	 
	public function action_index() {
		$this->_template->set('page_title', 'Personnel');
		$this->_template->set('page_info', 'view and manage personnel');
		$personnel = ORM::factory('Personnel')->where('delete_status','=','0');
		// Pass columns to use to filter search field by
	    $search_field = array(
			    'personnel_name', 
			    'personnel_email_address',
			    'personnel_id'
			);
		$search_value = $this->_search_context;
		$personnel->filter_personnel_list($search_field, $search_value);
		// // Send back the list
		$content_array = $this->group_personnel($personnel->find_all());			
		$this->_template->set('content_data', $content_array);
		$this->_set_search_context(I18n::get("nav.site.search.personnel"));
		$this->_set_content('personnel');
	}


	/**
	 * Function to edit personnel detailse
	 * @return void
	 * @author Joseph Bosire
	 */		
	public function action_edit() {
		$id='';
		if($this->request->query()){
			$id=$this->request->param('id');
		}else if($this->request->post()){
			$id = $this->request->post('id');
		}
		$employee = ORM::factory('Personnel', $id);		
		// This line was commented out because of many js/view errors on calling this url.. see lines further below
		// $this->_template->set('ajax_save_url', $this->_template->current_url);
		// view flag to check whether save was unsuccessfull after a post
		$this->_template->set('save_failed', 1);
		if ($this->request->post()) {
			// bind values to table columns
			$post = $this->request->post();			
			// STEP1: Register User Information
			$user = ORM::factory('User',$employee->user_id);
			$user->username = $post['username'];
			$user->email = $post['personnel_email_address'];
			if(!$employee->user_id){
				$user->password = $post['password'];
			}
			$user->save();						
			if($this->request->post('user_type')){
				$user->remove('roles');	
				$user->add('roles', ORM::factory('Role',$this->request->post('user_type')));
			}						
			$user->remove('roles',ORM::factory('Role')->where('name', '=', 'login')->find());	
			$user->add('roles', ORM::factory('Role')->where('name', '=', 'login')->find());							
			// STEP2: REgister Personnel Information
	        $employee->values($this->request->post());
			try {
				// add user_id to personnel record
				$employee->user_id = $user->id;
				// Upload avatar
				if(isset($_FILES['avatar'])){
					$directory =  DOCROOT.'assets/avatars/';
					// if(isset($employee->personnel_avatar)){
						// // Delete the previous avatar file
           				 // unlink($directory.$employee->personnel_avatar);
					// }
					$file = $_FILES['avatar'];
					$avatar =  $this->save_image($file,48,48,$directory);
					$employee->personnel_avatar = $avatar;
				}else{
					if(!$employee->personnel_avatar)
						$employee->personnel_avatar= "default.png";
				}
				//save personnel record
				$employee->save();			
	            // Update personnel
	            $this->_set_msg($employee->personnel_name.' record was saved!', 'success', true);
				// this flag is used to know whether or not a new record was successfully added
				if (!intval($id)){
	            	$this->_template->set('added_record', 1);
				}
				$this->_template->set('save_failed', 0);
				$id = $employee->personnel_id;
					
			} catch (Exception $e) {
				$errors = array();
				if ($e instanceof ORM_Validation_Exception) {
					$errors = $e->errors('models');
				}
				$this->_set_msg('Please correct the errors below.', 'error', $errors);
			}
		}
		// More of corective url adjustments in case JS screws this up
		if (!intval($id)) {
			$this->_template->set('addform', 1);
			$this->_template->set('ajax_save_url', $this->_template->controller_base_url . 'edit');
		} else {
			$this->_template->set('ajax_save_url', $this->_template->controller_base_url . 'edit/' . $id);
		}
	
		$this->_template->set('profile_mode', 1);		
		$this->_template->set('content_data', $employee);
		$roles = ORM::factory('Role')->where('name','!=','login')->find_all();
		$this->_template->set('roles', $roles);
		$this->_set_content('personnel_edit');
	}
	

	/**
	 * Function to delete personnel details
	 *
	 * @return void
	 * @author Joseph Bosire
	 */	
	public function action_delete() {
		$id = $this->request->param('id');
		if($id) {
			$personnel = ORM::factory('Personnel', $id);
			$data = array('id'=>$personnel->personnel_id);
			$user = ORM::Factory('User',$personnel->user_id);
			if ($personnel->loaded()){
				try {
					$personnel->delete_status=1;
					$user->delete_status=1;
					$personnel->save();
					$user->save();
					$this->_set_msg(' Personnel record was deleted', 'success',$data);
				} catch (Exception $e) {
					$this->_set_msg('Could not delete personnel because this would wipe their history!', 'error',$data);
				}
			}
			$this->redirect($this->request->referrer());
		}
	}
	
	
	/**
	 * Function to delete personnel avatar
	 *
	 * @return void
	 * @author Joseph Bosire
	 */	
	public function action_delete_avatar() {
		$id = $this->request->param('id');
		if($id) {
			$personnel = ORM::factory('Personnel', $id);
			$data = array('id'=>$personnel->personnel_id);			
			if ($personnel->loaded()) {
				try {
					$file =  DOCROOT.'assets/avatars/'.$personnel->personnel_avatar;
					unlink($file);					
					$personnel->personnel_avatar= "default.png";					
					$personnel->save();					
					$this->_set_msg(' Avatar was deleted', 'success',$data);
				} catch (Exception $e) {
					$this->_set_msg('Could not delete avatar', 'error',$data);
				}
			}			
		}		
	}
	
	
	/**
	 * Function to group personnel alphabetically
	 *
	 * @return array (alphabetically grouped personnel array)
	 * @author Joseph Bosire
	 */	
	public function group_personnel($personnel){
		$alphabets = array(
				'a',
				'b',
				'c',
				'd',
				'e',
				'f',
				'g',
				'h',
				'i',
				'j',
				'k',
				'l',
				'm',
				'n',
				'o',
				'p',
				'q',
				'r',
				's',
				't',
				'u',
				'v',
				'w',
				'x',
				'y',
				'z'
			);
		$group_array = array();
		foreach ($alphabets as $key => $value) {
			$group = array();
			foreach($personnel as $employee) {
				if(substr(strtolower($employee->personnel_name),0,1)==$value){
					$group[] = $employee;
				}
			}
			if(count($group)>0) {
				$group_array[] = array(
						'group'=>$value,
						'employees'=>$group
					);
			}				
		}		
		return $group_array;
		
	}
	
	
	/**
	 * Function to export personnel details to excel
	 *
	 * @return void
	 * @author Brian Mwadime
	 */	
	public function action_personnel_export_excel_address_book() {
		$clients = ORM::factory('Personnel')->find_all();
		$spreadsheet = Spreadsheet::factory(array(
				'author'  => 'current user',
				'title'      => 'Personnel Address Book',
				'subject' => 'Personnel Address Book',
				'description'  => 'List of Personnel',
				'path' =>  DOCROOT.'reports/',
				'name' => 'efpro_personnel_addressbook'
			));
		$spreadsheet->set_active_worksheet(0);
		try {
			$client_items_array['columns'] = array(
					'Personnel Name',
					'Telephone',
					'Email Address', 
					'Personnel status'
				);
			$client_items_array['rows'] = array();			
			foreach($clients as $item) {
						$personnel_active = ($item->personnel_active == 0) ? "Active" : "Inactive" ;
						$client_items_array['rows'][] = array(
						$item->personnel_name,
						$item->personnel_telephone,
						$item->personnel_email_address,
						$personnel_active
					);
			}
			$spreadsheet->set_data($client_items_array, false);
			$spreadsheet->send();
			//$spreadsheet->save();
			// exit;
		}Catch (Exception $e) {
			throw new Exception('Could not export Excel report. Try again later.' . $e);
		}
	}
	
	
	/**
	 * Function to display personnel address bookl
	 *
	 * @return void
	 * @author Joseph Bosire
	 */	
	public function action_personnel_address_book() {
		$personnel = ORM::factory('Personnel')->find_all();
		$this->_template->set('personnel',$personnel);
		$this->_set_content('reports/personnel_address_book');
	}
	
	
	/**
	 * Function to fetch personnel user profile
	 *
	 * @return void
	 * @author Joseph Bosire
	 */	
	public function action_user_profile(){
		$personnel = ORM::factory('Personnel')->where('user_id','=',$this->request->query('userId'))->find();
		$user_info = $personnel->user_info->as_array();
		$this->_set_msg('User Info fetched successfully','success',$user_info);
	}
	
	
	/**
	 * Function to edit personnel profile details
	 *
	 * @return void
	 * @author Joseph Bosire
	 */	
	public function action_edit_profile() {
		$id='';
		if($this->request->query()) {
			$id=$this->request->param('id');
		}else if($this->request->post()) {
			$id = $this->request->post('id');
		}
		$employee = ORM::factory('Personnel', $id);		
		// This line was commented out because of many js/view errors on calling this url.. see lines further below
		// $this->_template->set('ajax_save_url', $this->_template->current_url);
		// view flag to check whether save was unsuccessfull after a post
		$this->_template->set('save_failed', 1);
		if ($this->request->post()) {
			// bind values to table columns
			$post = $this->request->post();			
			// STEP1: Register User Information
			$user = ORM::factory('User',$employee->user_id);
			$user->username = $post['username'];
			$user->email = $post['personnel_email_address'];
			if(!$employee->user_id) {
				$user->password = $post['password'];
			}
			$user->save();					
			if($this->request->post('user_type')){
				$user->remove('roles');	
				$user->add('roles', ORM::factory('Role',$this->request->post('user_type')));
			}						
			$user->remove('roles',ORM::factory('Role')->where('name', '=', 'login')->find());	
			$user->add('roles', ORM::factory('Role')->where('name', '=', 'login')->find());
			// STEP2: REgister Personnel Information
	        $employee->values($this->request->post());
			try {
				// add user_id to personnel record
				$employee->user_id = $user->id;
				// Upload avatar
				if(isset($_FILES['avatar'])) {
					$directory =  DOCROOT.'assets/avatars/';
					// if(isset($employee->personnel_avatar)){
						// Delete the previous avatar file
           				// unlink($directory.$employee->personnel_avatar);
					// }
					$file = $_FILES['avatar'];
					$avatar =  $this->save_image($file,48,48,$directory);
					$employee->personnel_avatar = $avatar;
				} else {
					if (!$employee->personnel_avatar) {
						$employee->personnel_avatar= "default.png";
					}						
				}
				// save personnel record
				$employee->save();			
	            // Update personnel
	            $this->_set_msg($employee->personnel_name.' record was saved!', 'success', true);
				// this flag is used to know whether or not a new record was successfully added
				if (!intval($id)){
	            	$this->_template->set('added_record', 1);
				}
				$this->_template->set('save_failed', 0);
				$id = $employee->personnel_id;
					
			} catch (Exception $e) {
				$errors = array();
				if ($e instanceof ORM_Validation_Exception){
					$errors = $e->errors('models');
				}
				$this->_set_msg('Please correct the errors below.', 'error', $errors);
			}
		}
		// More of corective url adjustments in case JS screws this up
		if (!intval($id)) {
			$this->_template->set('addform', 1);
			$this->_template->set('ajax_save_url', $this->_template->controller_base_url . 'edit_profile');
		} else {
			$this->_template->set('ajax_save_url', $this->_template->controller_base_url . 'edit_profile/' . $id);
		}	
		$this->_template->set('profile_mode', 0);	
		$this->_template->set('content_data', $employee);
		$roles = ORM::factory('Role')->where('name','!=','login')->find_all();
		$this->_template->set('roles', $roles);
		$this->_set_content('personnel_edit');
	}
}
	