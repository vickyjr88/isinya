<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 404 Error Page
 *
 */
class HTTP_Exception_404 extends Kohana_HTTP_Exception_404 {
 
    /**
     * Generate a Response for the 404 Exception.
     *
     * The user should be shown a nice 404 page.
     *
     * @return Response
     */
    public function get_response()
    {
        $message = $this->getMessage() ? $this->getMessage() : 'Sorry, the url you typed cannot be found.';
        $view = new View(new Template_PHP('errors'), array('title' => 'Page Not Found!', 'message' => $message));
 
        $response = Response::factory()
            ->status(404)
            ->body($view);
 
        return $response;
    }
} // End HTTP_Exception_404
