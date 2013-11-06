<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This controller contains all system-wide logic for user controllers
 *
 * PHP version 5.3
 *
 * @category  Controllers
 * @package   Efficiency_Pro
 * @author    Hezron Obuchele <hezron4k@gmail.com>
 * @author    Joseph Bosire <kashboss@gmail.com>
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.1.2
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
abstract class Controller_App extends Controller
{

	/**
	 * Number of items to display per page
	 * @var int
	 */
	protected $_items_per_page;

	/**
	 * Page number to display
	 * @var int
	 */
	protected $_page;

	/**
	 * Temporary cache for instantly set user status messages after an action
	 * that don't require a redirect hence don't need cookies
	 * @var array
	 */
	protected $_user_message = null;

	/**
	 * Template path relative to 'views' folder
	 * @var string
	 */
	protected $_template_path = '../../../templates/';

	/**
	 * Template name
	 * @var string
	 */
	protected $_template_name = 'default';

	/**
	 * Template layout
	 * @var string
	 */
	protected $_template_layout = 'layout';

	/**
	 * Page search context (filter param)
	 * @var string
	 */
	protected $_search_context = null;

	/**
	 * Request params cache for use in child controllers
	 * @var array
	 */
	protected $_request_params = array();

	/**
	 * Template object
	 * @var BeatifulView
	 */
	protected $_template = null;

	/**
	 * Component-specific variables to be passed to the template
	 * @var array
	 */
	protected $_template_component_vars = array();

	/**
	 * Controls access for the whole controller based on roles using default Auth module,
	 * if not set to false we will only allow user with ALL roles specified
	 *
	 * Can be set to a string or an array, for example array('login', 'admin') or 'login'
	 *
	 * @var array | string | boolean
	 */
	protected $role_required = false;

	/**
	 * Deprecated alias of $role_required, maintained for backwards compatibility
	 * @var array | string | boolean
	 */
	protected $auth_required = false;

	/**
	 * Controls access for separate actions based on roles using default Auth module
	 *
	 *  Examples:
	 * 'adminpanel' => 'admin' will only allow users with the role admin to access action_adminpanel
	 * 'moderatorpanel' => array('login', 'moderator') will only allow users with the roles login and
	 * moderator to access action_moderatorpanel
	 *
	 * Name is plural roles because ALL roles are required
	 *
	 * @var array | string | boolean
	 */
	protected $roles_actions = false;

	/**
	 * Deprecated alias of $roles_actions, maintained for backwards compatibility
	 * @var array | string | boolean
	 */
	protected $secure_actions = false;

	/**
	 * Controls access for the whole controller based on permissions using ACL module,
	 * if not set to false we will only allow user with ANY of the permissions specified
	 *
	 * Can be set to a string or an array, for example array('login', 'admin') or 'login'
	 *
	 * @var array | string | boolean
	 */
	protected $permission_required = false;

	/**
	 * Controls access for actions based on user permissions
	 *
	 *  Examples:
	 * 'edit_users' => array('save_user', 'delete_user') will only allow users with the roles
	 * with permission edit_users to access action_save_user & action_delete_user
	 *
	 * Singular 'permission' because EITHER permission is required
	 *
	 * @var array | string | boolean
	 */
	protected $permission_actions = false;


	/**
	 * Function to log application errors to logs/component_name directory
	 *
	 * @param string  $message Message to log
	 * @param integer $level   Kohana_Log Level constant
	 *
	 *  @return	void
	 */
	protected function _log($message, $level) {
		Log::instance()->add($level, $message)->write();
		return;
		// Set log level
		/* Log levels
			LOG_EMERG   => 'EMERGENCY',
			LOG_CRIT	=> 'CRITICAL',
			LOG_ERR     => 'ERROR',
			LOG_WARNING => 'WARNING',
			LOG_NOTICE  => 'NOTICE',
			LOG_INFO    => 'INFO',
			LOG_DEBUG   => 'DEBUG'
		*/
		// Define defaults
		$caller_function = '';
		$caller_class = '';
		$caller_type = '';
		$debug_info = 'Null';
		$folder = isset($this->_component_name) ? $this->_component_name : 'core';
		// Attach debug info. to Errors and above only
		if ($level <= LOG_ERR) {
			// Add application version
			include_once DOCROOT.'version.php';
			if (class_exists('App_Version')) {
				$debug_info = 'Ver. ' . App_Version::APP_VERSION . ', ';
			}

			// Add calling function and class
			$trace = debug_backtrace(false);
			$caller_function = $trace[1]['function'];
			$caller_type = $trace[1]['type'];
			if (isset($trace[1]['class'])) {
				$caller_class = $trace[1]['class'];
			}

			$debug_info .= $caller_class . $caller_type . $caller_function . '()';
		}

		// Generate log entry
		// TODO: Save message log in json format to allow for flexibility
		if (!is_array($message)) {
			$messages[] = array(
				'time'  => date(Date::$timestamp_format),
				'level' => $level,
				'body'  => $message . ' {DebugInfo: ' . $debug_info . '}'
				);
		} else {
			$time = date(Date::$timestamp_format);
			foreach ($message as $msg) {
				$messages[] = array(
					'time'  => $time,
					'level' => $level,
					'body'  => $msg . ' {DebugInfo: ' . $debug_info . '}',
					);
			}
		}

		$file  = new Log_File(APPPATH.'logs/'.$folder);
		$file->write($messages);
	}


	/**
	 * Function to set up controller & model pagination using an ORM model
	 *
	 * @param ORM   model to paginate
	 * @param array list of columns in the model
	 * @param bool  whether or not to set template content_data property
	 * (set to false if you want to change how you call find_all() in the
	 * action instead e.g. find_all()->as_array())
	 *
	 * @return Pagination pagination object
	 */
	protected function _setup_pagination(ORM &$model, array $list_columns = array(), $set_content_data = true) {
		if (!empty($list_columns)) {
			$table_columns = $list_columns;
		} else {
			$table_columns = $model->list_columns();
		}

		$count_model = clone $model;
		// total record count of all filtered dB records (after SELECT * WHERE x)
		$count = $count_model->count_all();
		// total record count of all unfiltered dB records (after just SELECT *)
		$this->_template->set('pagination_total_unfiltered_count', $model->_total_count);
		// Setup pagination module
		$pagination = Pagination::factory(
			array(
				'total_items' => $count,
				'items_per_page' => Arr::get($this->_request_params, 'limit'),
				)
		);
		$order_by = Arr::get($this->_request_params, 'order_by');
		if ($order_by && array_key_exists($order_by, $table_columns)) {
			$model->order_by($order_by, Arr::get($this->_request_params, 'sort'));
		}

		$model->limit(Arr::get($this->_request_params, 'limit'));
		$model->offset($pagination->offset);

		if ($set_content_data) {
			$this->_template->set('content_data', $model->find_all());
		}

		// Generate list numbering for where handlebars @index helper is unavailable
		$this->_numbering = ($pagination->items_per_page * ($this->_request_params['page'] - 1)) + 1;
		$this->_template->set('pagination_numbering', $this->_numbering);
		// Pass by reference so controller actions can manipulate the actual object
		$this->_template->bind('pagination', $pagination);
		return $pagination;
	}


	/**
	 * Function to set up controller & model pagination using an ORM model,
	 * specifically for the jQuery data-tables plugin.
	 *
	 * @param ORM   model to paginate
	 * @param array list of columns in the model
	 *
	 * @return Pagination pagination object
	 */
	protected function _setup_datatables_pagination(ORM &$model, array $list_columns = array()) {
				if (!empty($list_columns)) {
			$table_columns = $list_columns;
		} else {
			$table_columns = $model->list_columns();
		}

		$count_model = clone $model;
		$count = $count_model->count_all();
		// Setup pagination module
		$pagination = Pagination::factory(
			array(
				'total_items' => $count,
				'items_per_page' => Arr::get($this->_request_params, 'limit')
				)
		);
		$order_by = Arr::get($this->_request_params, 'order_by');
		if ($order_by && array_key_exists($order_by, $table_columns)) {
			$model->order_by($order_by, Arr::get($this->_request_params, 'sort'));
		}

		$model->limit(Arr::get($this->_request_params, 'limit'));
		$model->offset($pagination->offset);

		// Generate list numbering
		$this->_numbering = ($pagination->items_per_page * ($this->_request_params['page'] - 1)) + 1;
		//TODO: create helper that increments this value between calls
		$this->_template->set('pagination_numbering', $this->_numbering);

		return $pagination;
	}


	/**
	 * Function to load the view for the content area of the template layout
	 *
	 * @param string content file name
	 *
	 *  @return	void
	 */
	protected function _set_content($view_name) {
		$content_partial_dir = Kohana::$config->load("theme-config.component_configs.{$this->_component_name}.content_blocks_folder");
		$content_partial_dir = ($content_partial_dir) ? $content_partial_dir . '/' : '';
		$this->_template_content = $content_partial_dir . $view_name;
		$this->_template->template()->partial('content', $this->_template_content);
	}


	/**
	 * Function to generate a breadcrumb for the current url
	 *
	 * @param string	text to be displayed for the home page segment
	 * @param array   extra entries to add to the breadcrumb path
	 *
	 *  @return	void
	 */
	protected function _breadcrumbs($root_title = 'Home', $pages = array()) {
		$pages_passed = $pages;
		$uri_segments = array();
		$segments     = explode('/', Request::detect_uri());
		foreach ($segments as $key => $segment) {
			$url = URL::site(join('/', array_slice($segments, 0, ($key + 1))), true);
			// make sure urls ending with / are treated same as those without the last / as well as those ending with /index
			if (strlen($segment) && !in_array($url, $uri_segments) && substr($url, -5) != 'index') {
				$pages[] = array(
						'title' => ucfirst($segment),
						'url'   => $url
					);
				$uri_segments[] = $url;
			}
		}

		// Set first item text to home
		$pages[count($pages_passed)]['title'] = $root_title;
		// Set last item url to none
		$pages[count($pages) - 1]['url'] = '';

		if (intval($pages[count($pages) - 1]['title']) > 0) {
			// remove number
			$pages[count($pages) - 1]['title'] = 'List';// TODO: Find a way to pass the page context from controller to here
		}

		$this->_template->template()->partial('breadcrumb', 'blocks/breadcrumb');
		$this->_template->set('breadcrumb_pages', $pages);
	}


	/**
	 * Function to set the search context variable of the current page for access in handlebars
	 *
	 * @param string $context_title Text to display as context
	 *
	 * @return void
	 */
	protected function _set_search_context($context_title) {
		$this->_template->set('search_context', $context_title);
	}


	/**
	 * Function to update the last item of the breadcrumb to match a specified context
	 *
	 * @param string $context_title          Text to display as current breadcrumb context
	 * @param boolean $merge_second_last     Whether or not to merge context text with the second last part
	 * @param boolean $overwrite_second_last Whether or not to overwrite second last part with context text
	 *
	 * @return void
	 */
	protected function _set_breadcrumbs_context($context_title, $merge_second_last = false, $overwrite_second_last = false) {
		$pages = $this->_template->breadcrumb_pages;
		if ($merge_second_last) {
			if ($overwrite_second_last) {
				$context_title = $pages[count($pages) - 2]['title'] . " $context_title";
			} else {
				$context_title = $pages[count($pages) - 2]['title'] . " $context_title";
			}

			$pages[count($pages) - 2]['url'] = $pages[count($pages) - 1]['url'];
			unset($pages[count($pages) - 1]);
		}

		$pages[count($pages) - 1]['title'] = $context_title;
		$this->_template->set('breadcrumb_pages', $pages);
	}


	/**
	 * Function to delete the text for message cookie to avoid repeated messages between page loads
	 *
	 * @return void
	 */
	protected function _clean_up_msg() {
		$types = array('error', 'info', 'success', 'attention');
		foreach ($types as $type) {
			Cookie::delete('message' . $type);
		}

	}


	/**
	 * Function to get the text for the system message/notifications area/box
	 *
	 * @return array user message
	 */
	protected function _get_msg() {
		$return = $this->_user_message;
		if (!$return) {
			$types = array('error', 'info', 'success', 'attention');

			foreach ($types as $type) {
				if (Cookie::get('message' . $type, false)) {
					$return = array('type' => $type, 'message_body' => I18n::get(Cookie::get('message' . $type)));
				}
			}
		}

		$this->_user_message = null;
		return $return;
	}


	/**
	 * Function to set the text for message/notifications area/box
	 *
	 * @param $msg  string the message to display to the user
	 * @param $type string message type (can be used as a css class to style the message box color)
	 * @param $temp array optional data you want to pass for json repsonses | boolean indicating whether this message will be used in the same request without redirect
	 *
	 * @return void
	 */
	protected function _set_msg($msg, $type = 'info', $temp = false) {
		if (!$this->_is_json_request) {
			$this->_user_message = null;
			if ($temp) {
				$this->_user_message = array('type' => $type, 'message_body' => $msg);
			} else {
				Cookie::set('message' . $type, $msg);
			}

			if ($type == 'error' && is_array($temp)) {
				// automatically set errors variable
				$this->_template->set('errors', $temp);
			}
		} else {
			// Prepare json response array for response in the after hook
			$response = array(
				'msg' => array(
					'type' => $type,
					'message_body' => $msg
				)
			);
			if (is_array($temp) && $type != 'error') {
				$response['data'] = $temp;
			} elseif (is_array($temp) && $type == 'error') {
				$response['error'] = $temp;
			}

			$this->_template->set('content_data', $response);
		}

	}


	/**
	 * Function to setup common template variables such as page metadata and base urls for links
	 *
	 * @return void
	 */
	protected function _setup_page_metadata() {
		$this->_template->set('template_name', $this->_template_name);
		$template_path = URL::base(). 'templates/' . $this->_template_name . '/' . $this->_template_folder . '/';
		$this->_template->set('template_path', $template_path);
		// Change site title based on config position
		$seo_config = Kohana::$config->load('settings.seo');
		$site_name = Kohana::$config->load('settings.site_name');
		if ($seo_config['title']['site-name-pos'] == 'beginning') {
			$title_prefix = $site_name . Kohana::$config->load('settings.seo.title.prefix-separator');
		} else {
			$title_prefix = Kohana::$config->load('settings.seo.title.prefix-separator');
		}

		if ($seo_config['title']['site-name-pos'] == 'end') {
			$title_suffix = Kohana::$config->load('settings.seo.title.suffix-separator') . $site_name;
		} else {
			$title_suffix = Kohana::$config->load('settings.seo.title.prefix-separator');
		}

		$this->_template->set('site_name', $site_name);
		$this->_template->set('title_prefix', $title_prefix);
		$this->_template->set('title_suffix', $title_suffix);
		$this->_template->set('site_tagline', Kohana::$config->load('settings.site_tagline'));
		// Setup page meta
		$this->_template->set('charset', Kohana::$charset);
		$this->_template->set('base_url', URL::site(null, true));
		$this->_template->set('meta_keywords', '');
		$this->_template->set('meta_description', '');
		$this->_template->set('meta_copyright', html_entity_decode('&copy;') . date('Y') . ' ' . $site_name);
		$this->_template->set('meta_language', I18n::lang());
		// Set a base url for component level urls i.e. controllers you want to reference
		// within the same component as the current controller
		$current_route_params = array(
			'controller' => false,
			'action' => false,
			'directory' => strtolower($this->request->directory())
			);
		$component_base_url = rtrim(Route::url(Route::name(Request::$current->route()), $current_route_params), '/') .  '/';
		$this->_template->set('component_base_url', $component_base_url);
		// Add a base url for system level urls i.e actions set in App controller hence available in current controller
		$current_route_params['controller'] = $this->request->controller();
		$system_functions_url = rtrim(Route::url(Route::name(Request::$current->route()), $current_route_params), '/') .  '/';
		$this->_template->set('controller_base_url', $system_functions_url);
		$this->_template->set('system_functions_url', $system_functions_url);
		$this->_template->set('current_url', $this->request->url() . '/');
	}


	/**
	 * Function to initialize the template
	 *
	 * @return void
	 */
	protected function _initialize_template() {
		// Get template name and load template based on config
		$this->_template_name = $this->_template_name;
		if (Kohana::$config->load('settings.template')) {
			$this->_template_name = Kohana::$config->load('settings.template');
		}

		// Update template path to point to location of template files
		$share_template = Kohana::$config->load("theme-config.component_configs.{$this->_component_name}.share_template");
		$this->_template_folder = $share_template ? $share_template : $this->_component_name;

		$this->_template_path = DOCROOT . '/templates/' . $this->_template_name . '/';

		// Update session with template info for use elsewhere e.g. config files
		$this->session->set('sys_template_component', $this->_template_folder);
		$this->session->set('sys_template_name', $this->_template_name);

		// Get theme for template if multiple themes are supported
		$theme = Kohana::$config->load("theme-config.component_configs.{$this->_template_folder}.theme");
		$themes = Kohana::$config->load("theme-config.component_configs.{$this->_template_folder}.themes");
		if ($theme && is_string($theme) && is_array($themes) && array_key_exists('default', $themes)) {
			// Note: default theme is compulsory
			// pull theme from url
			if ($this->request->query('theme')) {
				$this->session->set('theme', $this->request->query('theme'));
			}

			$this->_template_theme_name = $this->session->get('theme', $theme);
			if (!array_key_exists($this->_template_theme_name, $themes)) {
				$this->_template_theme_name = $theme;
			}

			$this->_template_theme_path = $themes[$this->_template_theme_name];
		}

		// Create template
		$view_class = $this->_template_layout;
		$view_model_class = 'View_' . ucfirst($this->_component_name) . 'Layout';
		Template_Handlebars::$dir = $this->_template_folder . DIRECTORY_SEPARATOR . 'tpl';
		$handlebars_template = new Template_Handlebars($view_class);

		$this->_template = new View($handlebars_template, new $view_model_class);
		$this->_template->set('template_path', URL::site() . 'templates/' . $this->_template_name  . '/' . $this->_template_folder . '/');
		if ($this->_template_theme_name) {
			$this->_template->set('theme_name', $this->_template_theme_name);
		}

		$this->_template->set('logged_in', !!(Auth::instance()->get_user()));
		$this->_template->set('username', (Auth::instance()->logged_in() ? Auth::instance()->get_user()->username : ''));
		$this->_template->set('user_id', (Auth::instance()->logged_in() ? Auth::instance()->get_user()->id : ''));
		$this->_template->set('user_info', Auth::instance()->get_user());

		$json_user_info = array();
		if ($this->_template->user_info) {
			$json_user_info = $this->_get_public_user_info($this->_template->user_info);
		}

		$this->_template->set('json_user_info', json_encode($json_user_info));

		$global_push_server_url = Kohana::$config->load("settings.socket_server_url");
		$instance_push_server_url = Kohana::$config->load("pusher.socket_server_url");
		if ($instance_push_server_url) {
			$this->_template->set('socket_server_url', $instance_push_server_url);
		} else {
			$this->_template->set('socket_server_url', $global_push_server_url);
		}

		$this->_template->set('pushUid', Kohana::$config->load("pusher.pushUid"));
		$this->_template->set('assets_url', URL::base() . 'templates/default/site/');
	}


	/**
	 * Function to generate template context for js handlebars template support
	 *
	 * @return array Associative array with public user information
	 */
	protected function _get_handlebars_js_context() {
		$context = array();
		return $this->_template->viewmodel();
	}


	/**
	 * Function to get a user's public information
	 *
	 * @param ORM     $user     Instance of ORM user model
	 * @param boolean $get_dept Whether or not to fetch department info for personnel
	 *
	 * @return array Associative array with public user information
	 */
	protected function _get_public_user_info($user, $get_dept = false) {
		$user_info = $user->as_array();
		$json_user_info = array();
		$json_user_info['id'] = $user_info['id'];
		$json_user_info['username'] = $user_info['username'];
		$json_user_info['status'] = 'offline';
		if (count($user_info['personnel_info']) || count($user_info['supplier_info']) || count($user_info['client_info'])) {
			if (count($user_info['personnel_info'])) {
				$extended_info = $user_info['personnel_info'];
				$extended_info['name'] = $extended_info['personnel_name'];
				unset($extended_info['personnel_name']);
				$extended_info['avatar'] = $extended_info['personnel_avatar'];
				unset($extended_info['personnel_avatar']);
			} elseif (count($user_info['supplier_info'])) {
				$extended_info = $user_info['supplier_info'];
				$extended_info['name'] = $extended_info['supplier_name'];
				unset($extended_info['supplier_name']);
				$extended_info['avatar'] = $extended_info['supplier_avatar'];
				unset($extended_info['supplier_avatar']);
			} elseif (count($user_info['client_info'])) {
				$extended_info = $user_info['client_info'];
				$extended_info['name'] = $extended_info['client_name'];
				unset($extended_info['client_name']);
				$extended_info['avatar'] = $extended_info['client_avatar'];
				unset($extended_info['client_avatar']);
			}

			$json_user_info['name'] = $extended_info['name'];
			$json_user_info['avatar'] = $extended_info['avatar'];
			if (!$json_user_info['avatar']) {
				$json_user_info['avatar'] = URL::base().'assets/avatars/default.png';
			} else {
				$json_user_info['avatar'] = URL::base().'assets/avatars/' . $json_user_info['avatar'];
			}
		}

		if (count($user_info['personnel_info']) && $get_dept) {
			$roles = $user->roles->where('name', '!= ', 'login')->find_all();
			if (count($roles)) {
				// TODO: Change this to determine main role if user has multiple roles
				$json_user_info['dept'] = $roles[0]->name;
			}
		} else {
			$json_user_info['dept'] = '';
		}

		return $json_user_info;
	}


	/**
	 * Function to setup page resources i.e. css and js assets
	 *
	 * @return void
	 */
	protected function _setup_page_assets() {
		// Set relative path to the theme css
		if (isset($this->_template_theme_path) &&
			!Kohana::$config->load("theme-config.component_configs.{$this->_template_folder}.theme_in_main_css")) {
			$theme_css = '';
			if (is_array($this->_template_theme_path) && count($this->_template_theme_path)) {
				foreach ($this->_template_theme_path as $path) {
					$theme_css .= HTML::style('templates/' . $this->_template_name . '/' . $this->_template_folder . '/' . $path);
				}
			} elseif (is_string($this->_template_theme_path) && $this->_template_theme_path) {
				$theme_css = HTML::style('templates/' . $this->_template_name . '/' . $this->_template_folder . '/' . $this->_template_theme_path);
			}
			$this->_template->set('theme_css', $theme_css);
		}

		// Set global component resources
		$css_asset_path = 'theme-config.assets.' . $this->_component_name . '.' . $this->_component_name;
		$css_asset_key = $css_asset_path . '.groups.globalcss';
		if (Kohana::$config->load($css_asset_key)) {
			$css_group = Kohana::$config->load($css_asset_path);
			$temp_template_path = 'templates/'  . $this->_template_name . '/';
			$temp_template_path .= $this->_template_folder ? $this->_template_folder : $this->_component_name;
			$temp_template_path .= '/';
			if (isset($this->_template_theme_path) &&
			Kohana::$config->load("theme-config.component_configs.{$this->_template_folder}.theme_in_main_css")) {
				// prepend theme css to global css array
				array_unshift(
					$css_group['groups']['globalcss'],
					array('CSS', $temp_template_path . $this->_template_theme_path, array(
							'media' => 'screen',
							)
						)
				);
			}

			$global_css = new Asset_Cache(new Asset_Group('globalcss', $css_group));
			$this->_template->set('globalstyles', $global_css);
		}

		$js_asset_path = 'theme-config.assets.' . $this->_component_name . '.' . $this->_component_name;
		$js_asset_key = $js_asset_path . '.groups.globaljs';
		if (Kohana::$config->load($js_asset_key)) {
			$global_js = new Asset_Cache(new Asset_Group('globaljs', Kohana::$config->load($js_asset_path)));
			$this->_template->set('globalscripts', $global_js);
		}

		$js_asset_path = 'theme-config.assets.' . $this->_component_name . '.' . $this->_component_name;
		$js_asset_key = $js_asset_path . '.groups.headglobaljs';
		if (Kohana::$config->load($js_asset_key)) {
			$headglobal_js = new Asset_Cache(new Asset_Group('headglobaljs', Kohana::$config->load($js_asset_path)));
			$this->_template->set('headglobalscripts', $headglobal_js);
		}

		//Set page-specific resources
		$css_asset_path = 'theme-config.assets.' . $this->_component_name . '.' . strtolower($this->request->controller());
		$css_asset_key = $css_asset_path . '.groups.' . strtolower($this->request->action()) . 'css';
		if (Kohana::$config->load($css_asset_key)) {
			$page_css = new Asset_Cache(new Asset_Group(strtolower($this->request->action()) . 'css', Kohana::$config->load($css_asset_path)));
			$this->_template->set('pagestyles', $page_css);
		}

		$js_asset_path = 'theme-config.assets.' . $this->_component_name . '.' . strtolower($this->request->controller());
		$js_asset_key = $js_asset_path . '.groups.' . strtolower($this->request->action()) . 'js';
		if (Kohana::$config->load($js_asset_key)) {
			$page_js = new Asset_Cache(new Asset_Group(strtolower($this->request->action()) . 'js', Kohana::$config->load($js_asset_path)));
			$this->_template->set('pagescripts', $page_js);
		}

	}


	/**
	 * Function to setup helpers based on template engine
	 *
	 * @return void
	 */
	protected function _setup_template_helpers() {
		// Setup handlebars helpers if template type is handlebars
		if ($this->_template->template() instanceof Template_Handlebars) {
			$controller = $this;
			$template_path = '../../../../' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $this->_template_name. DIRECTORY_SEPARATOR . $this->_component_name . DIRECTORY_SEPARATOR . 'tpl'. DIRECTORY_SEPARATOR;
			$template_url = 'templates/' . $this->_template_name. '/' . $this->_component_name . '/';
			Template_Handlebars::$helpers = array(
//				'profile_stats' => function ($template, $context, $args, $source) {
//					if (Kohana::$environment !== Kohana::PRODUCTION) {
//						return '<div class = "profiler">' . View::factory('profiler/stats') . '</div>';
//					}
//
//					return '';
//				},
				'i18n' => function ($template, $context, $args, $source) {
					$tmp = $context->get($args);
					// get raw string
					if (!$tmp && strpos($args, '"') === 0 && strpos($args, '"', 1) === strlen($args) - 1) {
						$tmp = trim($args, '"');
					}

					return I18n::get($tmp);
				},
				'php_json' => function ($template, $context, $args, $source) {
					$tmp = $context->get($args);
					$school = $tmp;
					return $school;
				},
				'percentage' => function ($template, $context, $args, $source) {
					$buffer = '';
					$args = explode(' ', $args);
					$args[0] = $context->get($args[0]);
					$args[1] = $context->get($args[1]);
					if (count($args) == 2 && $args[1]) {
						$buffer = ($args[0] / $args[1]) * 100;
					}

					return $buffer;
				},
				'wrap_handlebars_var' => function ($template, $context, $args, $source) {
					return '{{'.$args.'}}';
				},
				'format_date' => function ($template, $context, $args, $source) use($template_path) {
					// usage: {{#format_date var format}} {{#format_date created "d M"}}
					$args = explode(' ', $args);
					$date = $context->get($args[0]);
					unset($args[0]);
					$format = 'Y-m-d H:i:s';
					if (count($args) > 0) {
						$format = trim(implode(' ', $args), '"');
					}

					if (is_numeric($date)) {
						$date = date($format, $date);
					}

					return Date::local_time($date, $format);
				},
				'pretty_date_format' => function ($template, $context, $args, $source) use($template_path) {
					// usage: {{#format_date var format}} {{#format_date created "d M"}}
					$args = explode(' ', $args);
					$date = $context->get($args[0]);
					unset($args[0]);
					$format = 'Y-m-d';
					if (count($args) > 0) {
						$format = trim(implode(' ', $args), '"');
					}

					if (is_numeric($date)) {
						$date = date($format, $date);
					}

					if ($date > date($format, time())) {
						$format = "M d";
					} else {
						$format = "h:i a";
					}

					return Date::local_time($date, $format);
				},
				'ISO_format_date' => function ($template, $context, $args, $source) use($template_path) {
					// usage: {{#format_date var format timezone}} {{#format_date created "d M" "UTC"}}
					$args = explode(' ', $args);
					$date = $context->get($args[0]);

					unset($args[0]);
					$format = 'c';
					if (is_numeric($date)) {
						$date = date($format, $date);
					}

					return $date;
				},
				'user_can' => function ($template, $context, $args, $source) use($template_path, $controller) {
					// usage: {{#user_can permission_name}} {{#user_can INENTORY_VIEW}}
					$args = explode(' ', $args);
					foreach ($args as $permission) {
						if ($controller->_user_has_permission(trim($permission))) {
							return $template->render($context);
						} else {
							return '';
						}
					}

				},
				'menu' => function ($template, $context, $args, $source) use($template_path, $controller) {
					// usage: {{#menu name current}} {{#menu main dataset/details}}
					$args = explode(' ', $args);
					$menu_name  = $context->get($args[0]);
					$menu_path = 'config'.DIRECTORY_SEPARATOR.Kohana_Menu::CONFIG_DIR;
					if (Kohana::find_file($menu_path, $menu_name) === false) {
						$path = APPPATH.'config'.DIRECTORY_SEPARATOR.self::CONFIG_DIR.DIRECTORY_SEPARATOR.$config.EXT;
						throw new Kohana_Exception('Menu configuration file ":path" not found!', array(':path' => $path));
					}

					// filter menu based on permissions
					$menu_config = Kohana::$config->load(Kohana_Menu::CONFIG_DIR . DIRECTORY_SEPARATOR . $menu_name);
					$menu_config = $menu_config->as_array();
					$menu_items = $menu_config['items'];
					$menu_config['items'] = $controller->_filter_menu_based_on_permissions($menu_items, 'permission');

					$template = $template_path . 'menu';
					// Auto-detect view path when no view file given. TODO: Make it scan template's tpl dir first
					if ($template != null) {
						if (Kohana::find_file('views/' . Kohana_Menu::VIEWS_DIR, $template)) {
							$view_file = $template;
						} else {
							$view_file = Kohana_Menu::DEFAULT_VIEW;
						}

						$menu_config['view'] = Kohana_Menu::VIEWS_DIR . DIRECTORY_SEPARATOR . $view_file;
					} elseif (Arr::get($menu_config, 'view') === null) {
						if (Kohana::find_file('views/' . Kohana_Menu::VIEWS_DIR, $config_file)) {
							$view_file = $config_file;
						} else {
							$view_file = Kohana_Menu::DEFAULT_VIEW;
						}

						$menu_config['view'] = Kohana_Menu::VIEWS_DIR.DIRECTORY_SEPARATOR . $view_file;
					}

					$menu = new Menu($menu_config);
					if (count($args) > 1) {
						$menu->set_current($context->get($args[1]));
					}

					return (string) $menu;
				},
				'render_script' => function ($template, $context, $args, $source) use($template_url) {
					// usage: {{#render_script var_containing_link_to_script_with_handlebars_vars}}
					$asset_paths = $context->get($args);
					if ($asset_paths) {
						$asset_paths = str_ireplace('<script type = "text/javascript" src = "' . URL::site(), '', $asset_paths);
						$asset_paths = str_ireplace('"></script>', '\n', $asset_paths);
						$asset_paths = explode('\n', $asset_paths);
						// there's always a blank string returned as the last element, remove it TODO: fix it
						unset($asset_paths[count($asset_paths) - 1]);
					} else {
						$asset_paths = array($template_url . $args);
					}

					$buffer = "";
					if (count($asset_paths)) {
						foreach ($asset_paths as $path) {
							$asset_path = DOCROOT . trim(implode(DIRECTORY_SEPARATOR, explode('/', $path)));
							if (file_exists($asset_path) && !is_dir($asset_path)) {
								$asset_content = file_get_contents($asset_path);
								$blacklist = array();
								$force_lower = false;
								// generate inline script tag
								$preg_callback = function($matches) use ($context, $force_lower, $blacklist) {
									$key = $force_lower ? strtolower($matches[1]) : $matches[1];
									$val = $context->get($key);
									return ($val && !in_array($key, $blacklist)) ? $val : '';
								};
								$buffer .= '<script type = "text/javascript">';
								$buffer .= preg_replace_callback('/\\{\\{([^{}]+)\}\\}/', $preg_callback, $asset_content);
								$buffer .= '</script>';
							}
						}
					}

					return $buffer;
				},
				'greater_than' => function ($template, $context, $args, $source) {
					$args = explode(' ', $args);
					$buffer = '';
					$param1 = $context->get($args[0]);
					if (!$param1 && is_numeric($args[0])) {
						$param1 = intval($args[0]);
					}

					$param2 = $context->get($args[1]);
					if (!$param2 && is_numeric($args[1])) {
						$param2 = intval($args[1]);
					}

					if ($param1 && $param2 && count($args) == 2) {
						if ($param1 > $param2) {
							$template->setStopToken('else');
							$buffer = $template->render($context);
							$template->setStopToken(false);
							$template->discard($context);
						} else {
							$template->setStopToken('else');
							$template->discard($context);
							$template->setStopToken(false);
							$buffer = $template->render($context);
						}
					}

					return $buffer;
				},
				'equals_to' => function ($template, $context, $args, $source) {
					$args = explode(' ', $args);
					$buffer = '';
					$param1 = $context->get($args[0]);
					if (!$param1 && is_numeric($args[0])) {
						$param1 = intval($args[0]);
					}

					$param2 = $context->get($args[1]);
					if (!$param2 && is_numeric($args[1])) {
						$param2 = intval($args[1]);
					}

					if ($param1 && $param2 && count($args) == 2) {
						if ($param1 == $param2) {
							$template->setStopToken('else');
							$buffer = $template->render($context);
							$template->setStopToken(false);
							$template->discard($context);
						} else {
							$template->setStopToken('else');
							$template->discard($context);
							$template->setStopToken(false);
							$buffer = $template->render($context);
						}
					}

					return $buffer;
				},
				'get_first_error' => function ($template, $context, $args, $source) {
					// usage: {{get_first_error errors_array field_name}}
					$args = explode(' ', $args);
					$buffer = '';
					$param1 = $context->get($args[0]); // errors array has to be a var in the context
					// "field" can be either a string or a variable
					if (count($args) == 2 && substr($args[1], 0, 1) == '"' && substr($args[1], -1, 1) == '"') {
						$param2 = substr($args[1], 1, strlen($args[1]) - 2);
					} elseif (count($args) == 2 && $context->get($args[1])) {
						$param2 = $context->get($args[1]);
					}

					if (count($args) == 2 && $param1 && $param2 && is_array($param1)
						&& array_key_exists($param2, $param1)) {
						if (is_array($param1[$param2])) {
							$buffer = array_shift($param1[$param2]);
						} else {
							$buffer = $param1[$param2];
						}
					}

					return $buffer;
				},
				'key_in_array' => function ($template, $context, $args, $source) {
					$args = explode(' ', $args);
					$buffer = '';
					$param1 = $context->get($args[0]); // errors array has to be a var in the context
					// "field" can be either a string or a variable
					if (count($args) == 2 && substr($args[1], 0, 1) == '"' && substr($args[1], -1, 1) == '"') {
						$param2 = substr($args[1], 1, strlen($args[1]) - 2);
					} elseif (count($args) == 2 && $context->get($args[1])) {
						$param2 = $context->get($args[1]);
					}

					if (count($args) == 2 && $param1 && $param2 && is_array($param1)) {
						if (array_key_exists($param2, $param1)) {
								$template->setStopToken('else');
								$buffer = $template->render($context);
								$template->setStopToken(false);
								$template->discard($context);
						} else {
							$template->setStopToken('else');
							$template->discard($context);
							$template->setStopToken(false);
							$buffer = $template->render($context);
						}
					}

					return $buffer;
				},
				'iter' => function ($template, $context, $args, $source) {
					$args = explode(' ', $args);
					$tmp = $context->get($args[0]);
					$buffer = '';
					if (is_array($tmp) || $tmp instanceof Traversable) {
						$ch = count($args) > 1 ? $args[1] : '1';
						$next_ch = $ch;
						foreach ($tmp as $var) {
							if (is_array($var)) {
								$var['@index'] = $next_ch;
							} elseif (is_object($var)) {
								$var->{'@index'} = $next_ch;
							}

							$context->push($var);
							$buffer .= $template->render($context);
							$context->pop();

							$next_ch = ++$ch;
							if (!is_numeric($next_ch) && strlen($next_ch) > 1) {
								// if you go beyond z or Z reset to a or A
								$next_ch = $next_ch[0];
							}
						}
					}

					return $buffer;
				},
				'loop_to_limit' => function ($template, $context, $args, $source) {
					$tmp = $args;
					if (!is_numeric($tmp)) {
						$tmp = $context->get($tmp);
					}

					$buffer = '';
					for ($i = 0; $i < $tmp; $i++) {
						$buffer .= $template->render($context);
					}

					return $buffer;

				},
				'increment' => function ($template, $context, $args, $source) {
					// usage: {{#menu name current}} {{#menu main dataset/details}}
					$tmp = $context->get($args);
					$tmp++;
					$context->set($args, $tmp);
					return $tmp;
				},
				'if_toggle_is_true' => function ($template, $context, $args, $source) {
					// usage: {{#toggle true}}, or {{#if toggle}}
					if (!isset($GLOBALS['toggle_value'])) {
						$GLOBALS['toggle_value'] = true;
					}

					if ($GLOBALS['toggle_value']) {
						return $template->render($context);
					}

					return '';
				},
				'set_toggle_value_false' => function ($template, $context, $args, $source) {
					// usage: {{#toggle true}}, or {{#if toggle}}
					$GLOBALS['toggle_value'] = false;
				},
				'set_toggle_value_true' => function ($template, $context, $args, $source) {
					// usage: {{#toggle true}}, or {{#if toggle}}
					$GLOBALS['toggle_value'] = true;
				},
			);
		}

	}


	/**
	 * Function to setup request variables
	 *
	 * @return void
	 */
	protected function _setup_request_params() {
		// datatables ajax request
		if ($this->request->query('sEcho')) {
			// get list of displayed columns, NB: This doesn't mean the client
			// decides what columns are returned it should know these beforehand
			// TODO: Try implementing model_to_api here directly to abstract actions from this
			$columns = array();
			$search_params = array('global' => $this->request->query('sSearch'));
			foreach ($this->request->query() as $key => $value) {
				if (strpos($key, 'mDataProp_') === 0) {
					$col_index = intval(substr($key, 10));
					$columns[$col_index] = array(
						'name' => $value,
						'sortable' => ($this->request->query('bSortable_' . $col_index) == "true"),
					);
				} elseif (strpos($key, 'sSearch_') === 0) {
					// custom search params
					$search_params[substr($key, 8)] = $value;
				}
			}

			// global search param/filter
			$this->_request_params['search'] = $this->request->query('sSearch');
			// all search filters/params with their values (use this instead if
			// you added custom filtering fields to your table)
			$this->_request_params['search_params'] = $search_params;
			// pagination data
			$this->_request_params['limit']  = $this->request->query('iDisplayLength');
			$this->_request_params['offset'] = $this->request->query('iDisplayStart');
			// useless here but has to be set
			$this->_request_params['page']   = intval($this->request->query('page'));
			// setup sorting
			if ($columns[$this->request->query('iSortCol_0')]['sortable']) {
				$this->_request_params['order_by'] = $columns[$this->request->query('iSortCol_0')]['name'];
			}

			$this->_request_params['sort'] = $this->request->query('sSortDir_0');
		} else {
			// global search value if search_field sin't specified
			$this->_request_params['search']       = $this->request->query('search');
			$this->_request_params['search_field'] = $this->request->query('field');
			$this->_request_params['limit']        = $this->request->query('limit');
			$this->_request_params['offset']       = $this->request->query('offset');
			$this->_request_params['order_by']     = $this->request->query('orderby');
			$this->_request_params['sort']         = $this->request->query('sort');
			$this->_request_params['page']         = intval($this->request->query('page'));
		}

		// Set defaults for necessary params if not passed
		if (!$this->_request_params['limit']) {
			$this->_request_params['limit'] = Kohana::$config->load('pagination.default.items_per_page');
		}

		if (!$this->_request_params['offset']) {
			$this->_request_params['offset'] = 0;
		}

		if (strtolower($this->_request_params['sort']) == 'desc') {
			$this->_request_params['sort'] = 'desc';
		} else { // Value was passed but wasn't desc/wasn't passed at all
			$this->_request_params['sort'] = 'asc';
		}

		if ($this->_request_params['page'] == 0) {
			$this->_request_params['page'] = 1;
		}

		// Add other request params
		$request_data = array();
		if ($this->request->method() == HTTP_Request::GET) {
			$request_data = $this->request->query();
		}

		// Add PUT/POST type params
		if ($this->request->method() == HTTP_Request::POST
			|| ($this->request->method() == HTTP_Request::PUT && $this->request->param('id'))) {
			parse_str($this->request->body(), $request_data);
		}

		$this->_request_params = array_merge($request_data, $this->_request_params);
	}


	/**
	 * Function to check if user has certain permission
	 *
	 * @param string $permission name of the permission
	 *
	 * @return boolean true if they have it, false otherwise
	 */
	public function _user_has_permission($permission) {
		$permission_model = ORM::factory('Permission')->where('name', ' = ', $permission)->find();
		$current_user = Auth::instance()->get_user();

		if ($permission_model->loaded() && $current_user && $current_user->can($permission_model)) {
			return true;
		} elseif (substr($permission, -4) == 'VIEW' && $permission_model->loaded()
			&& $current_user && !$current_user->can($permission_model)) {
			// check if user has edit permission but check was for view
			$resource         = substr($permission, 0, strpos($permission, 'VIEW'));
			$permission_model = ORM::factory('Permission')->where('name', ' = ', $resource . 'EDIT')->find();
			if ($permission_model->loaded() && $current_user && $current_user->can($permission_model)) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Function to get menu config file name (without extension) for current user
	 *
	 * @param string $default default menu config in case menu config file not found
	 *
	 * @return void
	 */
	protected function _get_user_menu_config($default = 'default') {
		$menu_name = $default;
		if ($this->_current_user) {
			$roles = $this->_current_user->roles->where('name', '!= ', 'login')->find_all();
			if (count($roles)) {
				$main_role = $roles[0];// Change this to determine main role if user has multiple roles
				$menu_name = strtolower($main_role->name);
				if (!Kohana::find_file('config', 'menu/' . $menu_name)) {
					$menu_name = $default;
				}
			}
		}

		return $menu_name;
	}


	/**
	 * Function to perform recusrive array filter
	 *
	 * @param array    $input    array to filter
	 * @param function $callback optional callback function used to filter
	 *
	 * @return array filtered array
	 */
	public function array_filter_recursive($input, $callback = null) {
		foreach ($input as &$value) {
			if (is_array($value)) {
				$value = $this->array_filter_recursive($value, $callback);
			}
		}

		return array_filter($input, $callback);
	}


	/**
	 * Function to filter the a menu config file based on the curent user's permissions
	 *
	 * @param array  $menu_items     array of menu items from config file
	 * @param string $permission_key item key containing the permission required
	 *
	 * @return array filtered menu items that user has permission to view based on permission defined in $permission_key
	 */
	public function _filter_menu_based_on_permissions($menu_items, $permission_key) {
		$controller = $this;
		return $this->array_filter_recursive(
			$menu_items,
			function($item) use ($permission_key, $controller) {
				if (is_array($item) && array_key_exists('items', $item)) {
					$item['items'] = $controller->_filter_menu_based_on_permissions($item['items'], $permission_key);
					$no_url = Request::current()->url() . '#';
					if (empty($item['items']) && $item['url'] === $no_url) {
						return false;
					}
				}

				return ( !isset($item[$permission_key])
					|| (is_string($item[$permission_key]) && $controller->_user_has_permission($item[$permission_key])));
			}
		);
	}


	/**
	 * Function to determine if user has permission to access the current controller
	 *
	 * @return boolean
	 */
	private function _user_authorized_to_access_controller() {
		$this->permission_required = (array) $this->permission_required;
		foreach ($this->permission_required as $permission) {
			if ($this->_user_has_permission($permission)) {
				return true;
			} else {
				// permission doesn't exist
				$this->_log('Required permission does not exist: ' . $permission, Log::DEBUG);
			}
		}

		return false;
	}


	/**
	 * Function to determine if user has permission to access the current action
	 *
	 * @return boolean
	 */
	private function _user_authorized_to_access_action() {
		$action_name = Request::current()->action();
		if (is_array($this->permission_actions)) {
			foreach ($this->permission_actions as $permission => $actions) {
				if ((is_array($actions) && in_array($action_name, $actions))
					|| (is_string($actions) && $actions == $action_name)) {
					if ($this->_user_has_permission($permission)) {
						return true;
					} else {
						// permission doesn't exist
						$this->_log('Required permission does not exist: ' . $permission, Log::DEBUG);
					}
				}
			}
		}

		return false;
	}


	/**
	 * Function to determine if permission is required to access the current action
	 *
	 * @return boolean
	 */
	private function _action_requires_auth_permission() {
		$action_name = Request::current()->action();
		if (is_array($this->permission_actions) && class_exists('Permission')) {
			foreach ($this->permission_actions as $permission => $actions) {
				if ((is_array($actions) && in_array($action_name, $actions))
					|| (is_string($actions) && $actions == $action_name)) {
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * Function to setup controller ACL functionality and check if current user
	 * has been authorized to access a certain resource
	 *
	 * @return void
	 */
	protected function _setup_authorization() { // Check user auth and role
		$action_name = Request::current()->action();
		// if remember me was selected by user during a previous login, autologin the user
		Auth::instance()->auto_login();

		// cache user object for easier access
		$this->_current_user = Auth::instance()->get_user();

		/* $username = Cookie::get('user_username', null);
		$user_password = Cookie::get('user_password', null);
		if ($username !== null && $user_password !== null) {
			Auth::instance()->login($username, $user_password, true);
			$this->_log('attempted to perform an auto-login', LOG_INFO);
		} */

		if (($this->role_required !== false && Auth::instance()->logged_in($this->role_required) === false)
				// role_required is set AND (the user isn't logged in OR doesn't have ALL the roles given in role_required)
				|| ( $this->permission_required !== false && ( !$this->_current_user || !$this->_user_authorized_to_access_controller() ))
				// OR permission_required is set AND (user isn't logged in OR doesn't have ANY of the required permission)
				|| (is_array($this->roles_actions) && array_key_exists($action_name, $this->roles_actions)
					&& Auth::instance()->logged_in($this->roles_actions[$action_name]) === false)
				// OR roles_actions is set AND the user doesn't have ALL the roles given in roles_actions
				|| ($this->_action_requires_auth_permission() && !$this->_user_authorized_to_access_action())
				// OR permission_actions is set AND the user role given in permission_actions is NOT logged in
		) {
			if ($this->_current_user) {
				// user is logged in but doesn't have the relevant roles/permission
				$this->access_required();
			} else {
				// user not logged in
				$this->login_required();
			}
		}
	}


	/**
	 * Maps the keys of $source to a new array using the supplied $map
	 *
	 * @param array $source Source array to be mapped
	 * @param array $map    Keymap of keys in the source and the target keys
	 *
	 * @return array Mapped array
	 */
	protected function map_array_keys(array $source, array $map) {
		$mapping = array();
		foreach ($source as $key => $value) {
			// mapping optional
			if (array_key_exists($key, $map)) {
				// use explicit mapping key if specified
				$mapping[$map[$key]] = $value;
			} else {
				// use default key
				$mapping[$key] = $value;
			}
		}

		return $mapping;
	}


	/**
	 * Reverse maps the keys of $source to a new array using the supplied $map
	 *
	 * @param array $source Source array to be reverse mapped
	 * @param array $map    Keymap of keys in the source and the target keys
	 *
	 * @return array Mapped array
	 */
	protected function reverse_map_array_keys(array $source, array $map) {
		$mapping = array();
		$map = array_flip($map);
		foreach ($source as $key => $value) {
			// mapping optional
			if (array_key_exists($key, $map)) {
				// use explicit mapping key if specified
				$mapping[$map[$key]] = $value;
			} else {
				// use default key
				$mapping[$key] = $value;
			}
		}

		return $mapping;
	}


	/**
	 * Initialize controller
	 *
	 * @param Request  $request  request object
	 * @param Response $response response object
	 *
	 * @return void
	 */
	public function __construct(Request $request, Response $response) {
		// Assign the request to the controller
		$this->request = $request;

		// Assign a response to the controller
		$this->response = $response;

		// Create a session object for the controller
		$this->session = Session::instance();

		// Map new changes in auth to child controllers
		$this->role_required = $this->auth_required;
		$this->roles_actions = $this->secure_actions;
	}


	/**
	 * Automatically executed before the controller action. Can be used to set
	 * class properties, do authorization checks, and execute other custom code.
	 *
	 * @return void
	 */
	public function before() {
		// This codeblock is very useful in development sites:
		// What it does is get rid of invalid sessions which cause exceptions, which may happen
		// 1) when you make errors in your code.
		// 2) when the session expires!
		/*try {
			$this->session = Session::instance();
		} catch (ErrorException $e) {
			session_destroy();
		}*/

		if (Arr::get($this->request->accept_type(), 'application/json') !== null) {
			$this->_is_json_request = true;
		} else {
			$this->_is_json_request = false;
		}

		$this->_request_browser = Request::user_agent(array('browser', 'version'));

		// perform authorization checks
		$this->_setup_authorization();

		// Initialize template
		$this->_initialize_template();

		// Register helpers
		$this->_setup_template_helpers();

		/**
		 * Begin setting up template variables
		 */
		// set common template vars
		$this->_setup_page_metadata();

		// setup page resources
		$this->_setup_page_assets();

		// set component-specific template vars setup in child controllers
		foreach ($this->_template_component_vars as $key => $value) {
			$this->_template->set($key, $value);
		}

		// setup request variables
		$this->_setup_request_params();

		// setup search variable
		if ($this->request->post('search_context')) {
			$this->session->set($this->request->controller() . '_search_context', $this->request->post('search_context'));
		} elseif ($this->request->query('clear_search_context')) {
			$this->session->delete($this->request->controller() . '_search_context');
		}

		// pass session var to controller action
		$this->_search_context = $this->session->get($this->request->controller() . '_search_context');
		$this->_template->set('current_search_context', $this->_search_context);

		// setup pagination session variable
		if ($this->request->post('paginate')) {
			$this->session->set('pagination_items_per_page', $this->request->post('paginate'));
		}

		$this->_items_per_page = $this->session->get('pagination_items_per_page', $this->_request_params['limit']);
		$this->_request_params['limit'] = $this->_items_per_page;

	}


	/**
	 * URL to change the site display language
	 *
	 * Accepts $language from /id url param
	 *
	 * @return void
	 */
	public function action_change_language() {
		$lang = $this->request->param('id');
		if (!array_key_exists('i18n' . DIRECTORY_SEPARATOR . $lang . '.php', Kohana::list_files('i18n', array(APPPATH)))) {
			$lang = 'en-us';
		}

		Cookie::set('lang', $lang);
		I18n::lang($lang);
		$this->_set_msg(I18n::get("system.notifications.change_lang_success"), 'success');
		$this->redirect($this->request->referrer());
	}


	/**
	 * URL to change the site theme
	 *
	 * Accepts $theme from /id url param
	 *
	 * @return void
	 */
	public function action_change_theme() {
		$theme = $this->request->param('id');
		$this->session->set('theme', $theme);
		$this->_set_msg(I18n::get("system.notifications.change_theme_success"), 'success');
		$this->redirect($this->request->referrer());
	}


	/**
	 * Automatically executed after the controller action. Can be used to apply
	 * transformation to the request response, add extra output, and execute
	 * other custom code.
	 *
	 * @return void
	 */
	public function after() {
		$this->_template->set('user_msg', $this->_get_msg());
		if ($this->_is_json_request && isset($this->_template->content_data)) {
			$response = array();
			if ($this->request->query('sEcho') && $this->_template->pagination) {
				// convert content_data to a normal array if it isn't
				$rows = array();
				foreach ($this->_template->content_data as $row) {
					if ($row instanceof ORM) {
						$rows[] = $row->as_array();
					} else {
						$rows[] = $row;
					}
				}

				$response['sEcho'] = intval($this->request->query('sEcho'));
				// number of all records in dB, before filtering
				$response['iTotalRecords'] = $this->_template->pagination_total_unfiltered_count;
				// number of records in dB that match query, not just the currently displayed resultset
				$response['iTotalDisplayRecords'] = $this->_template->pagination->total_items;
				$response['aaData'] = $rows;
			} else {
				$response = $this->_template->content_data;
			}

			$this->response->body(json_encode($response));
		} else {
			if ($this->request->is_ajax()) {
				// non-json ajax request from jquery-mobile-like apps that returns only the content body
				$this->_template = new View(new Template_Handlebars($this->_template_content), $this->_template->viewmodel());
				$this->_template->set('ajax_load', true);
				// force browsers not to cache such requests (particularly IE)
				$this->response->headers("Cache-Control", "no-cache, no-store");
				$this->response->headers('Content-type: text/html; charset = ' . $this->_template->charset);
			} elseif ($this->request->query('format') == 'pdf') {
				$template_path = str_ireplace(url::base(), url::base(true), $this->_template->template_path);
				$page_title = $this->_template->page_title;
				$this->_template = new View(new Template_Handlebars($this->_template_content), $this->_template->viewmodel());
				include_once Kohana::find_file('vendor', "mpdf/mpdf");
				$mpdf = new mPDF('c');
				$mpdf->SetDisplayMode('fullpage');
				// $mpdf->useSubstitutions = true; // optional - just as an example
				// $mpdf->SetHeader($url.'||Page {PAGENO}');  // optional - just as an example
				// $mpdf->CSSselectMedia = 'mpdf'; // assuming you used this in the document header
				$mpdf->setBasePath(url::base(true));
				$mpdf->SetTitle($page_title);
				$mpdf->SetAuthor(Kohana::$config->load('settings.site_name'));

				$header = '
				<div class = "report-header">
				<div class = "report-title"><div class = "main-title">Efficiency Pro</div><div class = "sub-title">' . $page_title . '</div></div>
				<div class = "report-img"><img src = "reports/leaf_banner_violet.png" /></div>
				</div>
				';

				$footer = '
				<table width = "100%" style = "vertical-align: bottom; font-family: Arial; font-size: 9pt; color: #999; border-top: 1px solid #999"><tr>
				<td width = "33%"><span style = "font-weight: normal;">{DATE D M jS, Y g:i:s a}</span></td>
				<td width = "33%" align = "center" style = "font-weight: normal;">Efficiency Pro</td>
				<td width = "33%" style = "text-align: right; ">Page {PAGENO} of {nbpg}</td>
				</tr></table>
				';

				$mpdf->SetHTMLHeader($header);
				$mpdf->SetHTMLFooter($footer);

				$mpdf->SetHeader($page_title . ' || Page {PAGENO}');

				$stylesheet = file_get_contents($template_path . 'css/print.css');
				$mpdf->WriteHTML($stylesheet, 1);

				$html = '<div id = "report-body">' . $this->_template->render() . '</div>';

				$mpdf->WriteHTML($html);
				$mpdf->Output();
				exit;
			} elseif ($this->request->query('format') == 'avery_pdf') {
				$template_path = str_ireplace(url::base(), url::base(true), $this->_template->template_path);
				$page_title = $this->_template->page_title;
				$this->_template = new View(new Template_Handlebars($this->_template_content), $this->_template->viewmodel());
				include_once Kohana::find_file('vendor', "mpdf/mpdf");

				// The last parameters are all margin values in millimetres:
				// Left-margin, right-margin, top-margin, bottom-margin, header-margin, footer-margin
				$mpdf = new mPDF('', '', 0, '', 0, 0, 5, 0, 0, 0);
				$mpdf->SetDisplayMode('fullpage');
				$mpdf->setBasePath(url::base(true));
				$mpdf->SetTitle($page_title);
				$mpdf->SetAuthor(Kohana::$config->load('settings.site_name'));

				$html = $this->_template->render();

				$mpdf->WriteHTML($html);
				$mpdf->Output();
				exit;
			}

			// add required template context for js handlebars template support
			$this->_template->set('hbs_js_context', json_encode($this->_get_handlebars_js_context()));
			// Attach template to response
			$this->response->body($this->_template);
		}

		// Clean up messages & cookies
		$this->_clean_up_msg();
	}


	/**
	 * Called from before() when the user does not have the correct rights to access a controller/action.
	 *
	 * Override this in your own Controller / Controller_App if you need to handle
	 * responses differently.
	 *
	 * For example:
	 * - handle JSON requests by returning a HTTP error code and a JSON object
	 * - redirect to a different failure page from one part of the application
	 *
	 * @return void
	 */
	public function access_required() {
		throw new HTTP_Exception_403("You do not have permission to view this page.");
	}


	/**
	 * Called from before() when the user is not logged in but they should. (Useradmin module function)
	 *
	 * @return void
	 */
	public function login_required() {
		if (isset($_SERVER['PATH_INFO'])) {
			$url = URL::base(true) . substr($_SERVER['PATH_INFO'], 1);
		} else {
			$url = URL::base(true);
		}

		Cookie::set('user_redirect', $url);
		throw new HTTP_Exception_401("You do not have permissions to view this page");
	}


	/**
	 * Function to manually highlight the current menu item
	 *
	 * @param string $uri uri of the current page
	 *
	 * @return void
	 */
	protected function _set_current_page($uri) {
		$this->_template->set('currentmenuitem', $uri);
	}


	/**
	 * Function to send message to clients using push server
	 *
	 * @param string $type    Message type
	 * @param string $payload Message to send
	 * @param array  $args    Optional args
	 *
	 * @return void
	 */
	protected function _push($type, $payload, $args = array()) {
		$data = array('type' => '', 'message' => $payload);
		switch ($type) {
			case 'chat':
				// chat messages to everyone e.g announcements
				if (array_key_exists('from', $args) && array_key_exists('to', $args)) {
					$data['type'] = 'chat/'.$args['from'].'/'.$args['to'];
				} else {
					$data['type'] = 'chat';
				}

				break;
			case 'channel':
				// chat messages to everyone e.g announcements
				if (array_key_exists('id', $args)) {
					$data['type'] = 'channel-'.$args['id'];
				}

				break;
			case 'appchat':
				// chat messages to everyone e.g announcements
				if (array_key_exists('pushUid', $args) && array_key_exists('id', $args)) {
					$data['type'] = $args['pushUid'].'/channel-'.$args['id'];
				}

				break;
			default:
				// Normal broadcast notification e.g new orders
				$data['type'] = 'notification';
				if (array_key_exists('type', $args)) {
					$data['type'] .= '/'.$args['type'];
				}

				if (array_key_exists('user_id', $args)) {
					$data['type'] .= '/'.$args['user_id'];
				}

				break;
		}

		// This is our new stuff
		$context = new ZMQContext();
		$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'codehive pusher');
		$socket->connect("tcp://localhost:5555");
		$socket->send(json_encode($data));
	}


	/**
	 * Uploads images to server. It crops the image based on the width and
	 * height and also resizes the image while maintaining the aspect ratio
	 *
	 * @param string  $image     the $FILE['image'] posted
	 * @param integer $width     width of the image to resize to
	 * @param integer $height    height of the image to resize to
	 * @param boolean $directory the directory to upload the image
	 *
	 * @return boolean
	 */
	public function save_image($image, $width = 200, $height = 200, $directory = false) {
		if (!Upload::valid($image) || !Upload::not_empty($image)
			|| !Upload::type($image, array('jpg', 'jpeg', 'png', 'gif'))) {
			return false;
		}

		$file = Upload::save($image, null, $directory);
		if ($file) {
			$filename = strtolower(Text::random('alnum', 20)).'.jpg';

			Image::factory($file)
				->resize($width, $height, Image::INVERSE)
				->crop($width, $height, null, 0)
				->save($directory.$filename);

			// Delete the temporary file
			unlink($file);

			return $filename;
		}

		return false;
	}


} // End Controller_App
