<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Controller that handles all Supplier related requests
 *
 * PHP version 5.3
 *
 * @category  Controllers
 * @package   Efficiency_Pro
 * @author    Hezron Obuchele <hezron4k@gmail.com>
 * @author    Joseph Bosire <kashboss@gmail.com>
 * @author    Brian Mwadime <brianmwadime@gmail.com>
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.0.5
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
class Controller_Equipment extends Controller_Site
{
	
	/**
	 * User roles array
	 * @array
	 */
	protected $role_required = array('login');

	/**
	 * User Permissions array
	 * @array
	 */
	protected $permission_actions = array(
		'EQUIPMENT_VIEW' => array(
			'index',
			'comments',
			'schedules',
								),
		'EQUIPMENT_EDIT' => array('edit'),
		'EQUIPMENT_DELETE' => array(
			'delete',
			'delete_avatar',
									),
										);
	
	/**
	 * Function to display index page
	 * 
	 * @return equipment list content
	*/
	public function action_index() {
		$this->_template->set('page_title', 'Equipment');
		$this->_template->set('page_info', 'view and manage Equipment');
		$equipment = ORM::factory('Equipment')->where('delete_status', '=', '0');
		// perform a model function that filters a datset
		$search_field = array('equipment_name', 'serial_number');
		$search_value = $this->_search_context;
		$list_columns = $equipment->get_equipment_list($search_field, $search_value);
		// Set up pagination params
		// Set maximum datatables limit here
		// Assumption is this sytem will never have a large dataset (millions) 
		// but if it becomes larger than this vaue switch
		// to ajax based datatables 
		//(see http://www.packtpub.com/article/data-tables-datatables-plugin-jquery-1.3-with-php)
		$this->_request_params['limit'] = 300;
		// Setup pagination
		$pagination = $this->_setup_pagination($equipment, $list_columns);
		// Send back the list
		$content_array = $this->group_equipment($equipment->find_all());
		$this->_template->set('content_data', $content_array);
		
		$this->_set_search_context(I18n::get("nav.site.search.equipment"));
		$this->_set_content('equipment');
	}


	/**
	 * Function to print excel file
	 * 
	 * @return equipment list content
	 */
	public function action_printexcel() {
	$spreadsheet = Spreadsheet::factory(
								array(
									'author'  => 'Kohana-PHPExcel',
									'title'      => 'Report',
									'subject' => 'Subject',
									'description'  => 'Description',
									'path' =>  DOCROOT.'reports/',
									'name' => 'reports',
									));
		
		$spreadsheet->set_active_worksheet(0);
		
		try {
				
			$as = $spreadsheet->get_active_worksheet();
			
			$sh['columns'] = array('Day', 'User', 'Count', 'Extra');
			$sh['rows'] = array(
								0 => array(1, 'John', 5, 587),
								1 => array(2, 'Den', 3, 981),
								2 => array(3, 'Anny', 1, 214)
								);
		
			$spreadsheet->set_data($sh, false);
			$spreadsheet->send();
			exit;
			
		} catch (Exception $e){
			exit;
		}
		
	}
	

	/**
	 * Function to perform an edit or add a supply item
	 * 
	 * @return returns the edited equipment item
	 */
	public function action_edit() {
		$id = $this->request->param('id');
		$equipment = ORM::factory('Equipment', $id);
		$path_to_images = url::base().'assets/avatars/';
		$this->_template->set('path', $path_to_images);
		// This line was commented out because of many js/view errors on calling this url.
		// See lines further below
		// $this->_template->set('ajax_save_url', $this->_template->current_url);
		// view flag to check whether save was unsuccessfull after a post
		$this->_template->set('save_failed', 1);
		// $directory =  DOCROOT.'assets/equipment/';
		if ($this->request->post()) {
			// bind values to table columns
			$equipment->values($this->request->post());
			try {
				if (isset($_FILES['avatar'])) {
					$directory =  DOCROOT.'assets/avatars/';
					$avatar =  $this->save_image($_FILES['avatar'], 48, 48, $directory);
					$equipment->equipment_avatar = $avatar;
				} else {
					if (!$equipment->equipment_avatar) {
						$equipment->equipment_avatar = "default.png";
					}
					
				}
				
				$equipment->save();
				// Upload item images
				
				// Update equipment
				$this->_set_msg('Successfully saved!', 'success', true);
				// this flag is used to know whether or not a new record was successfully added
				if (!intval($id)) {
					$this->_template->set('added_record', 1);
				}
				
				$this->_template->set('save_failed', 0);
				$id = $equipment->equipment_id;
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
	
		$this->_template->set('content_data', $equipment);
		$this->_set_content('equipment_edit');
	}

	
	/**
	 * Function to perform a delete on supply item
	 * 
	 * @return message for deletion status
	 */
	public function action_delete() {
		$id = $this->request->param('id');
		if ($id) {
			$equipment = ORM::factory('Equipment', $id);
			if ($equipment->loaded()) {
				try {
					$equipment->delete_status = 1;
					$equipment->save();
				} catch (Exception $e) {
					$this->_set_msg('Could not delete equipment item because this would wipe their history!', 'error');
				}
			}
			
			$this->redirect($this->request->referrer());
		}
		
		// $this->_set_content('supply_delete');
	}

	
	public function action_delete_avatar() {
		$id = $this->request->param('id');
		if ($id) {
			$equipment 	= ORM::factory('Equipment', $id);
			$data 		= array('id' => 'avatar-' . $equipment->equipment_id);			
			if ($equipment->loaded()) {
				try {
					$file =  DOCROOT.'assets/avatars/' . $equipment->equipment_avatar;
					unlink($file);
					
					$equipment->equipment_avatar = "default.png";
					
					$equipment->save();
					
					$this->_set_msg(' Avatar was deleted', 'success', $data);
					
				} catch (Exception $e) {
					$this->_set_msg('Could not delete avatar', 'error', $data);
				}
			}
						
		}
	}
	

	/**
	 * Function to display item comments tab in edit form
	 * 
	 * @return comments
	 */
	public function action_comments() {
		$id = $this->request->param('id');
		// Fetch comments for supply item
		if ($this->request->query()) {
			if ($id) {
				$equipment = ORM::factory('Equipment', $id)->comments->find_all();
				$this->_template->set('content_data', $equipment);
			}
			
			$this->_set_content('equipment_item_comments');
		}
		
		// Add comment to supply item
		if ($this->request->post()) {
			$comment = ORM::factory('EquipmentNote');
			$comment->values($this->request->post());
			$comment->equipment_id = $id;
			$comment->equipment_note_timestamp = date("Y-m-d H:i:s");
			try {
				$comment->save();
				$this->_set_msg('Added comment successfully', 'success', $comment->as_array());
			} catch (Exception $e) {
				$this->_set_msg('Could not add an empty commnet', 'error', true);
			}
		}
		
	}
	
	
	/**
	 * Function to display item comments tab in edit form
	 * 
	 * @return schedules
	 */
	public function action_schedules() {
		$id = $this->request->param('id');
		// Fetch comments for supply item
		if ($this->request->query()) {
			if ($id) {
				$equipment_schedules = ORM::factory('Equipment', $id)->tasks->find_all();
				$this->_template->set('content_data', $equipment_schedules);
			}
			
			$this->_set_content('equipment_item_schedules');
		}
	}
	
	
	/**
	 * Function to group arrays according to the alphabet
	 *
	 * @return array of grouped equipments 
	 */
	public function group_equipment($equipments) {
		$alphabets = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p'
		, 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
		$group_array = array();
		foreach ($alphabets as $key => $value) {
			$group = array();
			foreach ($equipments as $equipment) {
				if (substr(strtolower($equipment->equipment_name), 0, 1) == $value ) {
					$group[] = $equipment;
				}
				
			}
			
			if (count($group) > 0)
				$group_array[] = array(
					'group'=>$value,
					'equipment'=>$group
										);
		}
				
		return $group_array;
	}
}
