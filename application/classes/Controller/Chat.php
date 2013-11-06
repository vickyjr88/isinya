<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Controller that handles all chat and messages related requests
 *
 * PHP version 5.3
 *
 * @category  Controllers
 * @package   Efficiency_Pro
 * @author    Joseph Bosire <kashboss@gmail.com>
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.1.0
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
class Controller_Chat extends Controller_Site
{

	/**
	 * Controller role requirements
	 * @var array
	 */
	protected $role_required = array('login');


	/**
	 * Function to save chat messages and relay to recepient
	 *
	 * @return void
	 */
	public function action_chat() {
		$recepient = $this->request->param('id');
		$sender = $this->_current_user;
		$avatar = ($sender->personnel_info->personnel_avatar) ? $sender->personnel_info->personnel_avatar : 'default.png';
		//STEP1: Save the chat message;
		$message = ORM::factory('Message');
		$message->values($this->request->post());
		$message->sender = $sender;
		$message->recepient = $recepient;
		$message->time = time();
		try {
			$message->save();
			//STEP2: Send Chat Notification to Recepient
			$date = gmdate('m/d/Y H:i:s', $message->time) . " UTC";
			$payload = array(
				'msg_id' => $message->message_id,
				'msg' => $message->message,
				'time' => $date,
				'id' => $sender->id,
				'username' => $sender->username,
				'avatar' => 'assets/avatars/' . $avatar,
				"local_time" => Date::local_time("now", "m/d/Y H:i:s", "UTC")
			);
			$this->_push('appchat', $payload, array(
				'id' => $recepient,
				'pushUid'=> Kohana::$config->load("pusher.pushUid")
			));
			$this->_set_msg('Successful sent message', 'success', $payload);
		} catch(ORM_Validation_Exception $e) {
			$this->_set_msg('Someone slept on the job', 'error', TRUE);
		}
	}


	/**
	 * Function to save chat messages and relay to recepient
	 *
	 * @return void
	 */
	public function action_chat_history() {
		$this->_template->set('page_title', 'All Messages');
		$this->_template->set('page_info', 'view and manage chat messages');
		$messages = ORM::factory('Message')->get_grouped_messages($this->_current_user->id);
		$this->_template->set('messages', $messages);
		$this->_set_content('messages');
	}


	/**
	 * Function to retrieve offline messages from a particular user
	 *
	 * @return void
	 */
	public function action_offline_messages() {
		$sender = $this->request->param('id');
		$offline_messages = ORM::factory('Message')->get_offline_messages($this->_current_user->id, $sender);
		$this->_set_msg('Offline Messages Retrieved', 'success', $offline_messages);
	}


	/**
	 * Function to mark a sing message as read
	 *
	 * @return void
	 */
	public function action_mark_as_read() {
		if($this->request->param('id')) {
			$id = $this->request->param('id');
			$message = ORM::factory('Message', $id);
			$message->read = 1;
			try {
				$message->save();
				$this->_set_msg('Marked as Read', 'success', $message->as_array());
			} catch(Exception $e) {
				$this->_set_msg('Oops something went wrong failed to mark as read', 'error', TRUE);
			}
			//$this->redirect($this->request->referrer());
		} else if ($this->request->post() && $this->request->post('id')) {
			$id = $this->request->post('id');
			$message = ORM::factory('Message', $id);
			$message->read = 1;
			try {
				$message->save();
				$this->_set_msg('Marked as Read', 'success', $message->as_array());
			} catch(Exception $e) {
				$this->_set_msg('Failed to mark as read', 'error', TRUE);
			}
		}
	}


	/**
	 * Function to mark many messages as read
	 *
	 * @return void
	 */
	public function action_mark_all_as_read() {
		if($this->request->param('id')) {
			$sender = ORM::factory("User", $this->request->param('id'));
			$messages = ORM::factory('Message')->where("sender", '=', $sender->id)->find_all();
			foreach ($messages as $message) {
				$model = ORM::factory('Message', $message->message_id);
				try {
					$model->read = 1;
					$model->save();
				} catch(Exception $e) {
					$this->_set_msg('Failed to mark as read', 'error');
				}
			}
			$this->_set_msg("All conversations with " . $sender->personnel_info->personnel_name . ' were marked as Read',
			'success');
			$this->redirect($this->request->referrer());
		}
	}


	/**
	 * Function to retrieve a recent chat conversation between two users
	 *
	 * @return void
	 */
	public function action_recent_chat_history() {
		$sender = $this->request->param('id');
		$messages = ORM::factory('Message')->get_latest_chat_messages($this->_current_user->id, $sender);
		$this->_set_msg("Retrieved last 5 messages", "success", $messages);
	}

	/**
	 * Function to delete a chat message
	 *
	 * @return void
	 */
	public function action_delete() {
		$id = $this->request->param('id');
		if($id) {
			$message = ORM::factory('Message', $id);
			if($message->loaded()) {
				try {
					$message->delete();
				} catch (Exception $e) {
					$this->_set_msg('Could not delete message because another user is tied to it', 'error');
				}
			}
			$this->redirect($this->request->referrer());
		}
		$this->_set_content('message_delete');
	}

	/**
	 * Function to archive many messages at once
	 *
	 * @return void
	 */
	public function action_delete_all_conversations() {
		$sender = ORM::factory("User", $this->request->param('id'));
		if ($sender->loaded()) {
			$messages = ORM::factory('Message')->get_user_conversations($this->_current_user->id, $sender->id);
			foreach ($messages as $message) {
				try {
					$archive = ORM::factory("ArchivedMessage")->archive($message->message_id, $this->_current_user->id);
				} catch (Exception $e) {
					$this->_set_msg('Could not archive conversation
					 thread because this would wipe out other users history!', 'error');
				}
			}
			$this->_set_msg("All conversations with "
			 . $sender->personnel_info->personnel_name . ' were removed from your mailbox', 'success');
			$this->redirect($this->request->referrer());
		}
		$this->_set_content('message_delete');
	}


	/**
	 * Function to retrieve a set inbox contacts based on most recent conversation with them
	 *
	 * @return void
	 */
	public function action_conversations() {
		//STEP1: Get contacts with the most recent message
		$contacts = ORM::factory('Message')->get_inbox_contacts($this->_current_user->id);
		$this->_template->set('contacts', $contacts);
		//STEP2: Return the most recent conversation to display first time page loads
		//Otherwise return conversation based on user_id param passed in url
		$user2="";
		if ($this->request->param("id")) {
			$user2 = $this->request->param("id");
		} else if(count($contacts)) {
			$user2 = $contacts[0]['user_info']['user_id'];
		}
		$no_messages = (count($contacts)==0) ? True : False;
		$sender = ORM::factory('User', $user2);
		$conversations = ORM::factory('Message')->get_conversations($this->_current_user->id, $sender);
		$this->_template->set('page_title', 'My Conversations');
		$this->_template->set("conversation_info", $sender);
		$this->_template->set("no_messages", $no_messages);
		$this->_template->set("conversations", $conversations);
		$this->_set_content('conversations');
	}

	/**
	 * Function to retrieve the entire conversation with a single user
	 *
	 * @return void
	 */
	public function action_get_conversation() {

		$sender = ORM::factory('User', $this->request->param("id"));
		$conversations =array();// ORM::factory('Message')->get_conversations($this->_current_user->id, $sender);
		$post = $this->request->post();
		$to = "";
		$from = "";
		if($post) {
			$from = date('Y-m-d',strtotime($post['from']));
			$to = date('Y-m-d',strtotime($post['to']));
			if($to == '1969-12-31') {
				$to = date('Y-m-d',strtotime('2030-01-01'));
			}
			if($from == '1969-12-31') {
				$from = date('Y-m-d',strtotime('2030-01-01'));
			}
			$conversations = ORM::factory('Message')->get_conversations($this->_current_user->id, $sender, $from, $to);
		}else {
			$conversations = ORM::factory('Message')->get_conversations($this->_current_user->id, $sender);
		}
		$this->_template->set("conversation_info", $sender);

		$this->_template->set("filters", array("from"=>$from,"to"=>$to,"results"=>$this->_count_all_messages_in_conversation($conversations)));
		$this->_template->set("conversations", $conversations);
		$this->_set_content('conversation');
	}

	/**
	 * Function to count all the messages from multiple conversation threads
	 *
	 * @param array   array of conversation threads
	 *
	 * @return int	  Total count of messages
	 */
	private function _count_all_messages_in_conversation($conversations) {
		$total_messages = 0;
		foreach ($conversations as $conversation) {
			$total_messages += count($conversation['conversations']);
		}
		return $total_messages;
	}
}
