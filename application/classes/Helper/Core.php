<?php defined('SYSPATH') or die('No direct script access.');
class Helper_Core{
	
	private static $_uri_segments = null;

	/**
	 * Returns the number of friend invites
	 * @param $user_id id of the logged in user account
	 * @return $invites int total number of invites
	 */
	 public static function process_uri_segments(){
		if (self::$_uri_segments == null){
			$uri_segments = array();
			foreach($segments = explode('/', Request::detect_uri()) as $key => $segment) {
			    // make sure urls ending with / are treated same as those without the last / as well as those ending with /index
			    if (strlen($segment) && !in_array($segment, $uri_segments) && $segment != 'index') {
			        $uri_segments[] = $segment;
			    }
			}
			self::$_uri_segments = $uri_segments;
		}
		
		return self::$_uri_segments;
	}
	
	
	
}