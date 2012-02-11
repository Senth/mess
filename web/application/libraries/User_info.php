<?php if ( ! defined('BASEPATH')) exit('No direct scritp access allowed');

/**
 * Handles all the information about the user, if it is logged in etc.
 */
class User_info {
	/**
	 * Creates a new empty user
	 */
	public function __construct() {
		$this->reset();
		log_message('debug', 'User class loaded successfully!');
	}

	/**
	 * Logs out the user
	 */
	public function logout() {
		$this->reset();
	}

	/**
	 * Logs in the specified user
	 * @param user_id id of the user
	 * @param user_name name of the user
	 * @param level of the user
	 */
	public function login($user_id, $user_name, $user_level) {
		$this->m_id = $user_id;
		$this->m_name = $user_name;
		$this->m_level = $user_level;
		$this->m_logged_in = true;
	}

	/**
	 * Returns the name of the user
	 * @return name of the user
	 */
	public function get_name() {
		return $this->m_name;
	}

	/**
	 * Returns the user level
	 * @return user level
	 */
	public function get_level() {
		return $this->m_level;
	}

	/**
	 * Returns the user id
	 * @return user id
	 */
	public function get_id() {
		return $this->m_id;
	}

	/**
	 * Returns true if the user is logged in
	 * @return true if the user is logged in
	 */
	public function is_logged_in() {
		return $this->m_logged_in;
	}

	/**
	 * Resets the user information (except the ip)
	 */
	private function reset() {
		$this->m_name = 'Anonymous';
		$this->m_logged_in = false;
		$this->m_id = -1;
		$this->m_level = -1;
	}

	private $m_name;
	private $m_logged_in;
	private $m_level;
	private $m_id;
}

/* End of file User.php */
