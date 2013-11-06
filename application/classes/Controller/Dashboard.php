<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Controller that handles all Dashboard functionality
 *
 * PHP version 5.3
 *
 * @category  Controllers
 * @package   Efficiency_Pro
 * @author    Hezron Obuchele <hezron4k@gmail.com>
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.0.8
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
class Controller_Dashboard extends Controller_Site
{

	/**
	 * Controller role requirements
	 * @var array
	 */
	protected $auth_required = array('login');


	/**
	 * Function to display index page
	 *
	 * @return void
	 */
	public function action_index() {
		$this->_template->set('page_title', 'Dashboard');
		$this->_template->set('page_info', 'everything you need to know');
		$this->_template->set('localdev', strpos(URL::base(true), 'vps.circleksolutions.com') === false);
		$content = array();
		$this->_set_content('dashboard');
	}


}
