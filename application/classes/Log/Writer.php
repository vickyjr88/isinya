<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Log_Writer
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
abstract class Log_Writer extends Kohana_Log_Writer
{


	/**
	 * Formats a log entry.
	 *
	 * @param array  $message
	 * @param string $format
	 *
	 * @return string
	 */
	public function format_message(array $message, $format = "time --- level: body in file:line") {
		$additional = null;
		if (isset($message['additional']['exception'])) {
			$additional = $message['additional']['exception'];
		}

		$message['time']  = Date::formatted_time('@' . $message['time'], self::$timestamp, self::$timezone, true);
		$message['level'] = $this->_log_levels[$message['level']];
		unset($message['additional']);
		unset($message['trace']);

		$string = strtr($format, $message);

		if ($additional) {
			// Re-use as much as possible, just resetting the body to the trace
			$message['body']  = $additional->getTraceAsString();
			$message['level'] = $this->_log_levels[self::$strace_level];

			$string .= PHP_EOL . strtr($format, $message);
		}

		return $string;
	}


}
