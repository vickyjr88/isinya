<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Beautiful Handlebars Template
 *
 * @package     Beautiful
 * @subpackage  Beautiful View
 * @category    Template
 * @author      Hezron Obuchele
 * @copyright   Hezron Obuchele, 2013
 * @license     MIT
 */
class Template_Handlebars extends Template {
	
	/**
	 * Template directory.
	 */
	public static $dir = 'templates';

	/**
	 * Template_Handlebars works with .handlebars files
	 */
	public static $ext = 'handlebars';
	
	/**
	 * Array of helpers to use in this template e.g.
	 * 	array(
	 * 		'i18n' => function ($template, $context, $args, $source) {
	 * 			return __($context->get($args));
	 * 		}
	 *  )
	 */
	public static $helpers = array();
	
	/**
	 * Partials.
	 *
	 * @access  protected
	 */
	protected $_partials = array();

	/**
	 * The rendering method, wooo!
	 *
	 * @param   ViewModel  Data to be passed to template
	 * @return  string     Rendered template
	 */
	public function render(ViewModel $view)
	{
		$path_parts = explode(DIRECTORY_SEPARATOR, realpath($this->path()));
		unset($path_parts[count($path_parts)-1]);
		$base_dir = implode(DIRECTORY_SEPARATOR, $path_parts);
		/*$base_dirs = array($base_dir);
		$sub_dirs = scandir($base_dir);
		foreach ($sub_dirs as $key => $dir) {
			if (is_dir($base_dir . DIRECTORY_SEPARATOR . $dir) && $dir != '.' && $dir != '..')
				$base_dirs[] = $base_dir . DIRECTORY_SEPARATOR . $dir;
		}*/
		//print_r($base_dirs);exit;
		$engine =  new Beautiful_Handlebars(array(
			'partials_loader' => new Handlebars_Loader_FilesystemLoader($base_dir, array('extension' => static::$ext)),
			'escape' => function($value) {
				return HTML::chars($value);
			},
		));
		// Add the helpers
		foreach (static::$helpers as $name => $helper) {
			$engine->addHelper($name, $helper);
		}
		$template = file_get_contents($this->path());
		
		// Load partials
		foreach ($this->_partials as $name => $path) {
			//echo $name . ' => ' . $path . '<br/>';
			//if (!$this->_file_in_dirs($name, $base_dirs) && strpos($path . '.' . static::$ext, $name . '.' . static::$ext) === false){// dynamic partial
			$engine->registerPartial($name, $path);
			//}
		}
		return $engine->loadTemplate($template)->render($view);
	}
	
	/**
	 * Loads a new partial from a path. If the path is empty, the partial will
	 * be removed.
	 *
	 * @param   string  partial name
	 * @param   mixed   partial path, FALSE to remove the partial
	 * @return  Kostache
	 */
	public function partial($name, $path)
	{
		if ($path === FALSE)
		{
			unset($this->_partials[$name]);
		}
		else
		{
			$this->_partials[$name] = $path;
		}

		return $this;
	}
	
	/*private function _file_in_dirs($file, $dirs){
		foreach ($dirs as $dir) {
			//echo $file . ' => ' . $dir . '/' . $file . '.' . static::$ext . '<br/>';
			if (file_exists($dir . '/' . $file . '.' . static::$ext))
				return true;
		}
		return false;
	}*/
}