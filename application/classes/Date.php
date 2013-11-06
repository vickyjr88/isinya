<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Date
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
class Date extends Kohana_Date
{


	/**
	 * Returns a date/time string in the specified timestamp format in the specified
	 * timezone/local user timezone (http://www.php.net/manual/datetime.construct)
	 *
	 *     $time = Date::local_time('5 minutes ago');
	 *
	 * @param  string $datetime_str     datetime string
	 * @param  string $timestamp_format timestamp format
	 * @param  string $timezone         timezone identifier, defaults to user-detected timezone/UTC otherwise
	 *
	 * @return string
	 */
	public static function local_time($datetime_str = 'now',
		$timestamp_format = "Y-m-d H:i:s", $timezone = null) {
		// Use autodetected timezone from JS if not specified here
		if (!$timezone) {
			$timezone = defined('USER_TIMEZONE') ? USER_TIMEZONE : 'UTC';
		}
		// Set date using default timezone
		$date_time = new DateTime($datetime_str);
		// adjust timezone to user pref/args
		$date_timezone = new DateTimeZone($timezone);
		$date_time->setTimezone($date_timezone);

		return $date_time->format($timestamp_format);
	}


	/**
	 * Deprecated! Alias of Date::local_time() retained for backwards compatibility,
	 * use that instead.
	 *
	 * @param  string $datetime_str     datetime string
	 * @param  string $timestamp_format timestamp format
	 * @param  string $timezone         timezone identifier
	 *
	 * @return string
	 */
	public static function user_time($datetime_str = 'now',
		$timestamp_format = "Y-m-d H:i:s", $timezone = null) {
		// Use autodetected timezone from JS if not specified here
		return self::local_time($datetime_str, $timestamp_format, $timezone);
	}


}
