<?php defined('SYSPATH') or die('No direct script access.'); 
	  
class Controller_SupplyLocations extends Controller_Site
{
	protected $auth_required = array('login');
	
	protected $permission_actions = array(
		'INVENTORY_VIEW' => array('index', 'items'),
		'INVENTORY_EDIT' => array('edit'),
		'INVENTORY_DELETE' => array('delete'),
	);

	/**
	 * Function to display index page
	 * @return void
	 */
	public function action_index() {
		$this->_template->set('page_title', 'Supply Locations');
		$this->_template->set('page_info', 'view and manage supply locations');
		$supply_locations = ORM::factory('SupplyLocation')->where('delete_status','=','0');
		// perform a model function that filters a datset
		$search_field = array(
				'supply_location_name'
				);
		$search_value = $this->_search_context;
		$list_columns = $supply_locations->get_supply_location_list($search_field, $search_value);
		
		// Set up pagination params
		// Set maximum datatables limit here
		// Assumption is this sytem will never have a large dataset (millions) but if it becomes larger than this vaue switch
		// to ajax based datatables (see http://www.packtpub.com/article/data-tables-datatables-plugin-jquery-1.3-with-php)
		$this->_request_params['limit'] = 300;

		// Setup pagination
		$pagination = $this->_setup_pagination($supply_locations, $list_columns);
		// Commented as we're no longer using our pagination control but instead using the one for dataTables
        // $this->_template->set('pagination_data', $pagination);
		
		// Send back the list
		$content_array = $supply_locations->find_all();
		$this->_template->set('content_data', $content_array);
		
		$this->_set_search_context(I18n::get("nav.site.search.locations"));
		$this->_set_content('supply_locations');
	}
	
	/**
	 * Function to  display edit page and also edit locations
	 *
	 * @return void
	 */
	public function action_edit() {
		$id = $this->request->param('id');
		$supply_location = ORM::factory('SupplyLocation', $id);
		// This line was commented out because of many js/view errors on calling this url.. see lines further below
		// $this->_template->set('ajax_save_url', $this->_template->current_url);
		// view flag to check whether save was unsuccessfull after a post
		$this->_template->set('save_failed', 1);
		if ($this->request->post()){
			// bind values to table columns
	        $supply_location->values($this->request->post());			
			try{
				$supply_location->save();
	            // Update supplier
	            $this->_set_msg('Successfully saved!', 'success', true);
				// this flag is used to know whether or not a new record was successfully added
				if (!intval($id)){
	            	$this->_template->set('added_record', 1);
				}
				$this->_template->set('save_failed', 0);
				$id = $supply_location->supply_location_id;	
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
		$this->_template->set('content_data', $supply_location);	
		$this->_set_content('supply_location_edit');
	}
	
	/**
	 * Function to delete  locations
	 * @return void
	 */
	public function action_delete(){
		$id = $this->request->param('id');
		if($id){
			$supply_location = ORM::factory('SupplyLocation', $id);
			if ($supply_location->loaded()){
				try{
					$supply_location->delete_status=1;
					$supply_location->save();
				} catch (Exception $e) {
					$this->_set_msg('Could not delete location because this would wipe their history!', 'error');
				}
			}
			$this->redirect($this->request->referrer());
		}
		$this->_set_content('location_delete');
	}

	/**
	 * Function to get items in a cetain location
	 *
	 * @return void
	 */
	public function action_items(){
		if($this->request->param('id')){
			$supply_location = ORM::factory('SupplyLocation');
			$this->_template->set('content_data', $supply_location->find_all());
			$this->_template->set('list_items', 1);
			$this->_set_content('$supply_locations');
		}else{
			$this->redirect('friends');
		}
	}

}