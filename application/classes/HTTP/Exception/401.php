<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 401 Error Page
 *
 */
class HTTP_Exception_401 extends Kohana_HTTP_Exception_401 {
 
    /**
     * Generate a Response for the 401 Exception.
     *
     * The user should be redirect to a login page.
     *
     * @return Response
     */
    public function get_response()
    {
        $component = Session::instance()->get('sys_template_component');
		$login_url = 'user/login';
		if ($component && $component != 'site')
			$login_url = $component . '/user/login'; 
        $response = Response::factory()
            ->status(401)
            ->headers('Location', URL::site($login_url));
 
        return $response;
    }
} // End HTTP_Exception_401
