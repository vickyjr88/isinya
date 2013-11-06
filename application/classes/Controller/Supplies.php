<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Controller that handles all supplies (inventory items) related requests
 *
 * PHP version 5.3
 *
 * @category  Controllers
 * @package   Efficiency_Pro
 * @author    Hezron Obuchele <hezron4k@gmail.com>
 * @author    Joseph Bosire <kashboss@gmail.com>
 * @author    Ian Madege <imadege1990@gmail.com>
 * @author    Victor Kipkoech <kipmasi@gmail.com>
 * @author    Brian Mwadime <kipmasi@gmail.com>
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.0.5
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
class Controller_Supplies extends Controller_Site
{
	/**
	 * Controller role requirements
	 * @var array
	 */
	protected $role_required = array('login');

	/**
	 * Required permissions to view this page
	 * @var array
	 */
	protected $permission_actions = array(
		'INVENTORY_VIEW' => array(
			'index',
			'transactions',
			'transaction',
			'inventory_export_excel_stock_levels',
			'supplier_export_excel_inventory_reorder',
			'supplier_export_excel_address_book'),
			'INVENTORY_EDIT' => array(
				'edit',
				'delete',
				'move_item',
				'shrink_item',
				'allocate_item_list',
				'allocate_item_shelf_count_list',
				'allocate_item_shelf_list',
				'allocate_item',
				'allocate',
				'allocate_recepient_list'
			)
		);

	/**
	 * Function to display the index page
	 */
	public function action_index() {
		$path_to_images = url::base() . 'avatars/';
		$this->_template->set('page_title', 'Supplies');
		$this->_template->set('path', $path_to_images);
		$this->_template->set('page_info', 'view and manage supplies');
		$supplies = ORM::factory('Supply')->where('delete_status', '=', '0');

		// Perform a model function that filters a datset
		$search_field = array('supply_name', 'supply_purchased_quantity');
		$search_value = $this->_search_context;

		if (isset($_GET['iDisplayLength']) && isset($_GET['iDisplayStart'])) {
			$supplies->limit($_GET['iDisplayLength'])
					->offset($_GET['iDisplayStart']);
		}

		if (isset($_GET['sSearch'])) {
			$content_array = $supplies
					->where('product_code', 'like', '%'.$_GET['sSearch'].'%')
					->or_where('supply_name', 'like', '%'.$_GET['sSearch'].'%')
					->find_all()->as_array();
			$content_count = $supplies
					->where('product_code', 'like', '%'.$_GET['sSearch'].'%')
					->or_where('supply_name', 'like', '%'.$_GET['sSearch'].'%')
					->count_all();
		} else {
			$content_array = $supplies->find_all()->as_array();
			$content_count = $supplies->count_all();
		}

		$supply_types = ORM::factory('SupplyType')->find_all();
		$package_types = ORM::factory('SupplyPackageType')->find_all();
		$unit_types = ORM::factory('SupplyUnitType')->find_all();

		if(isset($_SERVER['HTTP_ACCEPT']) 
				&& stripos($_SERVER['HTTP_ACCEPT'], 'json') !== false) {
			$rec_data = Model_Base::format_orm_array_for_datatable_json(
					$content_array,
					array('avatar', 'product_code','supply_name', 'reorder_level',
							'target_level', 'quantity_per_package', 'price_per_unit',
							'total_quantity', 'percentage_limit', 'supply_id'
						)
					);
			$data_tables_response['aaData'] = $rec_data;
			$data_tables_response['sEcho'] = !empty($_GET['sEcho']) ?
					$_GET['sEcho'] : 1;
			$data_tables_response['iTotalRecords'] = intval($content_count);
			$data_tables_response['iTotalDisplayRecords'] = intval($content_count);
			echo json_encode($data_tables_response);
			exit;
		}

		$this->_template->set('content_data', $content_array);
		$this->_set_search_context(I18n::get("nav.site.search.supplies"));
		$this->_set_content('supplies');
	}

	/**
	 * Function to retrieve item details
	 */
	public function action_item_details() {
		$item_id = $this->request->param('id'); 
		$item_details = ORM::factory('Supply',$item_id);
		$supplyTransactions = ORM::factory('SupplyTransaction')
				->get_transactions($item_id);
		$this->_template->set('transactions',$supplyTransactions);
		$this->_template->set('item_details',$item_details);
		$this->_set_content('item_details');

	}

	/**
	 * Function to perform an edit or add a supply item
	 */
	public function action_edit() {
		$id = $this->request->param('id');
		$supply = ORM::factory('Supply', $id);
		$path_to_images = url::base() . 'avatars/supplies';
		$this->_template->set('path', $path_to_images);
		// This line was commented out because of many js/view errors on calling this
		// url.. see lines further below $this->_template->set('ajax_save_url',
		// $this->_template->current_url); view flag to check whether save was 
		// unsuccessfull after a post
		$this->_template->set('save_failed', 1);
		$directory = DOCROOT . 'assets/supplies/';
		if ($this->request->post()) {
			// Bind values to table columns
			$supply->values($this->request->post());
			try {
				// Upload item main image
				if (isset($_FILES['item_avatar'])) {
					$directory = DOCROOT . 'assets/supplies/';
					$avatar = $this->save_image($_FILES['item_avatar'], 200, 300,
							$directory);
					$supply->avatar = $avatar;
				} else {
					if (!$supply->avatar)
						$supply->avatar = "default-item.jpg";
				}
				$supply->save();
				// Upload item images
				for ($i = 1; $i <= 5; $i++) {
					if (isset($_FILES['image' . $i])) {
						try {
							$directory = DOCROOT . 'assets/supplies/';
							$media = ORM::factory('Media');
							$avatar = $this->save_image($_FILES['image' . $i], 200,
									300, $directory);
							$media->filename = $avatar;
							$media->supply_id = $supply->supply_id;
							$media->media_id = 1;
							$media->save();
						} catch(Exception $e) {
							$this->_set_msg('Please correct the errors below.',
									'error', true);
						}
					}
				}
				// Update supplier
				$this->_set_msg('Successfully saved!', 'success', true);
				// This flag is used to know whether or not a new record was
				// successfully added
				if (!intval($id)) {
					$this->_template->set('added_record', 1);
				}
				$this->_template->set('save_failed', 0);
				$id = $supply->supply_id;
			} catch (Exception $e) {
				$errors = array();

				if ($e instanceof ORM_Validation_Exception) {
					$errors = $e->errors('models');
				}
				$this->_set_msg(
						'Please correct the errors below.', 'error', $errors);
			}
		}
		// More of corective url adjustments in case JS screws this up
		if (!intval($id)) {
			$this->_template->set('addform', 1);
			$this->_template->set('ajax_save_url',
					$this->_template->controller_base_url . 'edit');
		} else {
			$this->_template->set('ajax_save_url',
					$this->_template->controller_base_url . 'edit/' . $id);
			}
		// Check if profile image exit
		$supply_types = ORM::factory('SupplyType')->find_all();
		$package_types = ORM::factory('SupplyPackageType')->find_all();
		$unit_types = ORM::factory('SupplyUnitType')->find_all();
		$this->_template->set('supply_types', $supply_types);
		$this->_template->set('package_types', $package_types);
		$this->_template->set('unit_types', $unit_types);
		$this->_template->set('content_data', $supply);
		$this->_set_content('supply_edit');
	}

	/**
	 * Function to perform a retrieve images
	 */
	public function action_images() {
		$id = $this->request->param('id');
		$images_data = ORM::factory('Media')
				->where('supply_id', '=', $id)->find_all();
		if ($images_data->count() == 0) {
			$this->_template->set('imageerror', 1);
			$this->_set_msg('No images available for the specified product.',
					'error', TRUE);
		}
		$path_to_images = url::base() . 'avatars/supplies';
		$this->_template->set('images_data', $images_data);
		$this->_template->set('path', $path_to_images);
		$this->_template->set('supply_id', $id);
		$this->_template->set('ajax_save_url',
				$this->_template->controller_base_url . 'images/' . $id);
		$this->_template->set('images_data', $images_data);
		$this->_set_content('supplies_images');
	}

	public function action_uploadimages() {
		$id = $this->request->param('id');

		if ($this->request->post()) {
			for ($i = 1; $i <= 5; $i++) {
				if (isset($_FILES['image' . $i])) {
					try {
						$directory = DOCROOT . 'assets/supplies/';
						$media = ORM::factory('Media');
						$avatar = $this->save_image(
								$_FILES['image' . $i], 200, 300, $directory);
						$media->filename = $avatar;
						$media->supply_id = $id;
						$media->media_id = 1;
						$media->save();
						$this->_set_msg(
								'image uploaded successfull.', 'success', true);
					} catch(Exception $e) {
						$this->_set_msg(
								'Please correct the errors below.', 'error', true);
					}
				}
			}

		}
		$this->_template->set('ajax_save_url', 
			$this->_template->controller_base_url . 'uploadimages/' . $id);
		$this->_set_content('supply_upload_images');
	}

	/**
	 * Function to perform a delete on supply item
	 */
	public function action_delete() {
		$id = $this->request->param('id');
		if ($id) {
			$supply = ORM::factory('Supply', $id);
			if ($supply->loaded()) {
				try {
					$supply->delete_status = 1;
					$supply->save();
				} catch (Exception $e) {
					$this->_set_msg('Could not delete inventory item because'.
							' this would wipe their history!', 'error');
				}
			}
			$this->redirect($this->request->referrer());
		}
	}

	/**
	 * Function to display item transactions tab in edit form
	 */
	public function action_transactions() {
		$id = $this->request->param('id');
		// Supply item id
		if ($id) {
			$supplyTransactions = ORM::factory('SupplyTransaction')
					->get_transactions($id);
			$this->_template->set('content_data', $supplyTransactions);
			$this->_template->set('supply_id', $id);
		}
		$this->_set_content('supply_item_transactions');
	}

	/**
	 * Function to display details of a single transaction
	 */
	public function action_transaction() {
		$id = $this->request->param('id');
		// Transaction id
		if (empty($id)) {
			$this->_set_msg(
					'No information can be provided for that transaction',
					'error', true);
			$this->_set_content('messages_only');
			return;
		}
		$ids = explode(', ', $id);
		// Support for displaying mutiple related transactions in the same popup
		if (count($ids)) {
			// TODO: we're in multiple mode, modify the table in the view to display
			// data based on this ids
		}

		$trans_number = '';
		try {
			$transaction = ORM::factory('SupplyTransaction', $id);
			$user = ORM::factory('User', $transaction->user_id);
			$personnel = ORM::factory('Personnel')
					->where('user_id', '=', $user->id)->find();
			$content = array();
			$content['user'] = $user;
			$content['personnel'] = $personnel;
			$content['transaction'] = $transaction;
			if ($transaction->supply_transactions_type_id == 
					Model_SupplyTransaction::$addition_type_id) {
				$supplyPurchase = ORM::factory(
						'SupplyPurchase', $transaction->fk_key_field_id);
				$supplier = ORM::factory('Supplier', $supplyPurchase->supplier_id);
				$user = ORM::factory('User', $transaction->user_id);
				$personnel = ORM::factory('Personnel')
					->where('user_id', '=', $user->id)->find();

				$content['purchase'] = 1;
				$content['supplyPurchase'] = $supplyPurchase;
				$content['supplier'] = $supplier;

				$this->_template->set('content_data', $content);
				$this->_set_content('supply_transaction_details');
				return;
			} else if ($transaction->supply_transactions_type_id ==
					Model_SupplyTransaction::$move_type_id) {
				$supplyMove = ORM::factory(
						'SupplyMove', $transaction->fk_key_field_id);

				$from_shelf = ORM::factory(
						'SupplyShelf', $supplyMove->move_from_sssp_id);
				$to_shelf = ORM::factory(
						'SupplyShelf', $supplyMove->move_to_sssp_id);
				$content['move'] = 1;
				$content['supplyMove'] = $supplyMove;
				$content['from_shelf'] = $from_shelf;
				$content['to_shelf'] = $to_shelf;

				$this->_template->set('content_data', $content);
				$this->_set_content('supply_transaction_details');
				return;
			} else if ($transaction->supply_transactions_type_id ==
					Model_SupplyTransaction::$shrink_type_id) {
				$SupplyShrink = ORM::factory(
						'SupplyShrink', $transaction->fk_key_field_id);

				$content['shrink'] = 1;
				$content['supplyShrink'] = $SupplyShrink;

				$this->_template->set('content_data', $content);
				$this->_set_content('supply_transaction_details');
				return;
			} else if ($transaction->supply_transactions_type_id ==
					Model_SupplyTransaction::$allocation_type_id) {
				$supplyAllocation = ORM::factory(
					'SupplyAllocation', $transaction->fk_key_field_id);
				$allocations = Model_Allocation::get_inventory_allocations(
					$supplyAllocation->supply_allocation_id);
				$allocation_recipient = array();
				if ($supplyAllocation->client_id != 0) {
					$client = ORM::factory('Client', $supplyAllocation->client_id);
					$allocation_recipient['name'] = $client->client_name;
					$allocation_recipient['telephone'] = $client->client_telephone;
					$allocation_recipient['email'] = $client->client_email_address;
					$allocation_recipient['recipient_type'] = 'Client';
				} else if ($supplyAllocation->personnel_id != 0) {
					$personnel = ORM::factory(
						'Personnel', $supplyAllocation->personnel_id);
					$allocation_recipient['name'] = $personnel->personnel_name;
					$allocation_recipient['telephone'] = 
							$personnel->personnel_telephone;
					$allocation_recipient['email'] = 
							$personnel->personnel_email_address;
					$allocation_recipient['recipient_type'] = 'Personnel';
				} else {
					$allocation_recipient['name'] = 'N/A';
					$allocation_recipient['telephone'] = 'N/A';
					$allocation_recipient['email'] = 'N/A';
					$allocation_recipient['recipient_type'] = 'N/A';
				}

				$sum = 0;
				foreach ($allocations as $alloc) {
					if (is_numeric($alloc['quantity']))
						$sum += $alloc['quantity'];
				}

				$content['allocate'] = 1;
				$content['allocation_sum'] = $sum;
				$content['allocation_recipient'] = $allocation_recipient;
				$content['supplyAllocation'] = $supplyAllocation;
				$content['allocations'] = $allocations;

				$trans_number = 'PULL # '.$supplyAllocation->supply_allocation_id;
				$report_params = array(
					'report_type' => 'pull_sheet',
					'supply_allocation_id' =>
							$supplyAllocation->supply_allocation_id,
					'transaction_id' => $trans_number,
					'user' => $personnel->personnel_name,
					'total_allocation' => $sum,
					'transaction_date' => date('F j, Y g:i a',
							strtotime($supplyAllocation->allocation_date)),
					'recepient_name' => $allocation_recipient['name'],
					'recepient_phone' => (string)$allocation_recipient['telephone'],
					'recepient_email' => $allocation_recipient['email']
				);
				$content['report_url'] = 
						$this->_template->controller_base_url . "report?" .
						http_build_query($report_params);

				$this->_template->set('content_data', $content);
				$this->_set_content('supply_transaction_details');
				return;
			} else {
				$this->_set_msg($transaction->id . 'Could not correctly retrieve'.
						' that transaction\'s information', 'error', true);
				$this->_set_content('messages_only');
			}
			return;
			// Important. Do not remove
		} catch(Exception $e) {
			$this->_set_msg('An error occurred while retrieving information for '.
					'that transaction', 'error', true);
			$this->_set_content('messages_only');
			return;
		}
		$this->_set_msg('No information to display', 'error', true);
		$this->_set_content('messages_only');
	}

	public function action_supplier_export_excel_pull_transaction_sheet() {
		$id = $this->request->param('id');

		$spreadsheet = Spreadsheet::factory(array(
				'author'  => 'current user',
				'title'	  => 'Pull Sheet Report',
				'subject' => 'Inventory Pull Sheet',
				'description'  => 'Inventory Pull Sheet Report',
				'path' =>  DOCROOT.'reports/',
				'name' => 'efpro_inventory_pull_sheet'
			));
		$spreadsheet->set_active_worksheet(0);

				if (empty($id)) {
			$this->_set_msg('No information can be provided for '.
					'that transaction', 'error', true);
			$this->_set_content('messages_only');
			return;
		}
		$ids = explode(', ', $id);
		// Support for displaying mutiple related transactions in the same popup
		if (count($ids)) {
			// TODO: we're in multiple mode, modify the table in the view to display 
			// data based on this ids
		}
		try {
			$transaction = ORM::factory('SupplyTransaction', $id);
			$user = ORM::factory('User', $transaction->user_id);
			$personnel = ORM::factory('Personnel')
					->where('user_id', '=', $user->id)->find();
			$content = array();
			$content['user'] = $user;
			$content['personnel'] = $personnel;
			$content['transaction'] = $transaction;
			if ($transaction->supply_transactions_type_id == 
					Model_SupplyTransaction::$addition_type_id) {
				$supplyPurchase = ORM::factory(
					'SupplyPurchase', $transaction->fk_key_field_id);
				$supplier = ORM::factory('Supplier', $supplyPurchase->supplier_id);
				$user = ORM::factory('User', $transaction->user_id);
				$personnel = ORM::factory('Personnel')
						->where('user_id', '=', $user->id)->find();

				$content['purchase'] = 1;
				$content['supplyPurchase'] = $supplyPurchase;
				$content['supplier'] = $supplier;


				return;
			} else if ($transaction->supply_transactions_type_id ==
					Model_SupplyTransaction::$move_type_id) {
				$supplyMove = ORM::factory(
						'SupplyMove', $transaction->fk_key_field_id);
				$from_shelf = ORM::factory(
						'SupplyShelf', $supplyMove->move_from_sssp_id);
				$to_shelf = ORM::factory(
						'SupplyShelf', $supplyMove->move_to_sssp_id);
				$content['move'] = 1;
				$content['supplyMove'] = $supplyMove;
				$content['from_shelf'] = $from_shelf;
				$content['to_shelf'] = $to_shelf;


				return;
			} else if ($transaction->supply_transactions_type_id ==
					Model_SupplyTransaction::$shrink_type_id) {
				$SupplyShrink = ORM::factory(
						'SupplyShrink', $transaction->fk_key_field_id);
				$content['shrink'] = 1;
				$content['supplyShrink'] = $SupplyShrink;


				return;
			} else if ($transaction->supply_transactions_type_id ==
					Model_SupplyTransaction::$allocation_type_id) {
				$supplyAllocation = ORM::factory(
						'SupplyAllocation', $transaction->fk_key_field_id);
				$allocations = Model_Allocation::get_inventory_allocations(
						$supplyAllocation->supply_allocation_id);
				$allocation_recipient = array();
				if ($supplyAllocation->client_id != 0) {
					$client = ORM::factory('Client', $supplyAllocation->client_id);
					$allocation_recipient['name'] = $client->client_name;
					$allocation_recipient['telephone'] = $client->client_telephone;
					$allocation_recipient['email'] = $client->client_email_address;
					$allocation_recipient['recipient_type'] = 'Client';
				} else if ($supplyAllocation->personnel_id != 0) {
					$personnel = ORM::factory(
							'Personnel', $supplyAllocation->personnel_id);
					$allocation_recipient['name'] = $personnel->personnel_name;
					$allocation_recipient['telephone'] =
							$personnel->personnel_telephone;
					$allocation_recipient['email'] =
							$personnel->personnel_email_address;
					$allocation_recipient['recipient_type'] = 'Personnel';
				} else {
					$allocation_recipient['name'] = 'N/A';
					$allocation_recipient['telephone'] = 'N/A';
					$allocation_recipient['email'] = 'N/A';
					$allocation_recipient['recipient_type'] = 'N/A';
				}

				$sum = 0;
				foreach ($allocations as $alloc) {
					if (is_numeric($alloc['quantity']))
						$sum += $alloc['quantity'];
				}

				$content['allocate'] = 1;
				$content['allocation_sum'] = $sum;
				$content['allocation_recipient'] = $allocation_recipient;
				$content['supplyAllocation'] = $supplyAllocation;
				$content['allocations'] = $allocations;
			}
			$allocations_items_array['columns'] = array(
				'Item Code', 'Item', 'Quantity', 'Location');
			$allocations_items_array['rows'] = array();

			foreach($content['allocations'] as $item) {
				$allocations_items_array['rows'][] = array(
					$item['product_code'],$item['supply_name'],
					$item['quantity'],
					$item['supply_shelf_name'] . ' , ' .
						$item['supply_location_name'],);
			}

			if(array_key_exists("shrink",$content)) {

				$allocations_items_array['rows'][] = array('');
				$allocations_items_array['rows'][] = array('');
				$allocations_items_array['rows'][] =
						array($content['transaction']->details);
				$allocations_items_array['rows'][] =
						array('Personnel',
							$content['personnel']->personnel_name . ' , ' .
							$content['user']->email);
				$allocations_items_array['rows'][] =
						array('Quantity',$content['supplyShrink']->quantity);
				$allocations_items_array['rows'][] = array('Date',
					$content['supplyShrink']->shrink_date);

			} else if(array_key_exists("allocate",$content)) {

				$allocations_items_array['rows'][] = array('');
				$allocations_items_array['rows'][] = array('');
				$allocations_items_array['rows'][] =
						array('TRANSACTION NUMBER',$content['transaction']->id);
				$allocations_items_array['rows'][] = array(
					'Personnel',$content['personnel']->personnel_name. ' , '.
					$content['user']->email);
				$allocations_items_array['rows'][] = array(
					'Quantity',$content['allocation_sum']);
				$allocations_items_array['rows'][] =
						array('Date',$content['supplyAllocation']->allocation_date);

				$allocations_items_array['rows'][] = array('');
				$allocations_items_array['rows'][] = array('RECIPIENT INFORMATION');
				$allocations_items_array['rows'][] = array(
					'Recipient type',
					$content['allocation_recipient']['recipient_type']
				);
				$allocations_items_array['rows'][] =
						array('Name',$content['allocation_recipient']['name']);
				$allocations_items_array['rows'][] =array(
					'Telephone',
					$content['allocation_recipient']['telephone']
				);
				$allocations_items_array['rows'][] =
						array('Email',$content['allocation_recipient']['email']);

			} else if(array_key_exists("move",$content)) {

				$allocations_items_array['rows'][] = array('');
				$allocations_items_array['rows'][] = array('');
				$allocations_items_array['rows'][] =
						array($content['transaction']->details);
				$allocations_items_array['rows'][] = array(
					'Personnel',
					$content['personnel']
							->personnel_name. ' , '.$content['user']->email
				);
				$allocations_items_array['rows'][] =
						array('Quantity',$content['supplyMove']->move_quantity);
				$allocations_items_array['rows'][] =
						array('Date',$content['supplyMove']->move_date);

			} else if(array_key_exists("purchase",$content)) {

				$allocations_items_array['rows'][] = array('');
				$allocations_items_array['rows'][] = array('');
				$allocations_items_array['rows'][] =
						array($content['transaction']->details);
				$allocations_items_array['rows'][] = array(
					'Personnel',
					$content['personnel']
							->personnel_name. ' , '. $content['user']->email
				);
				$allocations_items_array['rows'][] =
						array('Supplier',$content['supplier']->supplier_name);
				$allocations_items_array['rows'][] = array(
					'Date',
					$content['supplyPurchase']->supply_purchase_date
				);
				$allocations_items_array['rows'][] = array(
					'Quantity',
					$content['supplyPurchase']->supply_purchased_quantity
				);
			}

			$spreadsheet->set_data($allocations_items_array, false);
			$spreadsheet->send();
			exit;
		}Catch (Exception $e) {
			echo "Could not create report.";exit;
		}
	}

	/**
	 * Function to display move item transaction form and handle post
	 */
	public function action_move_item() {
		// Get users shelfs
		$id = $this->request->param('id');// sssp-id
		$shelfs = Model_SupplyShelf::get_shelf_locations();
		
		//get previous shelf data
		$current_sssp_shelf = ORM::factory('SupplyShelfSupplyPurchase', $id);
		$current_sssp_shelf_id = $current_sssp_shelf->supply_shelf_id;
		$content = array();
		$content['purchase_id'] = $id;
		$content['shelfs'] = $shelfs;
		$this->_template->bind('content_data', $content);
		$this->_template->set('ajax_save_url', $this->_template->current_url);
		if ($this->request->post()) {
			// Check if user is moving to the same shelf
			if ($current_sssp_shelf_id != $this->request->post('shelf_id')) {
				$move_quantity = $this->request->post('quantity');
				if (!is_numeric($move_quantity) || $move_quantity < 0) {
					$this->_set_msg('You specified an invalid shrink value',
							'error', true);
					$this->_set_content('supply_transaction_move');
					return;
				}

				$supplyPurchase = ORM::factory('SupplyPurchase',
						$current_sssp_shelf->supply_purchase_id);
				$supply_id = $supplyPurchase->supply_id;

				$shelf_from_data = Model_SupplyShelf::
						get_locations_for_supplyitem_on_shelf
						($supply_id, $current_sssp_shelf_id);
				if (empty($shelf_from_data[0]['supply_qty_on_hand'])) {
					$this->_set_msg('There was a problem with the shelf-move'.
							' process. Please try again', 'error', true);
				} else if ($move_quantity >
						$shelf_from_data[0]['supply_qty_on_hand']) {
					$this->_set_msg('You specfied an excessive move value',
							'error', true);
				} else {
					try {
						// Shrink shelf-count for current shelfs till
						// move-count is reached
						$sssp_data = Model_SupplyShelf::
								get_all_locations_for_supplyitem
								($supply_id, $current_sssp_shelf_id);
						foreach ($sssp_data as $sssp_rec) {
							if ($move_quantity < $sssp_rec['supply_current_count']) {
								$supply_shelf_rec = ORM::
										factory(
											'SupplyShelfSupplyPurchase',
											$sssp_rec
											['supply_shelves_supply_purchases_id']
										);
								$supply_shelf_rec->supply_current_count = 
										$supply_shelf_rec->supply_current_count -
										$move_quantity;
								$supply_shelf_rec->save();

								break;
								// Important. Since just this sssp can be shrunk,
								// we dont need to shrink on other sssp records
							} else {
								$move_quantity -= $sssp_rec['supply_current_count'];
								$supply_shelf_rec = ORM::
									factory('SupplyShelfSupplyPurchase', 
									$sssp_rec['supply_shelves_supply_purchases_id']);
								$supply_shelf_rec->supply_current_count = 0;
								$supply_shelf_rec->save();
								// Loop should continue to next sssp since the
								// current one has fewer units than can be shrunk
							}
						}
						$move_quantity = $this->request->post('quantity');
						// Set back $move_quantity to initial value

						$sssp_shelf = ORM::factory('SupplyShelfSupplyPurchase')->where('supply_shelf_id', '=', $this->request->post('shelf_id'))->where('supply_purchase_id', '=', $current_sssp_shelf->supply_purchase_id)->where('supply_current_count', '>', 0)->find_all();
						if ($sssp_shelf->count() == 0) {
							$destination_sssp_shelf = ORM::factory('SupplyShelfSupplyPurchase');
							$destination_sssp_shelf->supply_initial_count = 0;
							$destination_sssp_shelf->supply_current_count = 0;
							$destination_sssp_shelf->supply_id = $supply_id;
						} else {
							$destination_sssp_shelf = ORM::factory('SupplyShelfSupplyPurchase', $sssp_shelf[0]->supply_shelves_supply_purchases_id);
						}
						$destination_sssp_shelf->supply_shelf_id = $this->request->post('shelf_id');
						$destination_sssp_shelf->supply_initial_count += $move_quantity;
						$destination_sssp_shelf->supply_current_count += $move_quantity;
						$destination_sssp_shelf->supply_purchase_id = $current_sssp_shelf->supply_purchase_id;
						$destination_sssp_shelf->save();

						// Record move, useful in reversing action in transactions tab
						$supplyMove = ORM::factory('SupplyMove');
						$supplyMove->move_quantity = $move_quantity;
						$supplyMove->move_from_sssp_id = $current_sssp_shelf->supply_shelves_supply_purchases_id;
						$supplyMove->move_to_sssp_id = $destination_sssp_shelf->supply_shelves_supply_purchases_id;
						$supplyMove->move_description = $this->request->post('move_description');
						$supplyMove->move_date = Date('Y-m-d H:i:s');
						$supplyMove->user_id = Auth::instance()->get_user()->id;
						$supplyMove->save();

						$from_shelf = ORM::factory('SupplyShelf', $current_sssp_shelf->supply_shelf_id);
						$to_shelf = ORM::factory('SupplyShelf', $destination_sssp_shelf->supply_shelf_id);
						$details = 'Move of supply-item from shelf ' . $from_shelf->supply_shelf_name . ' to shelf ' . $to_shelf->supply_shelf_name;
						$personnel_id = Auth::instance()->get_user()->id;
						Model_SupplyTransaction::record_transaction($supply_id, Model_SupplyTransaction::$move_type_id, $supplyMove->supplies_move_id, $personnel_id, $details, $move_quantity);

						$this->_set_msg('Move was successful', 'success', true);
					} catch(Exception $e) {
						$errors = array();
						if ($e instanceof ORM_Validation_Exception) {
							$errors = $e->errors('models');
						}
						$this->_set_msg('Please correct the errors below.', 'error', $errors);
					}
				}
			} else {
				$this->_set_msg('You cannot move to the same shelf', 'error', true);
			}
		}
		$this->_set_content('supply_transaction_move');
	}

	/**
	 * Function to handle item shinks on a shelf basis, from an AJAX request
	 */
	public function action_shrink_item() {
		$id = $this->request->param('id');
		// Supply purchase shelves id
		$content = array();
		$content['id'] = $id;
		$this->_template->bind('content_data', $content);
		if ($this->request->post()) {
			$sssp_id = $this->request->post('shrink_purchases_id');
			$sssp = ORM::factory('SupplyShelfSupplyPurchase', $sssp_id);
			$supplyPurchase = ORM::factory('SupplyPurchase', $sssp->supply_purchase_id);

			$supply_shelf_id = $sssp->supply_shelf_id;
			$supply_id = $supplyPurchase->supply_id;
			$shrink_quantity = $this->request->post('quantity');
			$shelf_data = Model_SupplyShelf::get_locations_for_supplyitem_on_shelf($supply_id, $supply_shelf_id);

			if (!is_numeric($shrink_quantity) || $shrink_quantity < 0) {
				$this->_set_msg('You specified an invalid shrink value', 'error', true);
				$this->_set_content('supplies_shrink');
				return;
			}

			if (empty($shelf_data[0]['supply_qty_on_hand'])) {
				$this->_set_msg('There was a problem with the shrink process. Please try again', 'error', true);
			} else if ($shrink_quantity > $shelf_data[0]['supply_qty_on_hand']) {
				$this->_set_msg('You specfied an excessive shrink value', 'error', true);
			} else {
				try {
					$sssp_data = Model_SupplyShelf::get_all_locations_for_supplyitem($supply_id, $supply_shelf_id);
					$shrink_id = 0;
					foreach ($sssp_data as $sssp_rec) {
						if ($shrink_quantity < $sssp_rec['supply_current_count']) {
							$shrink = ORM::factory('SupplyShrink');
							$shrink->quantity = $shrink_quantity;
							$shrink->sssp_id = $sssp_rec['supply_shelves_supply_purchases_id'];
							$shrink->shrink_date = Date('Y-m-d H:i:s');
							$shrink->user_id = Auth::instance()->get_user()->id;
							$shrink->shrink_description = $this->request->post('shrink_description');
							$shrink->save();
							$shrink_id = $shrink->supplies_shrink_id;

							$supply_shelf_rec = ORM::factory('SupplyShelfSupplyPurchase', $sssp_rec['supply_shelves_supply_purchases_id']);
							$supply_shelf_rec->supply_current_count = $supply_shelf_rec->supply_current_count - $shrink_quantity;
							$supply_shelf_rec->save();

							break;
							// Important. Since just this sssp can be shrunk, we dont need to shrink on other sssp records
						} else {
							$shrink_quantity -= $sssp_rec['supply_current_count'];
							$shrink = ORM::factory('SupplyShrink');
							$shrink->quantity = 0;
							$shrink->sssp_id = $sssp_rec['supply_shelves_supply_purchases_id'];
							$shrink->shrink_date = Date('Y-m-d H:i:s');
							$shrink->user_id = Auth::instance()->get_user()->id;
							$shrink->shrink_description = $this->request->post('shrink_description');
							$shrink->save();
							$shrink_id = $shrink->supplies_shrink_id;

							$supply_shelf_rec = ORM::factory('SupplyShelfSupplyPurchase', $sssp_rec['supply_shelves_supply_purchases_id']);
							$supply_shelf_rec->supply_current_count = 0;
							$supply_shelf_rec->save();
							// Loop should continue to next sssp since the current one has fewer units than can be shrunk
						}
					}
					// Update total item amount of the supply-item
					$this->total_quantity($supply_id);

					$id = $this->request->param('id');
					// Supply purchase shelves id

					$shrink_quantity = $this->request->post('quantity');
					$personnel_id = Auth::instance()->get_user()->id;
					$shelf_rec = ORM::factory('SupplyShelf', $supply_shelf_id);
					$details = 'Shrink of inventory on shelf ' . $shelf_rec->supply_shelf_name;
					Model_SupplyTransaction::record_transaction($supply_id, Model_SupplyTransaction::$shrink_type_id, $shrink_id, $personnel_id, $details, $shrink_quantity);

					$content['id'] = $sssp_id;
					$this->_set_msg('Successfully saved!', 'success', true);
				} catch(Exception $e) {
					$errors = array();
					if ($e instanceof ORM_Validation_Exception) {
						$errors = $e->errors('models');
					}
					$this->_set_msg('Please correct the errors below.', 'error', $errors);
				}
			}
		}
		$this->_set_content('supplies_shrink');
	}

	/**
	 * Function to get a list of AVAILABLE items for the multiple allocate page
	 * TODO: Ensure this function ONLY returns a list of items with at least 1 quantity available
	 */
	public function action_allocate_item_list() {
		$filter = $this->request->post('filter');
		// User input, filter item list based on this using LIKE
		$limit = 30;
		$supplies = ORM::factory('Supply')->limit($limit);
		if (!empty($filter)) {
			$list = $supplies->where('supply_name', 'LIKE', '%' . $filter . '%')->and_where('total_quantity', '>', '0')
					->find_all()->as_array(null, 'supply_name');
		} else {
			$list = $supplies->where('total_quantity', '>', '0')->find_all()->as_array(null, 'supply_name');
		}
		$this->_set_msg('Success', 'success', $list);
	}

	/**
	 * Function to get an item's count list based on its SHELF NAME for the multiple allocate page
	 * TODO: Integrate filter capability here
	 */
	public function action_allocate_item_shelf_count_list() {
		$ssspId = $this->request->post('ssspId');
		$shelf_id = $this->request->post('shelf_id');
		$filter = $this->request->post('filter');
		// User input, filter count list based on this using LIKE (Yeah, we might have shelves with thousands of items)
		$limit = 10;
		if (!empty($shelf_id) && !empty($ssspId)) {
			$sssp = ORM::factory('SupplyShelfSupplyPurchase', $ssspId);
			$supply_id = Model_SupplyShelfSupplyPurchase::get_supply_id($ssspId);
			if ($supply_id != null) {
				$item_locations = Model_SupplyShelf::get_aggregate_shelf_location_for_supplyitem($supply_id, $shelf_id);
				$list = array();
				$item_counter = 1;
				foreach ($item_locations as $loc) {
					if ($item_counter > $limit)
						break;
					$loop_start_val = is_numeric($filter) && $filter > 1 ? $filter : 1;
					$limit += $loop_start_val;
					for ($i = $loop_start_val; ($i <= $loc['supply_qty_on_hand'] && $i <= $limit); $i++) {
						if (!in_array((string)$i, $list)) {
							$list[] = (string)$i;
							// Values MUST be strings
							$item_counter++;
						}
					}
				}
				$this->_set_msg('Success', 'success', $list);
			} else {
				$this->_set_msg("Invalid item/shelf.", "error");
			}
		} else {
			$this->_set_msg("Please specify an item and shelf.", "error");
		}
	}

	/**
	 * Function to get an item's shelf based on its NAME for the multiple allocate page
	 * TODO: Integrate filter capability here
	 */
	public function action_allocate_item_shelf_list() {
		$item = $this->request->post('item');
		// Item name
		$filter = $this->request->post('query');
		// User input, filter shelf list based on this using LIKE
		$limit = 30;
		if ($item) {
			$supply = ORM::factory('Supply')->where('supply_name', '=', $item)->find();
			if ($supply->loaded()) {
				$item_locations = Model_SupplyShelf::get_grouped_locations_for_supplyitem($supply->supply_id);
				$list = array();
				$item_counter = 1;
				foreach ($item_locations as $loc) {
					if ($item_counter > $limit) {
						break;
					}
					$shelf_name = $loc['supply_shelf_name'] . ', ' . $loc['supply_location_name'] . ' : ' . $loc['supply_qty_on_hand'];
					$list['shelves'][] = $shelf_name;
					$list['sssp_ids'][$shelf_name] = $loc['supply_shelves_supply_purchases_id'];
					$list['shelf_ids'][$shelf_name] = $loc['supply_shelf_id'];
					$item_counter++;
				}
				$this->_set_msg('Success', 'success', $list);
			} else {
				$this->_set_msg("Invalid item.", "error");
			}
		} else {
			$this->_set_msg("Please specify an item.", "error");
		}
	}

	/**
	 * Function to allocate a single item to client/personnel from a specific shelf
	 */
	public function action_allocate_item_from_shelf() {
		$supply_id = $this->request->param('id');
		$supply = ORM::factory('Supply', $supply_id);
		$this->_template->set('ajax_save_url', $this->_template->current_url);
		$supply_shelves = Model_SupplyShelf::get_grouped_locations_for_supplyitem($supply_id);
		$this->_template->set('supply_name', $supply->supply_name);
		$this->_template->set('content_data', $supply_shelves);
		$this->_set_content('supply_transaction_allocate');
	}

	/**
	 * Function to allocate a single item to client/personnel.
	 * If a sssp_id URL-param supplied, only the associated shelf will be listed
	 * in the list of shelves to allocate from.
	 */
	public function action_allocate_item() {
		$supply_id = $this->request->param('id');
		$supply = ORM::factory('Supply', $supply_id);
		$sssp_id = $this->request->query('sssp_id');
		if (empty($sssp_id)) {
			$supply_shelves = Model_SupplyShelf::get_grouped_locations_for_supplyitem($supply_id);
		} else {
			$this->_template->set('sssp_id', $sssp_id);
			$sssp = ORM::factory('SupplyShelfSupplyPurchase', $sssp_id);
			$shelf_id = $sssp->supply_shelf_id;
			$supply_shelves = Model_SupplyShelf::get_aggregate_shelf_location_for_supplyitem($supply_id, $shelf_id);
		}
		$this->_template->set('ajax_save_url', $this->_template->current_url.'?sssp_id='.$sssp_id);
		if ($this->request->post()) {
			$_supply_shelves = array();
			foreach ($supply_shelves as $key => $value) {
				$_supply_shelves[$value['supply_shelves_supply_purchases_id']] = $value;
			}
			$passed_allocation_ids = array();
			$errors_saving_allocation = false;

			// Validate quantities before recording allocation.
			foreach ($this->request->post('quantity') as $sssp_id => $qty) {
				if ($qty == 0 || !is_numeric($qty)) {
					continue;
				}
				if ($qty > $_supply_shelves[$sssp_id]['supply_qty_on_hand']) {
					$this->_set_msg('You specified invalid allocation amounts. Please try again', 'error', true);
					$this->_template->set('supply_name', $supply->supply_name);
					$this->_template->set('content_data', $supply_shelves);
					$this->_set_content('supply_transaction_allocate');
					return;
				}
			}

			$recepient_id = $this->request->post('recepient_id');
			$recepient_type = $this->request->post('recepient_type');
			// Some minor validation on recipient data.
			if (empty($recepient_id) || empty($recepient_type)) {
				$this->_set_msg('You need to specify a recipient for this allocation', 'error', true);
				$this->_template->set('supply_name', $supply->supply_name);
				$this->_template->set('content_data', $supply_shelves);
				$this->_set_content('supply_transaction_allocate');
				return;
			}

			// Record allocation.
			$allocated_quantity = 0;
			$supply_allocation = ORM::factory('SupplyAllocation');
			$supply_allocation->{$recepient_type . '_id'} = $recepient_id;
			$supply_allocation->allocation_date = date('Y-m-d H:i:s');
			$supply_allocation->user_id = Auth::instance()->get_user()->id;
			$supply_allocation->allocation_description = $this->request->post('allocation_description');
			$supply_allocation->save();

			foreach ($this->request->post('quantity') as $sssp_id => $qty) {
				if ($qty == 0 || !is_numeric($qty))
					continue;
				try {
					// Shrink inventory, then record allocation
					$sssp_shelf_id = $_supply_shelves[$sssp_id]['supply_shelf_id'];
					$sssp_data = Model_SupplyShelf::get_all_locations_for_supplyitem($supply_id, $sssp_shelf_id);
					$shrink_quantity = $qty;
					foreach ($sssp_data as $sssp_rec) {
						if ($shrink_quantity < $sssp_rec['supply_current_count']) {
							$supply_shelf_rec = ORM::factory('SupplyShelfSupplyPurchase', $sssp_rec['supply_shelves_supply_purchases_id']);
							$supply_shelf_rec->supply_current_count = $supply_shelf_rec->supply_current_count - $shrink_quantity;
							$supply_shelf_rec->save();

							break;
							// Important. Since just this sssp can be shrunk, we dont need to shrink on other sssp records
						} else {
							$shrink_quantity -= $sssp_rec['supply_current_count'];
							$supply_shelf_rec = ORM::factory('SupplyShelfSupplyPurchase', $sssp_rec['supply_shelves_supply_purchases_id']);
							$supply_shelf_rec->supply_current_count = 0;
							$supply_shelf_rec->save();
							// Loop should continue to next sssp since the current one has fewer units than can be shrunk
						}
					}
					// Update current total quantity  availble for the item  after allocation
					$this->total_quantity($supply_id);

					$allocation = ORM::factory('Allocation');
					$allocation->supply_allocation_id = $supply_allocation->supply_allocation_id;
					$allocation->sssp_id = $sssp_id;
					$allocation->quantity = $qty;
					$allocation->save();

					$allocated_quantity += $qty;

					$this->_set_msg('Allocation was successful', 'success', true);
				} catch (Exception $e) {
					echo $e->getMessage();
					exit ;
					$errors_saving_allocation = true;
					$errors = array();
					if ($e instanceof ORM_Validation_Exception) {
						$errors = $e->errors('models');
					}
					break;
				}
			}
			$personnel_id = Auth::instance()->get_user()->id;
			$alloc_desc = $this->request->post('allocation_description');
			$details = $alloc_desc;
			$trans_id = Model_SupplyTransaction::record_transaction($supply_id, Model_SupplyTransaction::$allocation_type_id, $supply_allocation->supply_allocation_id, $personnel_id, $details, $allocated_quantity);
			$this->_template->set('trans_id', $trans_id);

			// Redirection here is done because I couldn't get implement a modal-->child modal-->child modal to load on the UI
			// TODO: Enable modal-->child modal-->child modal on UI and remove this redirection. Second child-modal is intended for the
			// pull sheet.
			$this->redirect('Supplies/transaction/'.$trans_id); exit;

			// Update supply counts
			if (empty($sssp_id)) {
				$supply_shelves = Model_SupplyShelf::get_grouped_locations_for_supplyitem($supply_id);
			} else {
				$sssp = ORM::factory('SupplyShelfSupplyPurchase', $sssp_id);
				$shelf_id = $sssp->supply_shelf_id;
				$supply_shelves = Model_SupplyShelf::get_aggregate_shelf_location_for_supplyitem($supply_id, $shelf_id);
			}
		}
		$this->_template->set('supply_name', $supply->supply_name);
		$this->_template->set('content_data', $supply_shelves);
		$this->_set_content('supply_transaction_allocate');
	}

	/**
	 * Function to allocate multiple items to client/personnel
	 */
	public function action_allocate() {
		$data = $this->request->post('data');
		if ($data) {
			try {
				$recepient_id = $this->request->post('recepient_id');
				$recepient_type = $this->request->post('recepient_type');
				$allocation_description = $this->request->post('allocation_description');

				// Some minor validation on recipient data.
				if (empty($recepient_id) || empty($recepient_type)) {
					$this->_set_msg('You need to specify a recipient for this allocation', 'error', true);
					$this->_template->set('ajax_save_url', $this->_template->current_url);
					$this->_set_content('supply_transaction_allocate_multiple');
					return;
				}

				$supply_allocation = ORM::factory('SupplyAllocation');
				$supply_allocation->{$recepient_type . '_id'} = $recepient_id;
				$supply_allocation->allocation_date = date('Y-m-d H:i:s');
				$supply_allocation->user_id = Auth::instance()->get_user()->id;
				$supply_allocation->allocation_description = $allocation_description;
				$supply_allocation->save();

				$saved = array();
				$transaction_ids = array();
				// Holds data to be returned on success
				$supply_ids = array();
				$allocated_quantity = 0;
				$personnel_id = Auth::instance()->get_user()->id;
				// If (!empty($allocation_description)) $allocation_description = ': ' . $allocation_description;
				$details = $allocation_description;
				for ($row = 0, $row_len = count($data); $row < $row_len; $row++) {
					$sssp_id = $data[$row][3];
					$quantity = $data[$row][2];
					$item_name = $data[$row][0];
					$sssp = ORM::factory('SupplyShelfSupplyPurchase', $sssp_id);
					$shelf_name = $data[$row][1];
					$shelf = ORM::factory('SupplyShelf', $sssp->supply_shelf_id);
					$sp = ORM::factory('SupplyPurchase', $sssp->supply_purchase_id);
					$supply = ORM::factory('Supply', $sp->supply_id);
					$supply_id = $supply->supply_id;

					$_supply_shelves = Model_SupplyShelf::get_aggregate_shelf_location_for_supplyitem($supply_id, $shelf->supply_shelf_id);
					$passed_allocation_ids = array();
					$errors_saving_allocation = false;

					// Validate quantities before recording allocation.
					if ($quantity == 0 || !is_numeric($quantity) || $quantity > $_supply_shelves[0]['supply_qty_on_hand']) {
						$this->_set_msg('You specified invalid allocation amounts. Please try again', 'error', true);
						$this->_template->set('ajax_save_url', $this->_template->current_url);
						$this->_set_content('supply_transaction_allocate_multiple');
						return;
					}

					// Shrink inventory
					$sssp_shelf_id = $_supply_shelves[0]['supply_shelf_id'];
					$sssp_data = Model_SupplyShelf::get_all_locations_for_supplyitem($supply_id, $sssp_shelf_id);
					$shrink_quantity = $quantity;
					foreach ($sssp_data as $sssp_rec) {
						if ($shrink_quantity < $sssp_rec['supply_current_count']) {
							$supply_shelf_rec = ORM::factory('SupplyShelfSupplyPurchase', $sssp_rec['supply_shelves_supply_purchases_id']);
							$supply_shelf_rec->supply_current_count = $supply_shelf_rec->supply_current_count - $shrink_quantity;
							$supply_shelf_rec->save();

							break;
							// Important. Since just this sssp can be shrunk, we dont need to shrink on other sssp records
						} else {
							$shrink_quantity -= $sssp_rec['supply_current_count'];
							$supply_shelf_rec = ORM::factory('SupplyShelfSupplyPurchase', $sssp_rec['supply_shelves_supply_purchases_id']);
							$supply_shelf_rec->supply_current_count = 0;
							$supply_shelf_rec->save();
							// Loop should continue to next sssp since the current one has fewer units than can be shrunk
						}
					}
					// Update current total quantity  availble for the item  after allocation
					$this->total_quantity($supply_id);

					$allocation = ORM::factory('Allocation');
					$allocation->supply_allocation_id = $supply_allocation->supply_allocation_id;
					$allocation->sssp_id = $sssp_id;
					$allocation->quantity = $quantity;
					$allocation->save();

					$this->_set_msg('Allocation was successful', 'success', true);

					$allocated_quantity += $quantity;
					$supply_ids[] = $supply_id;
					$saved[] = $row;

					$transaction_ids[0] = Model_SupplyTransaction::record_transaction($supply_id, Model_SupplyTransaction::$allocation_type_id, $supply_allocation->supply_allocation_id, $personnel_id, $details, $quantity);
				}

				// Remove successfully saved records
				foreach ($saved as $row) {
					unset($data[$row]);
				}
				if (count($data) == 0) {
					$this->_set_msg('Records saved.', 'success', $transaction_ids);
				} else {
					$this->_set_msg('The above records were not saved.', 'error', $data);
					// Prepopulate this data back to table
				}
			} catch (Exception $e) {
				echo $e->getMessage();
				print_r($e->getTrace());
				exit ;
				$errors_saving_allocation = true;
				$errors = array();
				if ($e instanceof ORM_Validation_Exception) {
					$errors = $e->errors('models');
				}
				break;
			}
		}
		$this->_template->set('ajax_save_url', $this->_template->current_url);
		$this->_set_content('supply_transaction_allocate_multiple');
	}

	/**
	 * Function to pull recepients of a certain type for allocation form
	 */
	public function action_allocate_recepient_list() {
		$content = '<option value="">-- Choose One --</option>';
		$this->_template->bind('content_data', $content);
		$recepient_type = $this->request->param('id');
		if ($recepient_type == 'client' || $recepient_type == 'personnel') {
			$options = ORM::factory(ucfirst($recepient_type))->find_all()->as_array($recepient_type . '_id', $recepient_type . '_name');
			foreach ($options as $val => $label) {
				$content .= '<option value="' . $val . '">' . $label . '</option>';
			}
		}
		$this->_set_content('raw_string');
	}

	/**
	 * Function to update the total quantity of an item on purchase/allocate/shrink
	 */
	public function total_quantity($id) {
		$total_quantity = Model_Supply::get_shelves_totalquantity_per_item($id);
		foreach ($total_quantity as $t) {
			$total = $t['total'];
		}
		// Update total quantity
		$supply = ORM::factory('Supply', $id);
		$supply->total_quantity = $total;
		$supply->save();

	}

	/**
	 * Function to display item locations tab in edit form
	 */
	public function action_locations() {
		$id = $this->request->param('id');
		// Supply item id
		$content = array();
		$this->_template->bind('content_data', $content);
		if ($id) {
			$itemlocations = Model_SupplyShelf::get_grouped_locations_for_supplyitem($id);
			$this->_template->set('itemlocations', $itemlocations);
			$content['supply_id'] = $id;
			$supplies = ORM::factory('Supplier', $id)->supplies->find_all();
			$content['locations'] = $supplies;
		}
		$this->_set_content('supply_item_locations');
	}

	/**
	 * Function to display item comments tab in edit form
	 */
	public function action_comments() {
		$id = $this->request->param('id');
		// Fetch comments for supply item
		if ($this->request->query()) {
			if ($id) {
				$comments = ORM::factory('Supply', $id)->comments->find_all();
				$this->_template->set('content_data', $comments);
			}
			$this->_set_content('supply_item_comments');
		}
		// Add comment to supply item
		if ($this->request->post()) {
			$comment = ORM::factory('SupplyNote');
			$comment->values($this->request->post());
			$comment->supply_id = $id;
			$comment->supply_note_timestamp = date("Y-m-d H:i:s");
			try {
				$comment->save();
				$this->_set_msg('Added comment successfully', 'success', $comment->as_array());
			} catch (Exception $e) {
				$this->_set_msg('Could not add an empty commnet', 'error', TRUE);
			}
		}
	}

	/**
	 * Function to add purchases for a supply/item
	 */
	public function action_add_purchase() {
		$id = $this->request->param('id');
		// Supply item id
		$supply_id = $id;
		if (!$id) {
			$this->_set_msg('Missing supplier-ID', 'error', true);
			$this->_set_content('supply_purchases_add');
			return;
		}
		$package_types = ORM::factory('SupplyPackageType')->order_by('package_type_name', 'asc')->find_all();
		$suppliers = ORM::factory('Supplier')->order_by('supplier_name', 'asc')->find_all();
		$shelves = Model_SupplyShelf::get_put_shelves();
		$this->_template->set('supply_id', $id);
		$this->_template->set('package_types', $package_types);
		$this->_template->set('suppliers', $suppliers);
		$this->_template->set('shelves', $shelves);
		$locations_count = 0;
		if ($this->request->post()) {
			$shelf_locations = array();

			$count = 1;
			$sum = 0;
			while (isset($_POST['location_' . $count])) {
				$locations_count++;
				$shelf_locations[] = array('location' => $_POST['location_' . $count], 'count' => $_POST['count_' . $count], 'loc_index' => $locations_count);
				$sum += $_POST['count_' . $count];
				$count++;
				// VERY IMPORTANTE!!
			}

			if (empty($_POST['supply_purchased_quantity']) || $sum != $_POST['supply_purchased_quantity']) {
				$this->_set_msg('Please verify you purchase-account and shelf-locations', 'error', true);
				$this->_set_content('supply_purchases_add');
				return;
			}

			try {
				// Record purchase for supply item
				$SupplyPurchase = ORM::factory('SupplyPurchase');
				$SupplyPurchase->values($this->request->post());
				$SupplyPurchase->user_id = Auth::instance()->get_user()->id;
				$SupplyPurchase->save();

				$supplier = ORM::factory('Supplier', $SupplyPurchase->supplier_id);
				$supply_source = empty($supplier->supplier_name) ? '' : ' from ' . $supplier->supplier_name;
				$details = 'Purchase of supply-item' . $supply_source . ' :' . $SupplyPurchase->supply_description;
				$personnel_id = Auth::instance()->get_user()->id;
				Model_SupplyTransaction::record_transaction($supply_id, Model_SupplyTransaction::$addition_type_id, $SupplyPurchase->supply_purchase_id, $personnel_id, $details, $sum);

				foreach ($shelf_locations as $shelf_location) {
					$shelf = ORM::factory('SupplyShelfSupplyPurchase')->where('supply_shelf_id', '=', $shelf_location['location'])->where('supply_purchase_id', '=', $SupplyPurchase->supply_purchase_id)->where('supply_current_count', '>', 0)->find_all();
					$initial_count = 0;
					$current_count = 0;
					if ($shelf->count() == 0) {
						$sssp = ORM::factory('SupplyShelfSupplyPurchase');
					} else {
						$sssp = ORM::factory('SupplyShelfSupplyPurchase', $shelf[0]->supply_shelves_supply_purchases_id);
						$initial_count = $sssp->supply_initial_count;
						$current_count = $sssp->supply_current_count;
					}
					$sssp->supply_shelf_id = $shelf_location['location'];
					$sssp->supply_id = $supply_id;
					$sssp->supply_purchase_id = $SupplyPurchase->supply_purchase_id;
					$sssp->supply_shelf_id = $shelf_location['location'];
					$sssp->supply_initial_count = $initial_count + $shelf_location['count'];
					$sssp->supply_current_count = $current_count + $shelf_location['count'];
					$sssp->save();
				}
				// Update total count
				$this->total_quantity($supply_id);
				$this->_set_msg('Supply-Purchase successfully added', 'success', true);

				// To ensure dynamically added shelve-locations are not re-generated
				$locations_count = 1;
				$shelf_locations = array( array('location' => '', 'count' => '', 'loc_index' => $locations_count));
			} catch (Exception $e) {
				$this->_template->set('shelf_locations', $shelf_locations);
				$errors = array();
				if ($e instanceof ORM_Validation_Exception) {
					$errors = $e->errors('models');
				}
				$this->_set_msg('Sorry, some error occured while saving that supply-purchase. Please contact support', 'error', $errors);
			}
		} else {
			$locations_count++;
			$shelf_locations[] = array('location' => '', 'count' => '', 'loc_index' => $locations_count);
		}

		$this->_template->set('shelf_locations', $shelf_locations);
		$this->_template->set('locations_count', $locations_count);
		$this->_set_content('supply_purchases_add');
	}

	public function action_delete_image() {
		$supply_media_id = $this->request->param('id');
		$image = ORM::factory('Media', $supply_media_id);
		if ($image->loaded()) {
			try {
				$image->delete();
				$this->_set_msg('Image is deleted', 'success', array($supply_media_id));
			} catch (Exception $e) {
				$this->_set_msg(' Error Deleting Image', 'error', TRUE);
			}
		} else {
			$this->_set_msg(' Error Deleting Image', 'error', TRUE);
		}

	}

	// This function deletes  the item image and update the item image to default image
	public function action_delete_main_item_image() {
		$id = $this->request->param('id');
		$supply = ORM::factory('Supply', $id);
		if ($supply->loaded()) {
			$supply->avatar = "default-item.jpg";
			$supply->save();
			$this->_set_msg('Image is deleted', 'success', array($id));
		} else {
			$this->_set_msg(' Error Deleting Image', 'error', array($id));
		}
	}

	public function action_inventory_export_excel_stock_levels() {
		$stock = ORM::factory('Supply');
		$stock_items =  $stock->find_all();

		$spreadsheet = Spreadsheet::factory(array(
				'author'  => 'current user',
				'title'	  => 'Inventory Stock Levels',
				'subject' => 'Stock Levels',
				'description'  => 'Inventory Stock Levels for all Items',
				'path' =>  DOCROOT.'reports/',
				'name' => 'inventory_stock_levels'
			));
		$spreadsheet->set_active_worksheet(0);
		try {
			$stock_items_array['columns'] = array('Item code', 'Item', 'Reorder Level', 'Target Stock Level', 'Quantity in Stock');
			$stock_items_array['rows'] = array();

			foreach($stock_items as $item) {
				$stock_items_array['rows'][] = array($item->product_code, $item->supply_name,$item->reorder_level, $item->target_level, $item->total_quantity);
			}

			$spreadsheet->set_data($stock_items_array, false);
			$spreadsheet->send();
			exit;

		}Catch (Exception $e) {
			exit;
		}
	}

	public function action_supplier_export_excel_address_book() {
		$suppliers = ORM::factory('Supplier')->find_all();

		$spreadsheet = Spreadsheet::factory(array(
				'author'  => 'current user',
				'title'	  => 'Supplier Address Book',
				'subject' => 'Address Book',
				'description'  => 'List of Suppliers',
				'path' =>  DOCROOT.'reports/',
				'name' => 'efpro_supplier_addressbook'
			));
		$spreadsheet->set_active_worksheet(0);
		try {
			$stock_items_array['columns'] = array('Supplier code', 'Supplier Name', 'Contact Person', 'Order Email Address', 'Sales Email Address', 'Mobile Phone', 'Business Phone');
			$stock_items_array['rows'] = array();

			foreach($suppliers as $item) {
				$stock_items_array['rows'][] = array($item->supplier_code, $item->supplier_name,$item->supplier_contact_title .' '.$item->supplier_contact_person, $item->supplier_order_email, $item->supplier_sales_email, $item->supplier_cellphone, $item->supplier_business_phone . ' ext. ' .$item->supplier_business_phone_ext);
			}
			$spreadsheet->set_data($stock_items_array, false);
			$spreadsheet->send();
			exit;
		}Catch (Exception $e) {
			exit;
		}
	}

	public function action_supplier_export_excel_pull_sheet() {
		$pulls = Model_SupplyTransaction::get_transactions_by_type(Model_SupplyTransaction::$allocation_type_id);

		$spreadsheet = Spreadsheet::factory(array(
				'author'  => 'current user',
				'title'	  => 'Pull Sheet Report',
				'subject' => 'Inventory Pull Sheet',
				'description'  => 'Inventory Pull Sheet Report',
				'path' =>  DOCROOT.'reports/',
				'name' => 'efpro_inventory_pull_sheet'
			));
		$spreadsheet->set_active_worksheet(0);
		try {
			$reorder_items_array['columns'] = array('Pull ID', 'Pull Date', 'Allocating User', 'Allocation Details', 'Inventory Amount');
			$reorder_items_array['rows'] = array();

			foreach($pulls as $item) {
				$reorder_items_array['rows'][] = array(
						$item['id'],
						$item['time'],
						$item['personnel_name'],
						$item['details'],
						$item['amount']
					);
			}

			$spreadsheet->set_data($reorder_items_array, false);
			$spreadsheet->send();
			exit;
		}Catch (Exception $e) {
			exit;
		}

	}

	public function action_inventory_export_excel_stock_by_location() {
		$shelfs = ORM::factory('SupplyShelf')->find_all();
		$id = $this->request->param('id');
		$location = ORM::factory('SupplyLocation', $id);

		$spreadsheet = Spreadsheet::factory(array(
				'author'  => 'Current User',
				'title'	  => 'Stock by Location',
				'subject' => 'Stock by Location',
				'description'  => 'Stock by Location',
				'path' =>  DOCROOT.'reports/',
				'name' => 'stock-by-location'
			));
		$spreadsheet->set_active_worksheet(0);

		try {
			$category_items_array['columns'] = array('Shelf Name', 'Item', 'Reorder Level', 'Target Stock Level', 'Quantity in Stock');
			$category_items_array['rows'] = array();

				foreach ($location->get_supply_shelves() as $category) {

						foreach($category->get_supply_items() as $item) {
							$category_items_array['rows'][] = array($item['supply_shelf_name'], $item['supply_name'], $item['reorder_level'], $item['target_level'], $item['shelf_qty']);
							;
						}

				}
				$category_items_array['rows'][] = array('', '', '', '', $location->supply_location_name .' Subtotal: ' . $location->get_supplies_subtotal());

				$spreadsheet->set_data($category_items_array, false);
				$spreadsheet->send();
				exit;
		}Catch (Exception $e) {
			echo "Error";exit;
		}
	}

	public function action_inventory_export_excel_stock_by_category() {
		$categories = ORM::factory('SupplyType')->find_all();

		$spreadsheet = Spreadsheet::factory(array(
				'author'  => 'current user',
				'title'	  => 'Stock by Category',
				'subject' => 'Stock by Category',
				'description'  => 'Stock by Category',
				'path' =>  DOCROOT.'reports/',
				'name' => 'Stock by Category'
			));
		$spreadsheet->set_active_worksheet(0);

		try {
			$category_items_array['columns'] = array('Category', 'Item', 'Reorder Amount', 'Target Stock Level', 'Quantity in Stock');
			$category_items_array['rows'] = array();

			foreach ($categories as $category) {
				$category_items_array['rows'][] = array($category->supply_type_name, '', '', '', '');
				foreach($category->get_supply_items() as $item) {
					$category_items_array['rows'][] = array('', $item->supply_name, $item->reorder_level, $item->target_level, $item->total_quantity);
				}
				$category_items_array['rows'][] = array('', '', '', '', $category->supply_type_name .' Stock Subtotal: ' . $category->get_supplies_subtotal());
			}

			$spreadsheet->set_data($category_items_array, false);
			$spreadsheet->send();
			exit;
		}Catch (Exception $e) {
			exit;
		}
	}

	public function action_inventory_export_excel_stock_by_supplier() {
		$suppliers = ORM::factory('Supplier')->find_all();

		$spreadsheet = Spreadsheet::factory(array(
				'author'  => $this->_current_user->username ,
				'title'	  => 'Stock by Supplier',
				'subject' => 'Stock by Supplier',
				'description'  => 'Stock by Supplier',
				'path' =>  DOCROOT.'reports/',
				'name' => 'Stock by Supplier'
			));
		$spreadsheet->set_active_worksheet(0);

		try {
			$category_items_array['columns'] = array('Supplier', 'Item', 'Reorder Amount', 'Target Stock Level', 'Quantity in Stock');
			$category_items_array['rows'] = array();

			foreach ($suppliers as $category) {

				$category_items_array['rows'][] = array($category->supplier_name, '', '', '', '');
				foreach($category->get_supply_items() as $item) {
					$category_items_array['rows'][] = array('', $item->supply_name, $item->reorder_level, $item->target_level, $item->total_quantity);
				}
				$category_items_array['rows'][] = array('', '', '', '', $category->supplier_name .' Stock Subtotal: ' . $category->get_supplies_subtotal());
			}

			$spreadsheet->set_data($category_items_array, false);
			$spreadsheet->send();
			exit;
		}Catch (Exception $e) {
			exit;
		}
	}


	public function action_supplier_export_excel_inventory_reorder() {
		$suppliers_reorder = Model_Supply::get_inventory_to_reorder();

		$spreadsheet = Spreadsheet::factory(array(
				'author'  => 'current user',
				'title'	  => 'Supplier Inventory Reorder',
				'subject' => 'Inventory Reorder',
				'description'  => 'Supplier Inventory Reorder Report',
				'path' =>  DOCROOT.'reports/',
				'name' => 'efpro_inventory_reorder'
			));
		$spreadsheet->set_active_worksheet(0);
		try {
			$reorder_items_array['columns'] = array('Item', 'Reorder Level', 'Current Stock', 'Target Stock', 'Reorder Amount', 'Suppliers');
			$reorder_items_array['rows'] = array();

			foreach($suppliers_reorder as $item) {
				$reorder_items_array['rows'][] = array(
						$item['supply_name'],
						$item['reorder_level'],
						$item['total_quantity'],
						$item['target_level'],
						$item['reorder_amount'],
						$item['supplier_name']
					);
			}

			$spreadsheet->set_data($reorder_items_array, false);
			$spreadsheet->send();
			exit;
		}Catch (Exception $e) {
			exit;
		}
	}

	public function action_inventory_stock_levels() {
		$stock = ORM::factory('Supply');
		$this->_template->set('stock', $stock->find_all());
		$this->_template->set('page_title', 'Inventory Stock Level');
		$this->_set_content('reports/inventory-stock-levels');
	}

	public function action_inventory_stock_by_category() {
		$categories = ORM::factory('SupplyType')->find_all();
		$this->_template->set('categories', $categories);
		$this->_template->set('page_title', 'Inventory Stock by Category');
		$this->_set_content('reports/inventory-stock-by-categories');
	}

	public function action_inventory_stock_by_supplier() {
		$supplier = ORM::factory('Supplier')->find_all();
		$this->_template->set('supplier', $supplier);
		$this->_template->set('page_title', 'Inventory Stock by Supplier');
		$this->_set_content('reports/inventory-stock-by-supplier');
	}

	public function action_inventory_stock_by_location() {
		$id = $this->request->param('id');
		$location = ORM::factory('SupplyLocation', $id);
		if ($location->loaded()) {
			$this->_template->set('location', $location);
			$all_locations = ORM::factory('SupplyLocation')->find_all();
			$this->_template->set('all_locations', $all_locations);
			$this->_template->set('page_title', 'Inventory Stock by Location');
			$this->_template->set('page_info', 'location based inventory stock level report ');
			$this->_set_content('reports/inventory-stock-by-location');
		} else {
			$location = ORM::factory('SupplyLocation')->find_all();
			$this->_template->set('location', $location);
			$this->_template->set('page_title', 'Inventory Stock by Location: Choose a location');
			$this->_set_content('reports/inventory-stock-choose-location');
		}
	}

	public function action_supplier_address_book() {
		$suppliers = ORM::factory('Supplier')->find_all();
		$this->_template->set('suppliers',$suppliers);
		$this->_template->set('page_title', 'Suppliers Address Book');
		$this->_set_content('reports/supplier_address_book');
	}

	public function action_pull_sheets() {
		$pulls = Model_SupplyTransaction::get_pull_summary();
		$this->_template->set('pulls', $pulls);
		$this->_template->set('page_title', 'Pull Sheet Summary');
		$this->_set_content('reports/inventory-pulls');
	}

	public function action_inventory_reorder() {
		$inventory_to_reorder = Model_Supply::get_inventory_to_reorder();
		$this->_template->set('stock',$inventory_to_reorder);
		$this->_template->set('page_title', 'Inventory Stock to Reorder');
		$this->_set_content('reports/inventory-stock-to-reorder');
	}

	/**
	 * Function to generate barcode
	 */

	public function action_generate_barcode() {
		$barcode_url = Url::base(true).'vendor/barcode39/gen_barcode.php';
		$product_prefix = "PR";
		$id = $this->request->param('id');
		$item_details = ORM::factory('Supply', $id);
		if (!$item_details->loaded()) {
			$this->_set_msg('The URL is invalid. Please contact support', 'error', true);//echo 1;exit;
			$this->_set_content('messages_only_modal');
			return;
		}
		$item_code = $item_details->product_code;
		$barcode_path = DOCROOT.'assets/barcodes/items/'.$item_code.'.gif';
		if (!file_exists($barcode_path)) {
		//	if (is_writable($barcode_path)) {
				file_put_contents($barcode_path, file_get_contents($barcode_url.'?code='.$item_code));

		}

		$this->_template->set('barcode', URL::base(true).'assets/barcodes/items/'.$item_code.'.gif');
		$this->_template->set('item', $item_details);
		$this->_template->set('barcode_url', $barcode_url);
		$this->_template->set('product_name', $item_details->supply_name);
		$this->_template->set('item_id',$item_details->supply_id);
		if ($this->request->query('barcode_count')) {
			$barcode_count = $this->request->query('barcode_count');
		} else {
			$barcode_count = $item_details->total_quantity;
		}
		if ($barcode_count > 150) {
			$barcode_count = 150;
		}
		$export = false;
		if ($this->request->query('format')) {
			$export = true;
		}
		$this->_template->set('export_status', $export);
		$this->_template->set('barcode_count', $barcode_count);
		$this->_set_content('product_barcodes');
	}
}
