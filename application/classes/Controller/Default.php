<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Default Page
 *
 */
class Controller_Default extends Controller_Site {

	public function action_index()
	{
		$this->redirect("dashboard");
	}
	
	public function action_dashboard()
	{
		$this->_template->set('page_title', 'Dashboard');
		$this->_template->set('page_info', 'overview & stats');
		//$this->_template->set('content_data', $content_array);
		$this->_set_content('dashboard');
	}
	
	public function action_clients()
	{
		$this->_template->set('page_title', 'Clients');
		$this->_template->set('page_info', 'alphabetical listing of all clients');
		//$this->_template->set('content_data', $content_array);
		$this->_set_content('clients');
	}
	
	public function action_personnel()
	{
		$this->_template->set('page_title', 'Personnel');
		$this->_template->set('page_info', 'alphabetical listing of all personnel');
		//$this->_template->set('content_data', $content_array);
		$this->_set_content('personnel');
	}
	
	public function action_client_jobs()
	{
		$this->_template->set('page_title', 'Client Jobs');
		$this->_template->set('page_info', 'drag a client to the calendar/click on an empty space to create a job');
		//$this->_template->set('content_data', $content_array);
		$this->_set_content('client_jobs');
	}
	
	public function action_internal_jobs()
	{
		$this->_template->set('page_title', 'Internal Jobs');
		$this->_template->set('page_info', 'click on an empty space to create a job');//drag a common job type to the calendar/
		//$this->_template->set('content_data', $content_array);
		$this->_set_content('internal_jobs');
	}
	
	public function action_tasks()
	{
		$this->_template->set('page_title', 'Tasks');
		$this->_template->set('page_info', 'task lisitng');
		$this->_template->set('pagination', true);
		//$this->_template->set('content_data', $content_array);
		$this->_set_content('tasks');
	}
	
	public function action_equipment()
	{
		$this->_template->set('page_title', 'Equipment');
		$this->_template->set('page_info', 'alphabetical listing of all equipment');
		//$this->_template->set('content_data', $content_array);
		$this->_set_content('equipment');
	}
	
	public function action_inventory()
	{
		$this->_template->set('page_title', 'Inventory');
		$this->_template->set('page_info', 'view, manage and allocate supplies');
		$this->_template->set('pagination', true);
		//$this->_template->set('content_data', $content_array);
		$this->_set_content('inventory');
	}
	
	public function action_reports()
	{
		$this->_template->set('page_title', 'Reports');
		$this->_template->set('page_info', 'browse reports by name');
		//$this->_template->set('content_data', $content_array);
		$this->_set_content('reports');
	}

} // End Default
