<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Controller handling access denied messages
 */
class Access_denied extends MESS_Controller {
	/**
	 * Constructor
	 */ 
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Displays an access denied message
	 */
	public function index() {
		$this->load_view('access_denied', NULL);
	}
}
