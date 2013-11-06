<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Controller that handles all Jobs related requests
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
class Controller_Jobs extends Controller_Site
{

	protected $role_required = array('login');

	protected $permission_actions = array(
		'INVENTORY_VIEW'   => array(
			'index',
			'items'
			),
		'INVENTORY_EDIT'   => array('edit'),
		'INVENTORY_DELETE' => array('delete'),
		);
		
		
	/**
	 * Function to display add Tasks page
	 *
	 * @return void
	 */
	public function action_view() {
		$this->_template->set('page_title', 'Add Tasks');
		$this->_template->set('page_info', 'view and manage Tasks');
	
		$this->_set_content('job_tasks');
	}
	

	/**
	 * Function to display Schedule Page page
	 *
	 * @return void
	 */
	public function action_schedule() {
		$this->_template->set('page_title', 'Schedule Job');
		$this->_template->set('page_info', 'view and manage schedules');
	
		$this->_set_content('order_schedule');
	}
	
	
	/**
	 * Function to display Schedule Page page
	 *
	 * @return void
	 */
	public function action_assign() {
		$this->_template->set('page_title', 'Schedule Tasks');
		$this->_template->set('page_info', 'view and schedule Tasks');
	
		$this->_set_content('task_schedule_assign');
	}
	
	
	/**
	 * Function to display Schedule Page page
	 *
	 * @return void
	 */
	public function action_assign_person() {
		$this->_template->set('page_title', 'Assign Personnel');
		$this->_template->set('page_info', 'view and Assign Personnel');
	
		$this->_set_content('task_personel_assign');
	}
	
	
	/**
	 * Function to display Schedule Page page
	 *
	 * @return void
	 */
	public function action_assign_equip() {
		$this->_template->set('page_title', 'Assign Equipment');
		$this->_template->set('page_info', 'view and Assign Equipment');
	
		$this->_set_content('task_equipment_assign');
	}
	
	
	/**
	 * Function to display Schedule Page page
	 *
	 * @return void
	 */
	public function action_scheduler() {
		$this->_template->set('page_title', 'Schedule Job Tasks');
		$this->_template->set('page_info', 'view and manage schedules');
	
		$this->_set_content('job_schedule_edit');
	}
	
	
	/**
	 * Function to display add Tasks page
	 *
	 * @return void
	 */
	public function action_tasks() {
		$this->_template->set('page_title', 'Add Tasks');
		$this->_template->set('page_info', 'view and manage Tasks');
	
		$this->_set_content('task_edit');
	}
	
	
	/**
	 * Function to display add Tasks page
	 *
	 * @return void
	 */
	public function action_client() {
		$this->_template->set('page_title', 'Add Client');
		//$this->_template->set('page_info', 'view and manage Tasks');
	
		$this->_set_content('job_client_edit');
	}
	
	
	/**
	 * Function to display add Jobs page
	 *
	 * @return void
	 */
	public function action_jobs() {
		$this->_template->set('page_title', 'Add Jobs');
		$this->_template->set('page_info', 'view and manage Client Jobs');
	
		$this->_set_content('job_edit');
	}
	
	
	/**
	 * Function to display add Orders page
	 *
	 * @return void
	 */
	public function action_orders() {
		$this->_template->set('page_title', 'Add Order');
		$this->_template->set('page_info', 'view and manage Client Order');
	
		$this->_set_content('order_edit');
	}
	
	
	/**
	 * Function to display add Orders page
	 *
	 * @return void
	 */
	public function action_orders_drop() {
		$this->_template->set('page_title', 'Add Order');
		$this->_template->set('page_info', 'view and manage Client Order');
	
		$this->_set_content('order_edit2');
	}
	
	
	/**
	 * Function to display internal jobs page
	 *
	 * @return void
	 */
	public function action_internal() {
		$this->_template->set('page_title', 'Internal Jobs');
		$this->_template->set('page_info', 'view and manage Internal Jobs');
	
		$this->_set_content('internal_jobs');
	}
	
	
	/**
	 * Function to display internal jobs page
	 *
	 * @return void
	 */
	public function action_repeat() {
		$this->_template->set('page_title', 'Job Repeatition');
		$this->_template->set('page_info', 'Job Repeatition');
	
		$this->_set_content('job_repeat');
	}
	
	
	/**
	 * Function to display internal jobs page
	 *
	 * @return void
	 */
	public function action_profile() {
		$this->_template->set('page_title', 'Job Profiles');
		$this->_template->set('page_info', 'Job Profiles');
	
		$this->_set_content('job_profile_edit');
	}
	
	
	/**
	 * Function to display internal jobs page
	 *
	 * @return void
	 */
	public function action_profiles() {
		$this->_template->set('page_title', 'Job Profiles');
		$this->_template->set('page_info', 'Job Profiles');
	
		$this->_set_content('job_profiles');
	}
	
	
	/**
	 * Function to display client jobs page
	 *
	 * @return void
	 */
	public function action_clients() {
		$this->_template->set('page_title', 'Client Jobs');
		$this->_template->set('page_info', 'view and manage Jobs');
	
		$this->_set_content('client_jobs');
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

			try {
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