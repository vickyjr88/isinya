<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Utility class.
 * Sets up an instance of the menu for easier testing
 *
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @author Ando Roots <ando@sqroot.eu>
 * @since 2.0
 * @package Kohana/Menu
 * @category Tests
 * @group kohana.menu
 * @copyright (c) 2012, Ando Roots
 */
abstract class MenuBuilder extends Kohana_Unittest_TestCase {

	/**
	 * @var Menu
	 */
	protected $_menu;

	/**
	 * @var array Configuration of nav_simple
	 */
	protected $_nav_simple;

	public function setUp()
	{
		parent::setUp();
		$this->_nav_simple = self::_get_test_config();
		$this->_menu = $this->_build_test_menu();
	}

	/**
	 * Instantiate a test-menu
	 *
	 * @return Menu
	 */
	protected function _build_test_menu()
	{
		return new Menu($this->_nav_simple);
	}

	/**
	 * Load test menu config
	 *
	 * @param string $file_name Navigation config file name (without EXT) in test_data dir
	 * @return array
	 */
	protected static function _get_test_config($file_name = 'nav_simple')
	{
		$file_path = Kohana::find_file('tests/menu/test_data', $file_name);
		return require $file_path;
	}
}