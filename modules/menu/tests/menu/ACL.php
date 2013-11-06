<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Utility class for testing the visibility of menu items
 *
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @author Ando Roots <ando@sqroot.eu>
 * @since 2.0
 * @package Kohana/Menu
 * @category Tests
 * @group kohana.menu
 * @copyright (c) 2012, Ando Roots
 */
class Menu_ACL
{

	public static $is_admin;

	public static function is_admin()
	{
		return self::$is_admin;
	}
}