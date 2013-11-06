<?php defined('SYSPATH') or die('No direct script access.'); 
	  
class Controller_SupplyShelves extends Controller_Site
{
	protected $auth_required = array('login');
	
	protected $permission_actions = array(
		'INVENTORY_VIEW' => array('index'),
		'INVENTORY_EDIT' => array('edit'),
		'INVENTORY_DELETE' => array('delete'),
	);
	 
	/**
	 * Function to display index page
	 *
	 * @return void
	 */
	public function action_index() {
	   
		$this->_template->set('page_title', 'Supply Shelves');
		$this->_template->set('page_info', 'view and manage supply shelves');
		$supply_shelf = ORM::factory('SupplyShelf');
		$this->_request_params['limit'] = 300;
		$search_field = array(
				'supply_shelf_name'
				);
		$search_value = $this->_search_context;
		$list_columns = $supply_shelf->get_shelves_list($search_field, $search_value);
		// Set up pagination params
		$pagination = $this->_setup_pagination($supply_shelf, $list_columns);
		//locations
		$location = ORM::factory('SupplyLocation')->find_all();
		//$this->template->pagination = $paging->render();
		//$this->_template->set('pagination_data', $pagination);
		$this->_template->set('locationdata', $location);
		// Send back the list
		//$content_array = ORM::factory('SupplyShelf')->with('location')->select('supply_location_name')->find_all();
		$content_array = Model_SupplyShelf::get_shelf_list_summary('', 100, 0);
		$this->_template->set('content_data', $content_array);
		//$this->_template->set('ajax_save_url', $this->_template->current_url . 'edit');
		$this->_template->set('addform', 1);
		$this->_set_search_context(I18n::get("nav.site.search.shelves"));
		$this->_set_content('supply_shelves');
	}
	
	/**
	 * Function to display edit page 
	 *
	 * @return void
	 */
	public function action_edit() {
		$id = $this->request->param('id');
		$supply_shelf = ORM::factory('SupplyShelf', $id);
		// This line was commented out because of many js/view errors on calling this url.. see lines further below
		// $this->_template->set('ajax_save_url', $this->_template->current_url);
		// view flag to check whether save was unsuccessfull after a post
		$this->_template->set('save_failed', 1);
		if ($this->request->post()) {
			// bind values to table columns
			$supply_shelf->values($this->request->post());
			try {
				$supply_shelf->save();
				// Update supplier
				$this->_set_msg('Successfully saved!', 'success', true);
				// this flag is used to know whether or not a new record was successfully added
				if (!intval($id)) {
				$this->_template->set('added_record', 1);
				}
				$this->_template->set('save_failed', 0);
				$id = $supply_shelf->supply_shelf_id;	
			} catch (Exception $e) {
				$errors = array();
				if ($e instanceof ORM_Validation_Exception) {
					$errors = $e->errors('models');
				}
				$this->_set_msg('Please correct the errors below.', 'error', $errors);
			}
		}
		$locations = ORM::factory('SupplyLocation')->find_all();
		$this->_template->set('locations', $locations);
		
		$supply_shelf_types = ORM::factory('SupplyShelfType')->where('supply_shelf_type_id', '!=', '0')->find_all();
		$this->_template->set('shelf_types', $supply_shelf_types);
		
		// More of corective url adjustments in case JS screws this up
		if (!intval($id)) {
			$this->_template->set('addform', 1);
			$this->_template->set('ajax_save_url', $this->_template->controller_base_url . 'edit');
		} else {
			$this->_template->set('ajax_save_url', $this->_template->controller_base_url . 'edit/' . $id);
		}
		$this->_template->set('content_data', $supply_shelf);	
		$this->_set_content('supply_shelve_edit');
	}
	
	/**
	 * Function to get items in a specific shelves 
	 *
	 * @return void
	 */
	public function action_supply_items() {
		$id = $this->request->param('id');
		$supply_shelf = ORM::factory('SupplyShelf', $id);
		$this->_template->set('supplies', $supply_shelf->get_supply_items());
		$this->_set_content('supply_shelve_items');
	}
	
	
		/**
	 * Function to perform a delete on a package type
	 */
	public function action_delete() {
		$id = $this->request->param('id');
		if($id) {
			$supply_shelf= ORM::factory('SupplyShelf', $id)->where('delete_status','=','0');
			if ($supply_shelf->loaded()) {
				try{
					$supply_shelf->delete_status=1;
					$supply_shelf->save();
				} catch (Exception $e) {
					$this->_set_msg('Could not delete location because this would wipe their history!', 'error');
				}
			}
			$this->redirect($this->request->referrer());
		}
		$this->_set_content('location_delete');
	}

	/**
	 * Function to delete  several shelves 
	 *
	 * @return void
	 */
	public function action_multidelete() {
		if ($this->request->post()) {
			$counter=0;
		  $shelf_ids=array();
		  $shelf_ids=$this->request->post('id');
		  //loop through deleting 
		  foreach ($shelf_ids as $id) {
			$supply_shelf=ORM::factory('SupplyShelf', $id);
			try {
				$supply_shelf->delete();
				$counter++;
				$this->_set_msg('Successfull deleted!', 'success');
			} catch(Exception $e) {
				$this->_set_msg('Could not delete location because this would wipe their history!', 'error');
			}
		  }
		}
		$this->redirect($this->request->referrer());
	}

	public function action_ikanban(){
		$id = $this->request->param('id'); 
		$supply_shelf = ORM::factory('SupplyShelf', $id);
		$shelf_bacode = $supply_shelf->supply_shelf_id; 
		$barcode_url = Url::base(true).'vendor/barcode39/gen_barcode.php';
		if ($this->request->query('barcode_count')) {
			$barcode_count = $this->request->query('barcode_count');
		} else {
			$barcode_count = 1;
		}
		
		$barcode_path = DOCROOT.'assets/barcodes/shelves/'.$shelf_bacode.'.gif';
		if (!file_exists($barcode_path)) {
		
				file_put_contents($barcode_path, file_get_contents($barcode_url . '?code='.$shelf_bacode));
			
		}
		$this->_template->set('barcode', URL::base(true).'assets/barcodes/shelves/'.$shelf_bacode.'.gif');
		$this->_template->set('export_status', false);
		$this->_template->set('barcode_url', $barcode_url);
		$this->_template->set('barcode_name', $shelf_bacode);
		$this->_template->set('barcode_count', $barcode_count);
		$this->_template->set('shelf', $supply_shelf);
		$this->_template->set('supply_items', $supply_shelf->get_supply_items());
		
		$this->_set_content('shelf_barcodes');
	}
	
	public function action_all_ikanban_cards(){
		$barcode_url = Url::base(true).'vendor/barcode39/gen_barcode.php';
		if ($this->request->query('format') == 'avery_pdf') {
			$this->_template->set('export_status', true);
		} else {
			$this->_template->set('export_status', false);
		}
		$this->_template->set('barcode_url', $barcode_url);
		$this->_template->set('shelf_supply_items', Model_SupplyShelf::get_shelf_supply_items());
		$this->_set_content('shelf_all_barcodes');
	}
	
	public function action_generate_ikanban_card() {
		$id = $this->request->param('id');
		$supply_id = $this->request->query('item_id');
		$supply = ORM::factory('Supply', $supply_id);
		$supply_shelf = ORM::factory('SupplyShelf', $id);
		$shelf_bacode = $supply_shelf->supply_shelf_id; 
		$barcode_url = Url::base(true).'vendor/barcode39/gen_barcode.php';
		$barcode_path = DOCROOT.'assets/barcodes/shelves/'.$supply->product_code.'.gif';

		if (!file_exists($barcode_path)) {
				file_put_contents($barcode_path, file_get_contents($barcode_url . '?code='.$supply->product_code));
		}
		$this->_template->set('barcode', URL::base(true).'assets/barcodes/shelves/'.$supply->product_code.'.gif');
		$this->_template->set('barcode_url', $barcode_url);
		$this->_template->set('barcode_name', $shelf_bacode);
		$this->_template->set('shelf', $supply_shelf);
		$this->_template->set('supply', $supply);
		$this->_template->set('export_status', true);
		$this->_set_content('shelf_barcodes');
	}
}
