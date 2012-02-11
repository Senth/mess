<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model for handling the menu; such as retrieving buttons,
 * rearranging the buttons etc.
 */ 
class Menu extends CI_Model {

	/**
	 * Constructor that does 'nothing'
	 */ 
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Returns all the buttons that should be available for the active user
	 * @return array of buttons that the user should see
	 */
	public function get_buttons() {
		// Fetch buttons. Only fetch buttons the user should see (using level)
		// And don't fetch only_logged_out buttons if the user is logged in
		$this->db->select('name, link');
		$this->db->from('menu');
		$this->db->where('level <=', $this->user_info->get_level());
		$this->db->order_by('order', 'asc');

		// Check if the user is logged in, then we should not include
		// only logged out buttons
		if ($this->user_info->is_logged_in()) {
			$this->db->where('only_logged_out', 0);
		}

		$query = $this->db->get();

		foreach($query->result() as $row) {
			$buttons[] = array (
				'name' => $row->name,
				'link' => $row->link
			);
		}

		return $buttons;
	}
}
