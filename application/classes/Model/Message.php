<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing Messages table
 *
 * PHP version 5.3
 *
 * @category  Models
 * @package   Efficiency_Pro
 * @author    Joseph Bosire <kashboss@gmail.com>
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.1.1
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
class Model_Message extends Model_Base
{

	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_messages';

	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'message_id';

	/**
	 * Model's relationships
	 * @array
	 */
	protected $_belongs_to = array(
		'user_info' => array(
			'model' => 'User',
			'foreign_key' => 'sender'
			),
		'recepient_info' => array(
		'model' => 'User',
		'foreign_key' => 'recepient'
		)
	);

	protected $_has_many = array(
		'archives'=>array(
			'model'=>'ArchivedMessage',
			'foreign_key'=>'message_id'
		)
	);


	/**
	 * Setup validation rules
	 *
	 * @return array
	 */
	public function rules()
	{
		// TODO: See if there are any rules to be added
		return array();
	}

	/**
	 * Filter messages list
	 *
	 * @param string $search_field Field to perform search on
	 * @param string $search_value Value to search for
	 *
	 * @return void
	 */
	public function filter_messages_list($search_field, $search_value) {
		$table_columns = $this->get_supplier_list_columns();
		// Use buit-in filter across multiple columns
		$this->_search_list($search_field, $search_value, $table_columns);
		// OR perform other custom logic here
	}


	/**
	 * Get list of columns from messages table
	 *
	 * @return array
	 */
	public function get_messages_list_columns() {
		$table_columns = $this->_get_table_columns(array($this->object_name()));
		return $table_columns;
	}


	/**
	 * Get list of 5 of the most recent messages for a user
	 *
	 * @param int	$user_id
	 *
	 * @return object ORM
	 */
	public function get_recent_offline_messages($user_id) {
		return $this->where('recepient', '=', $user_id)
			->where("read", '=', 0)
			->limit(5, 0)->order_by('message_id', 'DESC')
		->find_all();
	}


	/**
	 * Get messages exchaged between two users
	 *
	 * @param int	$user_id id of the currently logged in user
	 * @param int	$sender id of the user messages are sent to
	 *
	 * @return object ORM
	 */
	public function get_messages($user_id, $sender) {
		return $this->where('recepient', '=', $user_id)
			->where('sender', '=', $sender)
			->order_by('message_id', 'ASC')
			->find_all()
			->as_array();
	}


	/**
	 * Group messages based on users
	 *
	 * @param int	$user_id id of the currently logged in user
	 *
	 * @return array
	 */
	public function get_grouped_messages($user_id) {
		$messages = array();
		$senders = $this->select(
			array(
				DB::expr('MAX(message_id)'),
				"latest_message_id"
			)
		)
			->where("recepient", '=', $user_id)
			->where("recepient_deleted", "=", 0)
			->order_by('latest_message_id')
			->group_by("sender")
			->find_all();
		foreach ($senders as $sender) {
			$messages[] = array(
				'sender' => $sender,
				"latest_message" => ORM::factory('Message', $sender->latest_message_id),
				"unread_messages" => ORM::factory('Message')
					->where('sender', '=', $sender->sender)
					->where('recepient', '=', $user_id)
					->where("read", '=', 0)
					->count_all()
			);
		}

		return $messages;
	}
	/**
	 * Returns a list of users who have exchanged messages with the logged in user
	 *
	 *
	 * @param int $user_id id of the currently logged in user
	 *
	 * @return array()
	 */
	public function get_inbox_contacts($user_id) {
		$contacts = array();
		$users = array();
		// STEP1: Get all people who have communicated with the user
		$user_group_one = $this->select(
			array(
				"sender",
				"user"
			)
		)
			->where("recepient", '=', $user_id)
			->where('message_id', 'NOT IN', (ORM::factory('ArchivedMessage')
			->user_archives($user_id))?ORM::factory('ArchivedMessage')->user_archives($user_id):array(0))
			->group_by('user')
			->find_all();
		$user_group_two = $this->select(array(
			"recepient",
			"user"
		))
			->where("sender", '=', $user_id)
			->where('message_id', 'NOT IN', (ORM::factory('ArchivedMessage')
			->user_archives($user_id))?ORM::factory('ArchivedMessage')->user_archives($user_id):array(0))
			->group_by('user')
			->find_all();
		$users = array_unique(array_merge(
				$this->_get_user_info($user_group_one),
				$this->_get_user_info($user_group_two)
			),
			SORT_REGULAR
		);
		// STEP2: Get the latest message exchanged with the users and build up array
		// TODO:: array of users has some null arrays find out why and remove them
		foreach ($users as $user) {
			if (intval($user['user_id']) > 0) {
				$recent_message = $this->get_most_recent_message($user_id, $user['user_id']);
				$contacts[] = array(
					"user_info" => $user,
					"latest_message" => $recent_message,
					"order"=>$recent_message->time,
					"unread_messages" => ORM::factory('Message')
						->where('sender', '=', $user['user_id'])
						->where('recepient', '=', $user_id)->where("read", '=', 0)
						->count_all()
				);
			}
		}

		// Reorder contact based on the most recent message
		$recent_contact = array();
		foreach ($contacts as $key => $row) {
			$recent_contact[$key] = $row['order'];
		}
		array_multisort($recent_contact, SORT_DESC, $contacts);
		return $contacts;
	}


	/**
	 * Get more information on users given a list of user ids
	 *
	 * @param array $user_id list of user ids
	 *
	 * @return array
	 */
	private function _get_user_info($user_ids){
		$users_info = array();
		foreach ($user_ids as $info) {
			$user = ORM::factory('User', $info->user);
			$users_info[]=array(
				"user_id"=>$user->id,
				"username"=>$user->username,
				"name"=>$user->personnel_info->personnel_name,
				"avatar"=>$user->personnel_info->personnel_avatar
			);
		}

		return $users_info;
	}


	/**
	 * Returns the most recent message exchaged
	 *
	 *
	 * @param int $user_1 - logged in user id
	 * @param int $user_2 - id of other user
	 *
	 * @return object ORM
	 */
	public function get_most_recent_message($user1,$user2) {
		$messages = $this->where("recepient", '=', $user1)
			->and_where("sender", '=', $user2)
			->or_where_open()
				->where("sender", '=', $user1)
				->and_where("recepient", '=', $user2)
			->or_where_close()
			->where('message_id', 'NOT IN', (ORM::factory('ArchivedMessage')
			->user_archives($user1))?ORM::factory('ArchivedMessage')->user_archives($user1):array(0))
			->limit(1, 0)
			->order_by('message_id', 'DESC')
			->find_all();
		return $messages[0];
	}


	/**
	 * Get a list of messages sent offline between two users
	 *
	 *
	 * @param int $user_id - logged in user id
	 * @param int $sender - id of other user
	 *
	 * @return array
	 */
	public function get_offline_messages($user_id, $sender) {
		$messages = $this->where('recepient', '=', $user_id)
		->where('sender', '=', $sender)
		->where('read', '=', 0)->find_all();
		$offline_messages = array();
		foreach ($messages as $message) {
			$offline_messages[] = array(
				"msg" => $message->message,
				"username" => $message->user_info->username,
				"avatar" => 'assets/avatars/' . $message->user_info->personnel_info->personnel_avatar,
				"msg_id" => $message->message_id,
				"time" => $message->time
			);
		}

		return $offline_messages;
	}


	/**
	 * Return the total count of offline unread messafes
	 *
	 *
	 * @param int $user_id - logged in user id
	 *
	 * @return int
	 */
	public function get_offline_message_count($user_id) {
		return $this->where('recepient', '=', $user_id)->where('read', '=', 0)->count_all();
	}


	/**
	 * Checks whether message is read
	 *
	 * @return int
	 */
	public function is_read() {
		if ($this->recepient == Auth::instance()->get_user()->id) {
			return $this->read;
		}
		else {
			return 1;
		}
	}


	/**
	 * Formats date into a particular sheme
	 *
	 * @return date
	 */
	public function get_formatted_date() {
		return date('Y-m-d H:i', $this->time);
	}


	/**
	 * Returns the ten most recent chat messages between two users
	 *
	 *
	 * @param int $user_id - id of logged in user
	 * @param int  $sender - id 0f the other user
	 *
	 * @return array()
	 */
	public function get_latest_chat_messages($user_id, $sender) {
		$messages = $this->where("recepient", '=', $user_id)
			->and_where("sender", '=', $sender)
			->or_where_open()
				->where("sender", '=', $user_id)
				->and_where("recepient", '=', $sender)
			->or_where_close()
			->where('message_id', 'NOT IN', (ORM::factory('ArchivedMessage')
			->user_archives($user_id))?ORM::factory('ArchivedMessage')->user_archives($user_id):array(0))
			->limit(10, 0)
			->order_by('message_id', 'ASC')
			->find_all();
		$message_array = array();
		foreach ($messages as $message) {
			$message_array[] = array(
				"msg" => $message->message,
				"username" => $message->user_info->username,
				"avatar" => 'assets/avatars/' . $message->user_info->personnel_info->personnel_avatar,
				"msg_id" => $message->message_id,
				"time" => date('c', $message->time)
			);
		}

		return $message_array;
	}


	/**
	 * Used to return an array with date as key to a group of messages
	 *
	 *
	 * @param int $user_id - id of logged in user
	 * @param int  $sender - id 0f the other user
	 * @param string $from - start of date range
	 * @param string  $to - end of date range
	 *
	 * @return array() ORM objects
	 */
	public function get_conversations($user_id, $sender, $from = "2013-01-01", $to = "2050-01-01") {
		// Step1: Returned grouped messages by date
		$conversation_dates = $this->select(array(
			DB::expr('DATE( FROM_UNIXTIME( time ) )'),
			"conversation_date"
		))
			->where("recepient", '=', $user_id)
			->and_where("sender", '=', $sender)
			->or_where_open()
				->where("sender", '=', $user_id)
				->and_where("recepient", '=', $sender)
			->or_where_close()
			->where('message_id', 'NOT IN', (ORM::factory('ArchivedMessage')
			->user_archives($user_id))?ORM::factory('ArchivedMessage')->user_archives($user_id):array(0))
			->group_by("conversation_date")
			->order_by("conversation_date", "DESC")->find_all();
		$conversations = array();
		foreach ($conversation_dates as $cdate) {
			if($cdate->conversation_date >= $from && $cdate->conversation_date <= $to){
				$conversations[] = array(
					"conversation_date" => $cdate->conversation_date,
					"conversations" => $this->group_conversation_by_date($user_id, $sender, $cdate->conversation_date)
				);
			}
		}

		return $conversations;
	}


	/**
	 * Used group user conversations by date
	 *
	 *
	 * @param int $user_id - id of logged in user
	 * @param int  $sender - id 0f the other user
	 * @param date $date - date to group by
	 *
	 * @return array() ORM objects
	 */
	public function group_conversation_by_date($user_id, $sender, $date) {
		return $this->select(array(DB::expr('DATE( FROM_UNIXTIME( time ) )'), "conversation_date"))
			->where("recepient", '=', $user_id)
			->and_where("sender", '=', $sender)
			->or_where_open()
				->where("sender", '=', $user_id)
				->and_where("recepient", '=', $sender)
			->or_where_close()
			->having(DB::expr('DATE( FROM_UNIXTIME( time ) )'), "=", $date)
			->order_by("message_id", "DESC")
			->find_all();
	}


	/**
	 * Used to get a list of of user conversation to be used to archive messages
	 *
	 *
	 * @param int $user_id - id of logged in user
	 * @param int  $sender - id 0f the other user
	 *
	 * @return objects ORM objects
	 */
	public function get_user_conversations($user_id, $sender) {
		return $this->where("recepient", '=', $user_id)
			->and_where("sender", '=', $sender)
			->or_where_open()
				->where("sender", '=', $user_id)
				->and_where("recepient", '=', $sender)
			->or_where_close()
			->where('message_id', 'NOT IN', (ORM::factory('ArchivedMessage')->user_archives($user_id)) ? ORM::factory('ArchivedMessage')->user_archives($user_id) : array(0))
			->find_all();

	}


}
