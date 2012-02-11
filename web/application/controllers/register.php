<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Controller that handles registrations
 */ 
class Register extends MESS_Controller {
	/**
	 * Constructor
	 */ 
	public function __construct() {
		parent::__construct();
		$this->load->model('User', 'user');
	}

	/**
	 * Validate the registration
	 */ 
	public function index($register_type) {
		// Only admins are able to create account, but not if there are no users
		$admin_level = $this->user->get_level_from_group('Admin');
		$no_users = $this->user->no_users();
		if (!$no_users && $this->user_info->get_level() < $admin_level) {
			$this->access_denied();
			return;
		}

		if (!isset($register_type)) {
			$this->access_denied();
			return;
		}

		$this->load->library('form_validation');
		$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
		$this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email');
		$this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[4]');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]');
		$this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'trim|required|matches[password]');

		// Error - invalid fields
		if ($this->form_validation->run() === FALSE) {
			$this->_show_register($register_type);
			return;
		}

		// Get the level of the user group
		$level = $this->user->get_level_from_group($register_type);

		// Error - no group with that name
		if ($level === FALSE) {
		 	add_error('level', 'There is no group with the ' . $register_type . ' name.');
			$this->_show_register($register_type);
			return;
		}
		
		// Try to create the user
		$success = $this->user->create_user(
			$this->input->post('username'),
			$level,
			$this->input->post('password'),
			$this->input->post('email'),
			$this->input->post('first_name'),
			$this->input->post('last_name')
		);

		// Error - Username already exists
		if ($success == FALSE) {
			add_error('username', 'That username already exists, please specify another one.');
			$this->_show_register($register_type);
			return;
		}

		// Success Message
		set_success('Successfully created ' . strtolower($register_type) . ' ' . $this->input->post('username') . '!');
		redirect('home', 'location');
	}

	private function _show_register($register_type) {
		$content['register_type'] = $register_type;
		$this->load_view('register/index', $content);
	}
}
