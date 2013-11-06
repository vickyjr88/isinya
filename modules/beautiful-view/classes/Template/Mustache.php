<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Beautiful Mustache Template
 *
 * @package     Beautiful
 * @subpackage  Beautiful View
 * @category    Template
 * @author      Luke Morton, Hezron Obuchele
 * @copyright   Luke Morton (2011), Hezron Obuchele (2013)
 * @license     MIT
 */
class Template_Mustache extends Template {
	
	/**
	 * Template directory.
	 */
	public static $dir = 'templates';

	/**
	 * Template_Mustache works with .mustache files
	 */
	public static $ext = 'mustache';
	
	/**
	 * Array of helpers to use in this template e.g.
	 * 	array(
	 * 		'i18n' => function($text, $context) {
	 * 			return __($text);
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
		$engine =  new Beautiful_Mustache(array(
			'cache' => APPPATH . 'cache/mustache',
			'partials' => $this->_partials,
			'helpers' => static::$helpers,
			'escape' => function($value) {
				return HTML::chars($value);
			},
		));
		$template = file_get_contents($this->path());
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
			$final_path = Kohana::find_file(static::$dir, $path, static::$ext);
			
			if ($final_path === FALSE)
			{
				throw new View_Exception(
					'The requested view :path could not be found',
					array(':path' => static::$dir.'/'.$path.'.'.static::$ext));
			}
			
			$this->_partials[$name] = file_get_contents($final_path);
		}

		return $this;
	}
}