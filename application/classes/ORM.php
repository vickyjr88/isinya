<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ORM
 *
 * PHP version 5
 *
 * @category  Kohana
 * @package   Efficiency_Pro
 * @author    Hezron Obuchele <hezron4k@gmail.com>
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.1.2
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
class ORM extends Kohana_ORM
{


	/**
	 * Function to begin a database transaction
	 *
	 * @return void
	 */
	public static function begin_transaction() {
		Database::instance()->begin();
	}


	/**
	 * Function to commit a database transaction
	 *
	 * @return void
	 */
	public static function commit_transaction() {
		Database::instance()->commit();
	}


	/**
	 * Function to roll back a database transaction
	 *
	 * @return void
	 */
	public static function rollback_transaction() {
		Database::instance()->rollback();
	}


}
