<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 404 Error Page
 *
 */
class HTTP_Exception_403 extends Kohana_HTTP_Exception_403 {
 
    /**
     * Generate a Response for the 403 Exception.
     *
     * The user should be shown a nice 403 page.
     *
     * @return Response
     */
    public function get_response()
    {
        $view = new View(new Template_PHP('errors'), array('title' => 'No access!', 'message' => $this->getMessage()));
 
        $response = Response::factory()
            ->status(403)
            ->body($view);
 
        return $response;
    }
} // End HTTP_Exception_404
