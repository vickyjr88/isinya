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
class Menu_ItemTest extends MenuBuilder
{

	public function testLabelsAreTranslated()
	{
		$this->markTestIncomplete('Think of a way to mock I18n __()');
	}

	public function testNewItemHasDefaultValues()
	{
		$menu_item = new Menu_Item([], $this->_menu);

		$default_item_config = Menu_Item::get_default_config();

		foreach ($default_item_config as $option => $default_value) {
			$this->assertEquals($default_value, $menu_item->{$option});
		}

	}

	public function testItemConfigOverridesDefaults()
	{
		$default_item_config = Menu_Item::get_default_config();
		$menu_item = new Menu_Item(['title' => 'test1', 'classes' => ['class1']], $this->_menu);

		$this->assertEquals('test1', $menu_item->title);
		$this->assertEquals(['class1'], $menu_item->classes);
		$this->assertEquals($default_item_config['visible'], $menu_item->visible);
	}

	public function testItemHasIcon()
	{
		foreach ($this->_menu->get_items() as $key => $item) {
			if (array_key_exists('icon', $this->_nav_simple['items'][$key])) {
				$this->assertContains($item->icon, (string) $item);
			}
		}
	}

	public function testIconIsIgnoredWhenEmpty()
	{
		foreach ($this->_menu->get_items() as $key => $item) {
			if (! array_key_exists('icon', $this->_nav_simple['items'][$key])) {
				$this->assertFalse(stristr((string) $item, '<i class'));
			}
		}
	}

	public function testAdditionalClassesCanBeAddedFromConfig()
	{
		$config['items'][0]['classes'] = ['testClass'];
		$menu = new Menu($config);
		$this->assertTrue(in_array('testClass', $menu->get_item(0)->classes));
	}

}