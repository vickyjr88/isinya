<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Controller that handles all Cleint related request
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
class Controller_Clients extends Controller_Site
{
	
	protected $auth_required = array('login');
	
	protected $permission_actions = array(
		'CLIENTS_VIEW' => array('index','more','clients_export_excel_address_book'),
		'CLIENTS_EDIT' => array('edit'),
		//'CLIENTS_DELETE' => array(),
	);
	
	/**
	 * Function to display index page
	 */
	public function action_index() { 
		$this->_template->set('page_title', 'Clients');
		$this->_template->set('page_info', 'view and manage clients');
		$path = url::base().'assets/avatars';
		$this->_template->set('path', $path);
		$clients = ORM::factory('Client')->where('delete_status','=','0');
		
		// perform a model function that filters a datset
		$search_field = array('client_name', 'client_contact_person');
		$content_array = $this->group_personnel($clients->find_all());
		$this->_template->set('content_data', $content_array);
		$this->_set_search_context(I18n::get("nav.site.people.clients"));
		$this->_set_content('clients');
		
	}


	/**
	 * Function to perform an edit or add a cleints
	 * @return content
	 */
	public function action_edit(){
		$id = $this->request->param('id');
		$client = ORM::factory('Client', $id);
		
		$path = url::base().'assets/avatars';
		if ($this->request->post()) {
			//STEP1: Register User Information
			$post = $this->request->post();	

			$user = ORM::factory('User',$client->user_id);
			$user->username = $post['username'];
			$user->email = $post['client_email_address'];
			if(!$client->loaded()){
				$user->password = $post['password'];

			}
					
			//STEP2: REgister Personnel Information

			$client->values($this->request->post());

			try {
				//add user_id to personnel record
				
				//check if file was posted
				if(isset($_FILES['client_avatar'])){
					$directory =  DOCROOT.'assets/avatars/';
					$avatar =  $this->save_image($_FILES['client_avatar'],150,150,$directory);
					$client->avatar= $avatar;
				}else{
					
				}

			//	if(!$client->client_users_id){
				$user->save();	
				$client->user_id=$user->id;					
				$user->remove('roles');	
									
				//$user->add('roles', ORM::factory('Role',$this->request->post('user_type')));
				$user->add('roles', ORM::factory('Role')->where('name', '=', 'login')->find());
				$user->add('roles', ORM::factory('Role')->where('name', '=', 'client')->find());
				//$user->save();
				$client->save();
				// Update cleints
				if (!intval($id)){
	            	$this->_template->set('added_record', 1);

				}
				$id = $client->client_id;
	            $this->_set_msg('Successfully saved!', 'success', true);
				
			}catch(Exception $e){
				//echo $e->getMessage();
				//print_r($e->getTrace());
				$errors = array();
				if ($e instanceof ORM_Validation_Exception){
					$errors = $e->errors('models');

				}
			
				$this->_set_msg('Please correct the errors below.', 'error', $errors);
					
			}
		}
		// More of corective url adjustments in case JS screws this up
		$this->_template->set('path', $path);
		if (!intval($id)) {
			$this->_template->set('addform', 1);
			$this->_template->set('ajax_save_url', $this->_template->controller_base_url . 'edit');

		} else {
			$this->_template->set('ajax_save_url',$this->_template->controller_base_url . 'edit/' . $id);
		}
		$this->_template->set('content_data', $client);
		
		$roles = ORM::factory('Role')->where('name','!=','login')->find_all();
		$this->_template->set('roles', $roles);
		$this->_set_content('client_edit');
	}


	/**
	 * Function to perform a  delete  
	 * 
	 */

	public function action_delete() {
		$id = $this->request->param('id');
		if($id) {
			$client = ORM::factory('Client', $id);
			$data = array('id'=>$client->client_id);
			$user = ORM::Factory('User',$client->client_users_id);
			if ($client->loaded()){
				try {
					$client->delete_status=1;
					$client->save();
					$user->delete_status=1;
					$user->save();
					$this->_set_msg(' Client record was deleted', 'success',$data);

				} catch (Exception $e) {
					$this->_set_msg('Could not delete client because this would wipe their history!', 'error',$data);

				}
			}
			$this->redirect($this->request->referrer());
		}
		//$this->_set_content('supplier_delete');
	}


	/**
	 * Function to perform a  delete  
	 * @return group
	 */
	
	public function group_personnel($personnel) {
		$alphabets = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		$group_array = array();
		foreach ($alphabets as $key => $value) {
			$group = array();
			foreach($personnel as $client){
				if(substr(strtolower($client->client_name),0,1)==$value){
					$group[] = $client;

				}

			}
			if(count($group)>0){
				$group_array[] = array('group'=>$value,'clients'=>$group);

			}
		}		
		return $group_array;
		
	}


	/**
	 * Function to get more details about the clients 
	 * 
	 */
	
	public function action_more() {
		$id = $this->request->param('id');
		$this->_template->set('page_title', 'Clients');
		$this->_template->set('page_info', 'view and manage clients');
		$this->_set_content('clients_more');
	}
	

	/**
	 * Function to get client reports
	 * 
	 */
	public function action_clients_export_excel_address_book() {
		$clients = ORM::factory('Client')->find_all();

		$spreadsheet = Spreadsheet::factory(array(
				'author'  => 'current user',
				'title'      => 'Client Address Book',
				'subject' => 'Client Address Book',
				'description'  => 'List of Clients',
				'path' =>  DOCROOT.'reports/',
				'name' => 'efpro_client_addressbook'
			));
		$spreadsheet->set_active_worksheet(0);
		try {
			$client_items_array['columns'] = array('Client Name','Contact Person','Telephone','Email Address', 'Postal Address');
			$client_items_array['rows'] = array();

			foreach($clients as $item){
				$client_items_array['rows'][] = array($item->client_name, $item->client_contact_person,$item->client_telephone, $item->client_email_address, $item->client_postal_address);

			}

			$spreadsheet->set_data($client_items_array, false);
			$spreadsheet->send();
			//$spreadsheet->save();
			exit;

		}Catch (Exception $e){
			throw new Exception('Could not export Excel report. Try again later.' . $e);
		}
	}

	/**
	 * Function to get client address books
	 * 
	 */
	
	public function action_client_address_book() {
		$client = ORM::factory('Client')->find_all();
		$this->_template->set('client',$client);
		$this->_set_content('reports/client_address_book');
	}
	
	
	
	}