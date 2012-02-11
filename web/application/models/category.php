<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Model for handling the interaction with categories
 */ 
class Category extends CI_Model {
	/**
	 * Constructor
	 */ 
	public function __construct() {
		parent::__construct();
		log_message('debug', 'Initialized Category Model');
	}

	/**
	 * Creates a new category for the specified user
	 * @param name name of the category to create
	 * @param user_id id of the user to create the category under
	 * @return true if the category was created, ERROR_DB if an error occured while
	 * 	writing to the db, ERROR_ALREADY_EXISTS if the specified
	 */ 
	public function create($name, $user_id) {
		// Check so that the category doesn't already exist
		$this->db->select('id');
		$this->db->from('categories');
		$this->db->where('name', $name);
		$this->db->where('user_id', $user_id);
		$query = $this->db->get();

		if ($query->num_rows() >= 1) {
			return $this->ERROR_ALREADY_EXISTS;
		}

		// Insert the new category
		$this->db->set('name', $name);
		$this->db->set('user_id', $user_id);
		$insert_ok = $this->db->insert('categories');
		if (!$insert_ok) {
			return $this->ERROR_DB;
		}

		return TRUE;
	}

	/**
	 * Updates the name of the specified category
	 * @param id id of the category
	 * @param new_name the new name of the category
	 * @param user_id the user_id of the category, for safety messures.
	 * @return TRUE if successfully updated the name. ERROR_ALREADY_EXISTS
	 * 	if the category already exists, ERROR_DB if something went
	 * 	wrong when updating.
	 */
	public function update_name($id, $new_name, $user_id) {
		// Check so that there doesn't exists a category with that name
		$this->db->select('id');
		$this->db->from('categories');
		$this->db->where('name', $new_name);
		$this->db->where('user_id', $user_id);
		$this->db->limit(1);
		$query = $this->db->get();
		if ($query == FALSE) {
			return $this->ERROR_DB;
		}
		// There is one exception, when the found category has the same id
		// as the one we want to change. This would mean that we want to
		// change from the same name to the same name (but the capital letters
		// could differ)
		else if ($query->num_rows() > 0 && $query->row()->id !== $id) {
			return $this->ERROR_ALREADY_EXISTS;
		}

		$this->db->set('name', $new_name);
		$this->db->where('user_id', $user_id);
		$this->db->where('id', $id);
		$update_ok = $this->db->update('categories');
		if (!$update_ok) {
			return $this->ERROR_DB;
		}

		return TRUE;
	}

	/**
	 * Updates the parent of the specified category
	 * @param id id of the category to update
	 * @param new_parent_id the id of the new parent
	 * @param user_id id of the user
	 * @return TRUE if successfully updated the category's parent.
	 * 	ERROR_DB if something went wrong with the db, ERROR_NO_PARENT_EXISTS
	 * 	if the specified parent doesn't exist.
	 */
	function update_parent($id, $new_parent_id, $user_id) {
		// Skip NULL parent
		if ($new_parent_id !== NULL) {
			$this->db->select('id');
			$this->db->from('categories');
			$this->db->where('id', $new_parent_id);
			$this->db->where('user_id', $user_id);
			$query = $this->db->get();
			if ($query == FALSE) {
				return $this->ERROR_DB;
			}
			else if ($query->num_rows() != 1) {
				return $this->ERROR_NO_PARENT_EXISTS;
			}
		}

		$this->db->set('parent_id', $new_parent_id);
		$this->db->where('id', $id);
		$this->db->where('user_id', $user_id);
		$update_ok = $this->db->update('categories');
		if (!$update_ok) {
			return $this->ERROR_DB;
		}

		return TRUE;
	}

	/**
	 * Changes the parents on all categories that have the specified category as a parent
	 * @param from_parent parent id of the categories we wish to change
	 * @param to_parent the new parent id the categories will have
	 * @return TRUE if successful, ERROR_DB if something went wrong
	 */
	public function change_parents($from_parent, $to_parent, $user_id) {
		$this->db->set('parent_id', $to_parent);
		$this->db->where('parent_id', $from_parent);
		$this->db->where('user_id', $user_id);
		$update_ok = $this->db->update('categories');
		if (!$update_ok) {
			return $this->ERROR_DB;
		}

		return TRUE;
	}

	/**
	 * Deletes a category
	 * @param id id of the category to delet
	 * @param user_id id of the user
	 * @param TRUE if successful, ERROR_DB if something went wrong
	 */
	public function delete($id, $user_id) {
		$this->db->where('id', $id);
		$this->db->where('user_id', $user_id);
		$delete_ok = $this->db->delete('categories');
		if (!$delete_ok) {
			return $this->ERROR_DB;
		}

		return TRUE;
	}

	/**
	 * Returns the id of the category that is alphabetically right before the specified
	 * name and has the same parent
	 * @param name name of the category that should be after the category we want to find
	 * @param parent_id id of the parent the category should have.
	 * @param user_id id of the user
	 * @return id of the category that is alphabetically right before. If no category is
	 * found, FALSE will be returned.
	 */
	public function get_category_before($name, $parent_id, $user_id) {
		$this->db->select('id');
		$this->db->from('categories');
		$this->db->where('user_id', $user_id);
		$this->db->where('parent_id', $parent_id);
		$this->db->where('name <', $name);
		$this->db->order_by('name', 'DESC');
		$this->db->limit(1);
		$query = $this->db->get();

		if ($query->num_rows() >= 1) {
			$row = $query->row();
			return $row->id;
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns the categories for the specified user id
	 * @param user_id the user to fetch the categories from
	 * @return all categories for the user, FALSE if an error occurred.
	 * 	The categories are sorted after its parent, i.e. array['NULL'] contains
	 * 	all the categories of the highest level, whereas array[$category_id] contains
	 * 	all the children of the category with $category_id.
	 */ 
	public function get_all($user_id) {
		$this->db->select('id');
		$this->db->select('name');
		$this->db->select('parent_id');
		$this->db->from('categories');
		$this->db->where('user_id', $user_id);
		$this->db->order_by('name', 'asc');
		$query = $this->db->get();

		if ($query == FALSE) {
			return FALSE;
		}

		return $this->_sort_categories($query, FALSE);
	}

	/**
	 * Returns the categories with the specified parent id
	 * @param parent_id id of the categories' parent id
	 * @param user_id id of the user
	 * @return all categories for the user with the specified parent id, FALSE if an error occured.
	 */
	public function get_all_by_parent($parent_id, $user_id) {
		$this->db->select('id');
		$this->db->select('name');
		$this->db->from('categories');
		$this->db->where('user_id', $user_id);
		$this->db->where('parent_id', $parent_id);
		$this->db->order_by('name', 'asc');
		$query = $this->db->get();

		if ($query == FALSE) {
			return FALSE;
		}

		return $this->_sort_categories($query, TRUE);
	}

	/**
	 * Returns the specified category by id
	 * @param id the category id
	 * @param user_id id of the user
	 * @return the category, FALSE if it doesn't exist
	 */ 
	public function get_by_id($id, $user_id) {
		$this->db->from('categories');
		$this->db->where('user_id', $user_id);
		$this->db->where('id', $id);
		$query = $this->db->get();

		if ($query == FALSE || $query->num_rows() != 1) {
			return FALSE;
		}

		return $query->row();
	}

	/**
	 * Returns the specified category by name
	 * @param name the category name
	 * @param user_id id of the user
	 * @return the specified category, FALSE if it doesn't exist.
	 */
	public function get_by_name($name, $user_id) {
		$this->db->from('categories');
		$this->db->where('user_id', $user_id);
		$this->db->where('name', $name);
		$query = $this->db->get();

		if ($query == FALSE || $query->num_rows() != 1) {
			return FALSE;
		}

		return $query->row();
	}

	/**
	 * Returns categories that are like the name specified
	 * @param name the name that it should search for
	 * @param user_id id of the user
	 * @return categories that are like the name specified
	 */
	public function get_like_name($name, $user_id) {
		// LIKE after
		$this->db->from('categories');
		$this->db->like('name', $name, 'after');
		$this->db->where('user_id', $user_id);
		$this->db->order_by('name', 'asc');
		$query = $this->db->get();

		$categories = array();

		foreach ($query->result() as $row) {
			$categories[$row->id] = array(
				'name' => $row->name,
				'id' => $row->id
			);
		}

		// LIKE both
		$this->db->from('categories');
		$this->db->like('name', $name, 'both');
		$this->db->where('user_id', $user_id);
		$this->db->order_by('name', 'asc');
		$query = $this->db->get();

		foreach ($query->result() as $row) {
			$categories[$row->id] = array(
				'name' => $row->name,
				'id' => $row->id
			);
		}

		return $categories;
	}
	
	/**
	 * Gets all the children and sub children of the specified id.
	 * Wrapper for the recursive function
	 * @param id id of the category to find all the children and sub children
	 * @param user_id id of the user
	 * @param add_parent if we should add the parent as a 'child', default false.
	 */
	public function get_children_of($id, $user_id, $add_parent = false) {
		$categories = $this->get_all($user_id);

		$children = array();
		$this->_get_children_recursive($id, $categories, $children);
		if ($add_parent === TRUE) {
			$children[$id] = true;
		}

		return $children;
	}

	public $ERROR_DB = 'error_db';
	public $ERROR_ALREADY_EXISTS = 'error_already_exists';
	public $ERROR_NO_PARENT_EXISTS = 'error_no_parent_exists';

	/**
	 * Returns all the children of the specified category
	 * @param id id of the category to find all the children and sub children from
	 * @param categories all the categories
	 * @param[out] children adds all the children to this variable
	 */
	private function _get_children_recursive($id, &$categories, &$children) {
		if (isset($categories[$id])) {
			foreach($categories[$id] as $child) {
				$children[$child['id']] = true;
				$this->_get_children_recursive($child['id'], $categories, $children);
			}
		}
	}

	/**
	 * Returns a sorted array.
	 * @param query inputs a result query from the db
	 * @param single_parent if there only is a single parent, then the categories will
	 * 	only be inserted into a 1D array, and a parent_id doesn't need to be specified
	 * @return The categories sorted after their parent, i.e. array['NULL'] contains
	 * 	all the categories of the highest level, whereas array[$category_id] contains
	 * 	all the children of the category with $category_id.
	 */ 
	private function _sort_categories($query, $single_parent) {
		$sorted_array = array();

		if ($single_parent === TRUE) {
			foreach($query->result() as $row) {
				$sorted_array[$row->id] = array(
					'name' => $row->name,
					'id' => $row->id
				);
			}
		} else {
			foreach($query->result() as $row) {
				$sorted_array[$row->parent_id][$row->id] = array(
					'name' => $row->name,
					'id' => $row->id
				);
			}
		}

		return $sorted_array;
	}
}
