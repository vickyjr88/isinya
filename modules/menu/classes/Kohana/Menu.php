<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Represents a single Menu type HTML entity
 *
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @author Ando Roots <ando@sqroot.eu>
 * @since 2.0
 * @package Kohana/Menu
 * @copyright (c) 2012, Ando Roots
 */
class Kohana_Menu
{

	/**
	 * Menu configuration is in this dir
	 *
	 * @since 2.2
	 */
	const CONFIG_DIR = 'menu';

	/**
	 * View filename to use when auto-detect fails
	 *
	 * @since 3.0
	 */
	const DEFAULT_VIEW = 'default';

	/**
	 * Menu view files reside in this dir
	 *
	 * @since 3.0
	 */
	const VIEWS_DIR = 'templates/menu';

	/**
	 * @var array Holds current menu configuration
	 */
	protected $_config;

	/**
	 * @var View Menu view object
	 */
	protected $_view;

	/**
	 * @var array An (ordered) array of Menu_Items in this menu
	 * @since 2.0
	 */
	protected $_items;

	/**
	 * @var int Reference to the currently active menu item
	 */
	protected $_active_item_index;

	/**
	 * Initialize a new menu.
	 * Use the factory method instead of the `new` keyword.
	 *
	 * @param array $config Menu configuration, overrides default values
	 * @see self::factory
	 */
	public function __construct(array $config)
	{
		$menu_items = array();
		if (array_key_exists('items', $config)) {
			$menu_items = $config['items'];

			// We don't want to save this to $this->_config
			unset($config['items']);
		}

		// Save menu config, overriding default values
		$this->_config = array_replace(self::get_default_config(), $config);

		// Load menu view (auto detected or manually specified)
		$this->_view = View::factory($this->get_view_path());

		// Transform menu items from an array to objects
		$this->_build_items($menu_items);
	}

	/**
	 * Transform menu items from an array to objects
	 *
	 * @since 3.0
	 * @param array $items An array of menu items
	 * @return \Kohana_Menu
	 */
	private function _build_items(array $items)
	{
		foreach ($items as $key => $item) {
			$this->_items[$key] = new Menu_Item($item, $this);
		}

		return $this;
	}

	/**
	 * Read menu configuration file
	 *
	 * @param string $config File name in config/menu dir
	 * @return array Menu configuration array
	 * @throws Kohana_Exception
	 */
	protected static function _get_menu_config($config)
	{
		if (Kohana::find_file('config'.DIRECTORY_SEPARATOR.self::CONFIG_DIR, $config) === FALSE) {
			throw new Kohana_Exception('Menu configuration file ":path" not found!', array(
				':path' => APPPATH.'config'.DIRECTORY_SEPARATOR.self::CONFIG_DIR.DIRECTORY_SEPARATOR.$config.EXT
			));
		}

		return Kohana::$config->load(self::CONFIG_DIR.DIRECTORY_SEPARATOR.$config)
			->as_array();
	}

	/**
	 * Instantiate a new menu
	 *
	 * @param string $config_file File name in config/menu/
	 * @throws Kohana_Exception
	 * @return Menu
	 * @since 2.0
	 */
	public static function factory($config_file = 'simple', $template = null)
	{
		// Load menu config
		$menu_config = self::_get_menu_config($config_file);

		// Auto-detect view path when no view file given
		if ($template != null){
			$view_file = Kohana::find_file('views/'.self::VIEWS_DIR, $template)
				? $template : self::DEFAULT_VIEW;
			$menu_config['view'] = self::VIEWS_DIR.DIRECTORY_SEPARATOR.$view_file;
		} elseif (Arr::get($menu_config, 'view') === NULL) {
			$view_file = Kohana::find_file('views/'.self::VIEWS_DIR, $config_file)
				? $config_file : self::DEFAULT_VIEW;
			$menu_config['view'] = self::VIEWS_DIR.DIRECTORY_SEPARATOR.$view_file;
		}

		return new Menu($menu_config);
	}

	/**
	 * Render the menu into HTML
	 *
	 * @since 2.0
	 * @return string the rendered view
	 */
	public function render()
	{
		// Try to guess the current active menu item
		if ($this->_config['guess_active_item'] && $this->_active_item_index === NULL) {
			$this->guess_active_item(Request::current());
		}

		return $this->_view
			->set('menu', $this)
			->render();
	}

	/**
	 * @since 1.0
	 * @see render()
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Get an array of Menu_Item instances
	 *
	 * @since 2.0
	 * @return array
	 */
	public function get_items()
	{
		return $this->_items;
	}

	/**
	 * Get only visible menu items
	 *
	 * @return array
	 * @since 3.0
	 */
	public function get_visible_items()
	{
		if ($this->_items === NULL) {
			return array();
		}

		$visible_items = array();

		foreach ($this->_items as $key => $item) {
			if (! $item->is_visible()) {
				continue;
			}
			$visible_items[$key] = $item;
		}

		return $visible_items;
	}

	/**
	 * Set the currently active menu item (by applying the `active_item_class` CSS class)
	 *
	 * @since 2.0
	 * @param int|string $id The ID of the menu (numerical array ID from the config file) or URL of a menu item
	 * @return Menu_Item|bool The active menu item or FALSE when item not found
	 */
	public function set_current($id = 0)
	{
		$active_item = $this->get_item($id);

		if (! $active_item) {
			return FALSE;
		}

		// Mark all menu items as inactive
		foreach ($this->_items as &$item) {
			$item->remove_class($this->_config['active_item_class']);
		}

		$active_item->add_class($this->_config['active_item_class']);
		return $active_item;
	}


	/**
	 * Access menu config properties
	 *
	 * @since 2.0
	 * @param string $name Name of a menu config property
	 * @return mixed
	 */
	public function __get($name)
	{
		if (array_key_exists($name, $this->_config)) {
			return $this->_config[$name];
		}
		return NULL;
	}

	/**
	 * Get an instance of Menu_Item based on its ID
	 *
	 * @since 2.1.1
	 * @param int|string $id Item ID or URL
	 * @return bool|Menu_Item
	 */
	public function get_item($id)
	{
		// Menu empty!
		if (count($this->_items) === 0) {
			return FALSE;
		}

		if (array_key_exists($id, $this->_items)) { // By ID
			return $this->_items[$id];
		} else { // By URL
			foreach ($this->_items as &$menu_item) {
				if ($menu_item->url === $id) {
					return $menu_item;
				}
			}
		}
		return FALSE;
	}

	/**
	 * @return array Default configuration for the menu
	 * @since 3.0
	 */
	public static function get_default_config()
	{
		return array(
			'active_item_class' => 'active',
			'view'              => FALSE,
			'guess_active_item' => FALSE,
		);
	}

	/**
	 * Get the path of the view file
	 *
	 * @since 3.0
	 * @return string
	 */
	public function get_view_path()
	{
		return $this->_config['view'] ? $this->_config['view'] : self::VIEWS_DIR.DIRECTORY_SEPARATOR.self::DEFAULT_VIEW;
	}

	/**
	 * Set the active link based on the current request
	 *
	 * @since 3.0
	 * @param Request $request
	 * @return bool|\Menu_Item
	 */
	public function guess_active_item(Request $request)
	{
		return $this->set_current($request->uri());
	}
}