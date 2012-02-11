<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Controller for the 'home' page
 */ 
class Home extends MESS_Controller {
	/**
	 * Shows the 'home' page
	 */ 
	public function index() {
		$content = array(
			'header' => 'This is the header',
			'content' => 'This is the content'
		);

		$this->load_view('home/index', $content);
	}
}
