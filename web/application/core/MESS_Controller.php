<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Base class of all controllers in MESS
 */ 
class MESS_Controller extends CI_Controller {
	public function __construct() {
		parent::__construct();

		// Initialize the user if it hasn't been created
		if (!$this->session->userdata('user')) {
			// Set the user in the session
			$this->session->set_userdata('user', $this->user_info);

			log_message('debug', 'Successfully loaded the user class.');
		}
		// Else make it easier to access the user
		else {
			log_message('debug', 'User was already loaded, setting the local user');
			$this->user_info = & $this->session->userdata('user');
		}

		$this->load->model('Menu', 'menu');
	}

	/**
	 * Loads the specified view with the passed content information
	 * @param view view to load (as content page)
	 * @param content information in the content
	 */ 
	protected function load_view($view, $content) {
		$buttons = $this->menu->get_buttons();

		$inner_content = array(
			'view' => $view,
			'data' => $content
		);

		$view_data = array(
			'buttons' => $buttons,
			'inner_content' => $inner_content
		);

		$this->load->view('template/index', $view_data);
	}

	/**
	 * Validates the user access to the page. If the user doesn't have access
	 * an error page is displayed (the same as is called in #access_denied()).
	 * @param group_name the minimum required access level, should be a
	 * 	name and not the actual value.
	 */
	protected function validate_access($group_name) {
		$this->load->model('User', 'user');
		$level_required = $this->user->get_level_from_group($group_name);

		if ($this->user_info->get_level() < $level_required) {
			$this->access_denied();
		}
	}

	/**
	 * Prints an access denied error
	 */
	protected function access_denied() {
			show_error('Access denied! You don\'t have access to this page. Try ' . anchor('login', 'logging in') . '...');	
	}
}
