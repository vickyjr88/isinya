<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing archived messages table
 *
 * @version 01 - Joseph Bosire 2013-08-29
 *
 * PHP version 5
 */
class Model_ArchivedMessage extends Model_Base {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_archived_messages';
	
	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'archived_message_id';
	
	/**
	 * Model's relationships
	 * @array
	 */
	protected $_belongs_to = array(
					'message' => array('model' => 'Message', 'foreign_key' => 'message_id'),
					);
	/**
	 * Archive functions that marks a message as archive
	 * @param $messag_id,$user_id
	 * @return boolean
	 * @author  Joseph Bosire
	 */	
	public function archive($message_id,$user_id){
		$model = ORM::factory('ArchivedMessage');
		$model->message_id = $message_id;
		$model->user_id = $user_id;
		$model->save();
		return true;
	}
	/**
	 * Get the ids of messages archived by the user
	 * @param $user_id
	 * @return array() of message ids
	 * @author  Joseph BOsire
	 */
	public function user_archives($user_id) {
		$archives = ORM::factory('ArchivedMessage')->where('user_id','=',$user_id)->find_all();
		$message_ids = array();
		foreach ($archives as $archive) {
			$message_ids[] = intval($archive->message_id);
		}
		return $message_ids;
	}
}
