<?php defined('SYSPATH') or die('No direct script access.'); 
/**
 * Controller that handles all Packages related request
 *
 * PHP version 5.3
 *
 * @category  Controllers
 * @package   Efficiency_Pro
 * @author    Ian Madege<imadege1990@gmail.com>
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.0.5
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */	  
class Controller_PackageTypes extends Controller_Site
{
	protected $auth_required = array('login');
	
	protected $permission_actions = array(
		'INVENTORY_VIEW' => array('index', 'items'),
		'INVENTORY_EDIT' => array('edit'),
		'INVENTORY_DELETE' => array('delete'),
	);
	
	
	/**
	 * Function to display index page
	 */
	public function action_index() {

		$this->_template->set('page_title', 'Packaging Types');
		$this->_template->set('page_info', 'view and manage packaging types');
		$package_types = ORM::factory('SupplyPackageType')->where('delete_status','=','0');
		// perform a model function that filters a datset
		$search_field = array('package_type_name');
		$search_value = $this->_search_context;
		$list_columns = $package_types->get_package_types_list($search_field,$search_value);
		// to ajax based datatables (see http://www.packtpub.com/article/data-tables-datatables-plugin-jquery-1.3-with-php)
		$this->_request_params['limit'] = 300;
		// Setup pagination
		$pagination = $this->_setup_pagination($package_types, $list_columns);
		// Commented as we're no longer using our pagination control but instead using the one for dataTables
        // $this->_template->set('pagination_data', $pagination);
		// Send back the list
		$content_array = $package_types->find_all();
		$this->_template->set('content_data', $content_array);
		$this->_set_search_context(I18n::get("nav.site.search.packagetypes"));
		$this->_set_content('supply_package_types');
	}


	/**
	 * Function to perform an edit or add a package type
	 */
	public function action_edit() {
		$id = $this->request->param('id');
		$package_type = ORM::factory('SupplyPackageType', $id);
		// This line was commented out because of many js/view errors on calling this url.. see lines further below
		// $this->_template->set('ajax_save_url', $this->_template->current_url);
		// view flag to check whether save was unsuccessfull after a post
		$this->_template->set('save_failed', 1);
		if ($this->request->post()) {
			// bind values to table columns
	        //$fields_to_update = array('fk_user_id', 'fk_conversation_id', 'time', 'reply','ip');	        
	        $package_type->values($this->request->post());			
			try{
				$package_type->save();
	            $this->_set_msg('Successfully saved!', 'success', true);
				if (!intval($id)) {
	            	$this->_template->set('added_record', 1);
				
				}
				$this->_template->set('save_failed', 0);
				$id = $package_type->package_type_id;

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
		$this->_template->set('content_data', $package_type);	
		$this->_set_content('supply_package_type_edit');
	}


	/**
	 * Function to perform a delete on a package type
	 */
	public function action_delete() {
		$id = $this->request->param('id');
		if($id) {
			$package_type = ORM::factory('SupplyPackageType', $id);
			if ($package_type->loaded()) {
				try {
					$package_type->delete_status=1;
					$package_type->save();
				
				} catch (Exception $e) {
					$this->_set_msg('Could not delete package type because this would wipe their history!', 'error');
				
				}
			
			}
			$this->redirect($this->request->referrer());
		
		}
		$this->_set_content('package_delete');

	}

}