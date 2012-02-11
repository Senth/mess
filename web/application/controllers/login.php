<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Controller handling the user interaction
 */ 
class Login extends MESS_Controller {
	/**
	 * Constructor
	 */ 
	public function __construct() {
		parent::__construct();
		$this->load->model('User', 'user');
	}

	/**
	 * Displays a login page
	 */ 
	public function index() {
		// Redirect to home if user is logged in
		if ($this->user_info->is_logged_in())
		{
			redirect('home', 'location');
		}

		if ($this->input->post('login') === FALSE) {
			$this->_show_login();
		} else {
			$this->_validate_login();
		}
	}

	/**
	 * Tries to login with the specified credentials
	 * @param user_name user's name
	 * @param user_password user's password
	 */
	private function _validate_login() {
		$this->load->model('User', 'user');

		// Check if the credentials are correct
		$user_id = $this->user->validate(
			$this->input->post('username'),
			$this->input->post('password')
		);

		// Post some error, and quit
		if ($user_id === FALSE) {
			add_error('no_user', 'There is no user with that password.');
			$this->_show_login();
			return;
		}

		$this->_login($user_id);
	}

	/**
	 * Logs out the user
	 */ 
	public function logout() {
		$this->user_info->logout();
		$this->session->set_userdata('user', $this->user_info);
		redirect('home');
	}

	/**
	 * Show login view
	 */
	private function _show_login() {
		if ($this->user->no_users()) {
			$content['no_users'] = TRUE;
		} else {
			$content['no_users'] = FALSE;
		}

		$this->load_view('login/index', $content);	
	}

	/**
	 * Logins in the specified user
	 * @para user_id the id of the user
	 */ 
	private function _login($user_id) {
		$user_data = $this->user->get_user($user_id);
		assert($user_data !== FALSE);
		$this->user_info->login($user_id, $user_data->name, $user_data->level);
		$this->session->set_userdata('user', $this->user_info);

		// Success Message
		set_success('Successfully logged in!');
		redirect('home');
	}
}
