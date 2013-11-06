<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This controller contains all logic common to all site controllers
 *
 * PHP version 5.3
 *
 * @category  Controllers
 * @package   Efficiency_Pro
 * @author    Hezron Obuchele <hezron4k@gmail.com>
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.0.8
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
class Controller_Site extends Controller_App
{

	/**
	 * System component name
	 * @var string
	 */
	protected $_component_name = 'site';

	/**
	 * Controller role requirements
	 * @var array
	 */
	protected $role_required = array('login');


	/**
	 * Before hook to initialize all site logic
	 *
	 * @return void
	 */
	public function before() {
		parent::before();
		// load user menu for logged_in users
		$this->_template->set('menuname', $this->_get_user_menu_config());
		// Setup language switcher
		$lang_files = Kohana::list_files('i18n', array(APPPATH));
		$lang_codes = array();

		foreach ($lang_files as $file_name => $full_path) {
			$code = str_ireplace('i18n' . DIRECTORY_SEPARATOR, '', $file_name);
			$code = str_ireplace('.php', '', $code);
			$lang_codes[] = $code;
		}

		
		$this->_template->set('languages', $lang_codes);		

		// setup breadcrumbs
		$this->_breadcrumbs('Home');
	}


	/**
	 * General purpose function to generate a report based on the parameters set in the URL
	 *
	 * @return void
	 */
	public function action_report() {
		$format = $this->request->param('id');
		$data   = $this->request->query();
		if (empty($data) || !isset($data['report_type'])) {
			throw new HTTP_Exception_404('Report not found!');
		}

		$report_type = $data['report_type'];
		// get report data
		unset($data['report_type']);
		$params = $data;

		foreach ($params as $k => $p) {
			if (is_numeric($p)) {
				$params[$k] = (int) $p;
			}
		}

		// Display the report on browser
		$jasper = new JasperReport($this->response);
		$jasper->generate_report($report_type, $params, 'I', array('auto_print' => true, 'format' => $format));
	}


	/**
	 * Function to email a report to a recepient
	 *
	 * @return void
	 */
	public function action_forward_report() {
		$status = array();
		$data   = $this->request->post();
		if (empty($data) || !isset($data['report_type']) || (!isset($data['member_id']) && !isset($data['loan_id']))
		|| ($data['member_id'] == 0 && $data['loan_id'] == 0)) {
			$status['status'] = 'Sorry, invalid request!';
		}

		$rcpt_id     = $data['member_id'];
		$loan_id     = $data['loan_id'];
		$report_type = $data['report_type'];
		// get user email address
		$member = ARM::factory('member')->get_member($rcpt_id);
		$to     = 'demo@localhost.com';

		$status['status'] = 'Email successfuly sent to ' . $member->member_name . '.';
		// get report data
		$params = array('parameter1' => 1);

		if ($to === '') {
			$status['status'] = 'Sorry, there was no email address found for ' . $member->member_name . '.';
		} else {
			$subject = 'Rununu User Report';
			// get site sender address
			$from      = Kohana::config('settings.site_email_address');
			$from_name = Kohana::config('settings.site_email_name');
			// get message body from url provided
			// TODO: Check if we can use the E output type for emails attachments of all file types aside from pdf
			try {
				$email = Email::factory($subject, '<p>Attached.</p>', 'text/html')->message('Attached.', 'text/plain')->attach_content(JasperReport::generate_report($report_type, $params, 'S'), 'user_report.pdf', 'application/pdf')->to($to)->from($from, $from_name)->send();
			} catch (Exception $e) {
				$email = false;
			}

			if (!$email) {
				$status['status']  = 'Sorry, the email could not be sent to ' . $member->member_name;
				$status['status'] .= ' at this time. Please try again later.';
			}
		}

		$this->response->body(json_encode($status));
	}


	/**
	 * Run any site functionality that needs to be performed after the action
	 *
	 * @return void
	 */
	public function after() {
		// Turn off templating for reports
		if (strtolower(Request::$current->action()) !== 'report') {
			parent::after();
		}

	}




}
