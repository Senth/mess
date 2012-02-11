<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Model for handling the interaction with items
 */ 
class Item extends CI_Model {
	/**
	 * Constructor
	 */ 
	public function __construct() {
		parent::__construct();

		log_message('debug', 'Initialized Item Model');
	}

	/*
	 * Initializes Item. Takes a user id, the model will then only fetch items
	 * from that user.
	 * @param $user_id id of the user.
	 * @return TRUE if initialization was successful.
	 */
	public function init($user_id) {
		$this->user_id = $user_id;

		return TRUE;
	}


	/**
	 * Move items from one category to another, a more efficient
	 * way of moving instead of updating all items one by one
	 * @param $from_category moves items from this category
	 * @param $to_category moves items to this category
	 * @return TRUE if successful, ERROR_DB if something went wrong
	 */
	public function move_items($from_category, $to_category) {
		$this->db->set('category_id', $to_category);
		$this->db->where('category_id', $from_category);
		$this->db->where('user_id', $this->user_id);
		$update_ok = $this->db->update($this->TABLE_PURCHASE_ITEM);
		if (!$update_ok) {
			return $this->ERROR_DB;
		}

		return TRUE;
	}

	public $ERROR_DB = 'error_db';	

	private $user_id = NULL;

	private $TABLE_ITEM = 'items';
	private $TABLE_PURCHASE_ITEM = 'purchase_items';
}
