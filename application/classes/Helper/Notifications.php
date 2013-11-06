<?php defined('SYSPATH') or die('No direct script access.');
class Helper_Notifications{


	/**
	 * Returns the number of friend invites
	 * @param $user_id id of the logged in user account
	 * @return $invites int total number of invites
	 */
	 public static function get_no_invites($user_id){
		$invites = ORM::factory('friend')
		->where('fk_user_two','=',$user_id)
		->where('status','=','0')
		->count_all();
		return $invites;
	}
	/**
	 * Returns list of people who have requested to be your friend
	 * @param $user_id id of the logged in user account
	 * @return $friends array of ORM objects
	 */
	public static function get_friend_invites($user_id){
		
		$friends = Model_Friend::get_friend_list($user_id,0);
		$temp = array();
		//print_r($friends);
		foreach ($friends as $friend) {
			$invite = $friend->followers->where('fk_user_one','=',$user_id)->find();
			//print_r($invite);
			if(!$invite->loaded())	
				$temp[] =$friend;
			
		}
		
		return $temp;
	}
	
}