<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This controller contains all logic for user management
 *
 * @version 02 - Hezron Obuchele 2013-05-23
 *
 * PHP version 5
 * LICENSE: Not for reuse or modification without the express 
 * written authorization from BeeBuy Investments Ltd.
 *
 * Controller_User
 * @author     Hezron Obuchele
 * @package    Efficiency Pro
 * @category   Controllers
 * @copyright  (c) 2013 BeeBuy Investments Ltd. - http://www.beebuy.biz
 */
class Controller_User extends Controller_Site {
	
	protected $_template_layout = 'login';
	
	public function action_login(){
		$this->_template->set('page_title', 'Login');
		if ($this->request->post()){
			// try to login user
			$username =  $this->request->post('username');
			$user_password =  $this->request->post('password');
			$remember =  $this->request->post('remember');
			if ($username !== null && $user_password !== null){
	            Auth::instance()->login($username, $user_password, $remember);
				if (Auth::instance()->get_user())
					$this->redirect(Cookie::get('user_redirect', 'home'));
				else
					$this->_set_msg("Invalid username/password!", "error", true);
	        }
		}
		// display login page
		$content = array();
		$this->_set_content('blank');
		$this->_template->set('content_data', $content);
	}
	
	public function action_logout(){
		if (Auth::instance()->logged_in()){
			Auth::instance()->logout();
			$this->_set_msg("You are now logged out.", "success");
		}
		$this->redirect('user/login');
	}
	
	public function action_question(){
		$recovery_username = $this->request->query('username');
		$user = ORM::factory('User')->where('username','=',$recovery_username)->find();
		if($user->loaded()){
			$this->_set_msg('Please answer your security question','success',$user->as_array());
		}else{
			$this->_set_msg('The username provided does not exist','error',TRUE);
		}
		
	}
	
	public function action_register_user_internal(){
		$model = ORM::factory('User');
		$model->username = 'evans';
		$model->email = 'evans@example.com';
		$model->password = 'evans';
		$model->save();
		$model->add('roles', ORM::factory('Role')->where('name', '=', 'inventory_staff')->find());
		$model->add('roles', ORM::factory('Role')->where('name', '=', 'login')->find());
	}
	
	public function action_change_password(){
		$answer = $this->request->post('answer');
		$username = $this->request->post('username');
		$new_password = $this->request->post('password');
		$user = ORM::factory('User')->where('username','=',$username)->where('answer','=',$answer)->find();
		if($user->loaded()){
			$model = ORM::factory('User',$user->id);
			$model->password = $new_password;
			$model->save();
			$this->_set_msg('Password Successfully changed you can now login','success',TRUE);
		}else{
			$this->_set_msg('You provided the wrong answer','error',TRUE);
		}
	}	
	
}

