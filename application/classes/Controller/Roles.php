<?php defined('SYSPATH') or die('No direct script access.'); 
/**
 * Controller representing role and permissions actions
 *
 * @version 03 - Hezron Obuchele 2013-07-20
 *
 * PHP version 5
 */	  
class Controller_Roles extends Controller_Site
{
	protected $role_required = array('login');
	
	protected $permission_actions = array(
		'USERS_VIEW' => array('index'),
		'USERS_EDIT' => array('edit'),
		'USERS_DELETE' => array('delete')
	);
	
	/**
	 * Function to display index page
	 */
	public function action_index() {
		$this->_template->set('page_title', 'User Types');
		$this->_template->set('page_info', 'view and manage user types');
		$roles = ORM::factory('Role')->where('delete_status','=','0');
		//Roles list
		$content_array = $roles->find_all();		
		$this->_template->set('content_data', $content_array);
		$this->_set_search_context(I18n::get("nav.site.search.roles"));
		$this->_set_content('roles');
	}

	public function action_edit() {
		$id='';
		if($this->request->query()){
			$id=$this->request->param('id');
		}else if($this->request->post()){
			$id = $this->request->post('id');
		}
		$role = ORM::factory('Role', $id);
		$this->_template->set('save_failed', 1);
		if ($this->request->post()) {
			// bind values to table columns
			$post = $this->request->post();			
			//STEP1: Save Role Information
	        $role->values($this->request->post());
			try {
				$role->save();
				$role->remove('permissions');
				foreach ($post['permission_ids'] as $key => $value) {
					$role->add('permissions', ORM::factory('Permission')->where('id', '=', $value)->find());
					
				}			
	            // Update role
	            $this->_set_msg(' record was saved!', 'success', true);
				// this flag is used to know whether or not a new record was successfully added
				if (!intval($id)){
	            	$this->_template->set('added_record', 1);
				}
				$this->_template->set('save_failed', 0);
				$id = $role->id;					
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
			$this->_template->set('ajax_save_url', $this->_template->controller_base_url . 'edit');
		} else {
			$this->_template->set('ajax_save_url', $this->_template->controller_base_url . 'edit/' . $id);
		}
//var_dump($role->get_role_permissions());exit;
		$resources = ORM::factory('Resource')->get_resources();
		$this->_template->set('content_data', $role);
		$this->_template->set('resources', $resources);
		$this->_set_content('role_edit');
	}
	

	
	public function action_delete() {
		$id = $this->request->param('id');
		if($id) {
			$role = ORM::factory('Role', $id);
			if ($role->loaded()){
				try {
					$role->delete_status=1;
					$role->save();
				} catch (Exception $e) {
					$this->_set_msg('Could not delete role because this would wipe their history!', 'error');
				}
			}
			$this->redirect($this->request->referrer());
		}
		$this->_set_content('role_delete');
	}
	
 
  
}

	
	