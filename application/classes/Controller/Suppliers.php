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
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.0.5
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
class Controller_Suppliers extends Controller_Site
{

	/**
	 * Controller role requirements
	 * @var array
	 */
	protected $role_required = array('login');

	/**
	 * Controller permission<->action definitions
	 * @var array
	 */
	protected $permission_actions = array(
		'INVENTORY_VIEW'   => array(
			'index',
			'items'
			),
		'INVENTORY_EDIT'   => array('edit'),
		'INVENTORY_DELETE' => array('delete'),
		);


	/**
	 * Function to display index page
	 *
	 * @return void
	 */
	public function action_index() {
		$this->_template->set('page_title', 'Suppliers');
		$this->_template->set('page_info', 'view and manage suppliers');
		$suppliers = ORM::factory('Supplier')->where('delete_status', '=', '0');
		// perform a model function that filters a dataset
		$search_field = array(
			'supplier_name',
			'supplier_contact_person',
			'supplier_order_email',
			'supplier_sales_email',
			'supplier_cellphone',
			'supplier_business_phone',
			);
		// use this to get ALL search params in addition to the global
		// filter e.g. date range Note that this will be set only if
		// other search params were set in addition to the global search
		$search_params = Arr::get($this->_request_params, 'search_params');
		// get only global filter value
		$search_value = $this->_request_params['search'];
		$list_columns = $suppliers->filter_suppliers_list($search_field, $search_value);

		// Set up pagination params
		// Set maximum datatables limit here
		// Assumption is this sytem will never have a large dataset (millions)
		// but if it becomes larger than this value switch to ajax based datatables
		// (see http://www.packtpub.com/article/data-tables-datatables-plugin-jquery-1.3-with-php)
		//$this->_request_params['limit'] = 300;

		// Setup pagination
		$this->_setup_pagination($suppliers, $list_columns, false);

		// Send back the list manually if you set 3rd param of _setup_pagination() to false
		$content_array = $suppliers->find_all();
		$this->_template->set('content_data', $content_array);

		$this->_set_search_context(I18n::get("nav.site.search.suppliers"));
		$this->_set_content('suppliers');
	}


	/**
	 * Function to display edit page
	 *
	 * @return void
	 */
	public function action_edit() {
		$id       = $this->request->param('id');
		$supplier = ORM::factory('Supplier', $id);

		// view flag to check whether save was unsuccessfull after a post
		$this->_template->set('save_failed', 1);
		if ($this->request->post()) {
			// bind values to table columns
			$post = $this->request->post();
			$user = ORM::factory('User', $supplier->supplier_user_id);
			if (!$user->loaded()) {
				$user->password = $post['password'];
			}

			// register user details
			$user->username = $post['username'];
			$user->email    = $post['supplier_sales_email'];
			if (!$supplier->loaded()) {
				$user->password = $post['password'];
			}

			try{
				$user->save();
				$user->remove('roles');
				$user->add('roles', ORM::factory('Role', $this->request->post('user_type')));
				$user->add('roles', ORM::factory('Role')->where('name', '=', 'login')->find());
				// get the post of the other users
				$supplier->values($this->request->post());
				// step two registration
				try {
					$supplier->supplier_user_id = $user->id;
					$supplier->save();
					// Update supplier
					$this->_set_msg('Successfully saved!', 'success', true);
					// this flag is used to know whether or not a new record was successfully added
					if (!intval($id)) {
						$this->_template->set('added_record', 1);
					}

					$this->_template->set('save_failed', 0);
					$id = $supplier->supplier_id;
				} catch (Exception $e) {
					$errors = array();
					if ($e instanceof ORM_Validation_Exception) {
						$errors = $e->errors('models');
					}

					$this->_set_msg('Please correct the errors below.', 'error', $errors);
				}

			} catch (Exception $e) {
				$errors = array();
				if ($e instanceof ORM_Validation_Exception) {
					$errors = $e->errors('models');
				}

				$this->_set_msg('Please correct the errors below.', 'error', $errors);
			}
			// add roles to the supplier
		}

		// More of corective url adjustments in case JS screws this up
		if (!intval($id)) {
			$this->_template->set('addform', 1);
			$this->_template->set('ajax_save_url', $this->_template->controller_base_url . 'edit');
		} else {
			$this->_template->set('ajax_save_url', $this->_template->controller_base_url . 'edit/' . $id);
		}

		$roles = ORM::factory('Role')->where('name', '=', 'suppliers')->find_all();
		$this->_template->set('roles', $roles);
		$this->_template->set('content_data', $supplier);
		$this->_set_content('supplier_edit');
	}


	/**
	 * Function to display supplier items list in tab
	 *
	 * @return void
	 */
	public function action_items() {
		$id = $this->request->param('id');
		if ($id) {
			$supplies = ORM::factory('Supplier', $id)->where('delete_status', '=', '0')->supplies->find_all();
			$this->_template->set('content_data', $supplies);
		}

		$this->_set_content('supplier_items');
	}


	/**
	 * Function to delete supplier
	 *
	 * @return void
	 */
	public function action_delete() {
		$id = $this->request->param('id');
		if ($id) {
			$supplier = ORM::factory('Supplier', $id);
			if ($supplier->loaded()) {
				try {
					$supplier->delete_status = 1;
					$supplier->save();

				} catch (Exception $e) {
					$this->_set_msg('Could not delete supplier because this would wipe their history!', 'error');
				}
			}

			$this->redirect($this->request->referrer());
		}

		$this->_set_content('supplier_delete');
	}


}
