<?php
require_once 'MenuBuilder.php';

/**
 * Test case for Menu_Item
 *
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @author Ando Roots <ando@sqroot.eu>
 * @since 2.0
 * @package Kohana/Menu
 * @category Tests
 * @group kohana.menu
 * @copyright (c) 2012, Ando Roots
 */
class Menu_MenuTest extends MenuBuilder
{

	public function testAllMenuItemsAreBuilt()
	{
		$menu_config = self::_get_test_config();

		$menu_item_count = count($menu_config['items']);

		$this->assertEquals($menu_item_count, count($this->_menu->get_items()));
	}

	public function testRenderedMenuHasAllVisibleItemLinks()
	{
		$rendered_menu = $this->_menu->render();
		foreach ($this->_menu->get_items() as $item) {
			if (! $item->is_visible()) {
				continue;
			}
			$this->assertContains($item->url, $rendered_menu);
		}
	}

	public function testHiddenLinksAreNotRendered()
	{
		Menu_ACL::$is_admin = FALSE;
		$this->_menu = $this->_build_test_menu();
		$this->assertFalse(stristr($this->_menu, 'projects'));
	}

	public function testActiveLinkIsHighlighted()
	{
		$this->_menu->set_current('tasks');

		foreach ($this->_menu->get_items() as $item) {
			if ($item->url !== 'tasks') {
				continue;
			}
			$this->assertTrue(in_array($this->_menu->active_item_class, $item->classes));
		}
	}

	/**
	 * Expect the active class to be removed from the previously active link
	 */
	public function testAtMostOneItemIsActive()
	{
		$this->_menu->set_current(0);
		$this->_menu->set_current(1);

		foreach ($this->_menu->get_items() as $index => $item) {
			$is_item_active = in_array($this->_menu->active_item_class, $item->classes);
			if ($index === 1) {
				$this->assertTrue($is_item_active);
			} else {
				$this->assertFalse($is_item_active);
			}
		}
	}

	public function testMenuConfigurationHasDefaultValues()
	{
		$menu = new Menu([]);

		$default_config = Menu::get_default_config();

		foreach ($default_config as $key => $value) {
			if ($key === 'view') {
				// Todo View is auto-detected, how to test the factory method?
				continue;
			}
			$this->assertEquals($value, $menu->{$key});
		}
	}

	public function testCorrectLinkIsAutomaticallyMarkedAsActive()
	{
		// Make a dummy request to populate Request::current()
		Request::$current = Request::factory('tasks', array(), FALSE);

		$menu_config = self::_get_test_config();
		$menu_config['guess_active_item'] = TRUE;

		$menu = new Menu($menu_config);
		$menu->render();

		// Check that an item was marked as active
		$menu_items = $menu->get_items();

		foreach ($menu_items as $item) {
			if ($item->url === 'tasks') {
				$this->assertTrue(in_array('active', $item->classes));
			}
		}
	}

}