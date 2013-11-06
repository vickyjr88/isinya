<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Controller that handles management reporting
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
class Controller_Report extends Controller_Site
{

	/**
	 * Controller role requirements
	 * @var array
	 */
	protected $role_required = array('login');

	/**
	 * Controller permission requirements
	 * @var array
	 */
	protected $permission_required = array('INVENTORY_VIEW');

	/**
	 * Controller permission<->action definitions
	 * @var array
	 */
	protected $permission_actions = array(
		'INVENTORY_VIEW' => array(
			'index',
			'transactions',
			'transaction',
			'inventory_export_excel_stock_levels',
			'supplier_export_excel_inventory_reorder',
			'supplier_export_excel_address_book'
			),
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
	 * Function to display reports view
	 *
	 * @return void
	 */
	public function action_index() {
		$this->_set_content('reports_view');
	}


}
