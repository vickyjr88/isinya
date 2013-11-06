<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This controller contains all logic for the front page website
 *
 *
 * PHP version 5
 * LICENSE: Not for reuse or modification without the express 
 * written authorization from BeeBuy Investments Ltd.
 *
 * Controller_User
 * @author     Joseph Bosire
 * @package    Efficiency Pro
 * @category   Controllers
 * @version    0.0.5
 * @copyright  (c) 2013 BeeBuy Investments Ltd. - http://www.beebuy.biz
 */
class Controller_FrontPage extends Controller_Site {
	
	protected $_template_layout = "frontpage";
	/**
	 * The landing page
	 *
	 * @return void
	 * @author Joseph Bosire 
	 */	 
	public function action_index() {
		//$this->_template_layout = "frontpage";
		//$this->before();
		$this->_set_content('home');
	}
	/**
	 * The landing page
	 *
	 * @return void
	 * @author Joseph Bosire 
	 */	 
	public function action_features() {
		//$this->_template_layout = "frontpage";
		//$this->before();
		$this->_set_content('features');
	}
	/**
	 * The pricing page
	 *
	 * @return void
	 * @author Joseph Bosire 
	 */	 
	public function action_pricing() {
		//$this->_template_layout = "frontpage";
		//$this->before();
		$this->_set_content('pricing');
	}
	/**
	 * The contact page
	 *
	 * @return void
	 * @author Joseph Bosire 
	 */	 
	public function action_contact() {
		//$this->_template_layout = "frontpage";
		//$this->before();
		$this->_set_content('contact');
	}
	/**
	 * The about page
	 *
	 * @return void
	 * @author Joseph Bosire 
	 */	 
	public function action_about() {
		//$this->_template_layout = "frontpage";
		//$this->before();
		$this->_set_content('about_us');
	}
}

