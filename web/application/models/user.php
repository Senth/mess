<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Model interacting with the user table
 */
class User extends CI_Model {
	/**
	 * Constructor, does nothing
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Checks if there are no users in the database
	 * @return true if there are no users
	 */
	public function no_users() {
		$this->db->select('id');
		$this->db->from('users');

		$query = $this->db->get();

		return $query->num_rows() == 0;
	}

	/**
	 * Checks if the user credentials are correct
	 * @param name the user's name
	 * @param password the user's password
	 * @return if the user credentials are correct an id of the
	 * 		user is returned, else false is returned
	 */ 
	public function validate($name, $password) {
		$this->db->select('id');
		$this->db->from('users');
		$this->db->where('name', $name);
		$this->db->where('password', md5($password));

		$query = $this->db->get();

		// Return the id
		if ($query->num_rows() == 1) {
			return $query->row()->id;
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns an array with all the user information
	 * @param id the user's id to fetch information about
	 * @return array with all the user information, false if the user doesn't exist.
	 */
	public function get_user($id) {
		$this->db->from('users');
		$this->db->where('id', $id);

		$query = $this->db->get();

		if ($query->num_rows() === 1) {
			$row = $query->row();
			return $row;
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns the level of the specified user group
	 * @param name the name of the group
	 * @return the level of the specified group if it exists, else it returns false
	 */ 
	public function get_level_from_group($name) {
		$this->db->select('level');
		$this->db->from('user_groups');
		$this->db->where('name', $name);

		$query = $this->db->get();

		if ($query->num_rows() === 1) {
			$row = $query->row();
			return $row->level;
		} else {
			return FALSE;
		}
	}

	/**
	 * Tries to create a user with the specified information
	 * @param username the user name
	 * @param level level of the user
	 * @param password password of the user, this function will md5-hash it
	 * @param email the email of the user
	 * @param first_name the first name of the user
	 * @param last_name the last name of the user
	 * @return true if the user was created successfully. Returns false if a user with the specified
	 * 	username already exists.
	 */
	public function create_user($username, $level, $password, $email, $first_name, $last_name) {
		// Check for existing user
		$this->db->select('id');
		$this->db->from('users');
		$this->db->where("name COLLATE utf8_general_ci LIKE '" . $username . "'");
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return false;
		} 

		// Insert the user
		$this->db->set('name', $username);
		$this->db->set('level', $level);
		$this->db->set('password', md5($password));
		$this->db->set('email', $email);
		$this->db->set('first_name', $first_name);
		$this->db->set('last_name', $last_name);
		$this->db->set('created', time());
		
		return $this->db->insert('users');
	}
}
