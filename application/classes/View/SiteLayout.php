<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Site component template viewmodel
 *
 * PHP version 5
 *
 * @category  ViewModels
 * @package   Efficiency_Pro
 * @author    Hezron Obuchele <hezron4k@gmail.com>
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.1.2
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
class View_SiteLayout extends ViewModel
{


	/**
	 * Gets page title property
	 *
	 * @return string
	 */
	public function page_title() {
		return 'Site Layout';
	}


	/**
	 * Gets user message property
	 *
	 * @return string
	 */
	public function message() {
		return $this->user_msg;
	}


}
