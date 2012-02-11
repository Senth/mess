<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Controller handling the categories
 */
class Categories extends MESS_Controller {
	/**
	 * Constructor
	 */ 
	public function __construct() {
		parent::__construct();
		$this->load->model('Category', 'category');
		$this->load->model('Item', 'item');
		$this->item->init($this->user_info->get_id());

		$this->validate_access('User');	
	}

	/**
	 * Displays all the categories inculding being able to create categories
	 */ 
	public function index() {
		$categories = $this->category->get_all($this->user_info->get_id());

		$content['display_categories']['categories'] = $categories;

		$this->load_view('category/index', $content);
	}

	/**
	 * Creates a new category
	 */ 
	public function create() {
		// Only handle ajax updates
		if ($this->input->post('ajax') === FALSE) {
			return;
		}

		$json_return['success'] = FALSE;

		// Validate
		$this->load->library('form_validation');
		$this->form_validation->set_rules('category_name', 'Category Name', 'trim|required|min_length[3]|max_length[50]');
		if($this->form_validation->run() === FALSE) {
			get_errors($json_return);
			echo json_encode($json_return);
			return;
		}

		// Create the actual category
		$success = $this->category->create($this->input->post('category_name'), $this->user_info->get_id());

		if ($success === $this->category->ERROR_ALREADY_EXISTS) {
			add_error_json('This Category already exists.', $json_return);
			echo json_encode($json_return);
			return;
		} else if ($success === $this->category->ERROR_DB) {
			add_error_json('Error inserting the category to the db.', $json_return);
			echo json_encode($json_return);
			return;
		}

		set_success_json('Successfully created ' . $this->input->post('category_name') . ' category!', $json_return);
		$json_return['success'] = TRUE;
		echo json_encode($json_return);
	}

	/**
	 * Updates a category
	 */
	public function update() {
		// Only handle ajax updates
		if ($this->input->post('ajax') === FALSE) {
			return;
		}

		$json_return['success'] = FALSE;

		// Name
		if ($this->input->post('variable_name') == 'category_name') {
			// Validate
			$this->load->library('form_validation');
			log_message('debug', 'variable data: ' . $this->input->post('variable_data'));
			$this->form_validation->set_rules('variable_data', 'Category Name', 'trim|required|min_length[3]|max_length[50]');
			if($this->form_validation->run() === FALSE) {
				get_errors($json_return);
				echo json_encode($json_return);
				return;
			}


			$update_status = $this->category->update_name($this->input->post('id'), $this->input->post('variable_data'), $this->user_info->get_id());
			if ($update_status === TRUE) {
				$json_return['success'] = TRUE;
			} else if ($update_status === $this->category->ERROR_DB) {
				add_error_json('Could not update the category name', $json_return);
			} else if ($update_status === $this->category->ERROR_ALREADY_EXISTS) {
				add_error_json('There already exists a category with that name', $json_return);
			} else {
				add_error_json('Unknown error', $json_return);
			}
		}
		// Parent
		else if ($this->input->post('variable_name') == 'parent_id') {
			// Fix parent id
			$parent_id = $this->input->post('variable_data');
			if ($parent_id == 'null') {
				$parent_id = NULL;
			}

			$update_status = $this->category->update_parent($this->input->post('id'), $parent_id, $this->user_info->get_id());
			if ($update_status === TRUE) {
				$json_return['success'] = TRUE;
			} else if ($update_status === $this->category->ERROR_DB) {
				add_error_json('Could not update the category\'s parent', $json_return);
			} else if ($update_status === $this->category->ERROR_NO_PARENT_EXISTS) {
				add_error_json('That parent doesn\'t belong to you...', $json_return);
			} else {
				add_error_json('Unknown error', $json_return);
			}
		}

		echo json_encode($json_return);
	}

	/**
	 * Returns a category by id, used by ajax only
	 * @return prints a json category object
	 */ 
	public function get_by_id() {
		if ($this->input->post('ajax') === FALSE) {
			return;
		}

		$json_return['success'] = FALSE;

		$category_row = $this->category->get_by_id($this->input->post('id'), $this->user_info->get_id());
		if ($category_row === FALSE) {
			echo json_encode($json_return);
			return;
		}
	
		$this->_set_category($category_row, $category);

		$json_return['success'] = TRUE;
		$json_return['category'] = $category;

		echo json_encode($json_return);
	}

	/**
	 * Returns a category by name, only used with ajax
	 * @return prints a json category object
	 */
	public function get_by_name() {
		if ($this->input->post('ajax') === FALSE) {
			return;
		}

		$json_return['success'] = FALSE;

		$category_row = $this->category->get_by_name($this->input->post('category_name'), $this->user_info->get_id());
		if ($category_row === FALSE) {
			echo json_encode($json_return);
			return;
		}

		$this->_set_category($category_row, $category);
		
		$json_return['success'] = TRUE;
		$json_return['category'] = $category;

		echo json_encode($json_return);
	}

	/**
	 * Returns all the categories with the specified parent, only used with ajax
	 * @return prints all the categories as json
	 */ 
	public function get_all_by_parent() {
		if ($this->input->post('ajax') === FALSE) {
			return;
		}

		$json_return['success'] = FALSE;

		$categories = $this->category->get_all_by_parent($this->input->post('parent_id'), $this->user_info->get_id());
		if ($categories === FALSE) {
			echo json_encode($json_return);
			return;
		}

		$json_return['categories'] = $categories;
		$json_return['success'] = TRUE;
		echo json_encode($json_return);
	}

	/**
	 * Returns categories that are like the name specified
	 * @return json object with all the names
	 */ 
	public function get_like_name() {
		$categories = $this->category->get_like_name($this->input->post('term'), $this->user_info->get_id());

		$json_return = array();

		// Don't add children of the category we are trying to remove...
		if ($this->input->post('delete_id') !== FALSE) {
			$children = $this->category->get_children_of($this->input->post('delete_id'), $this->user_info->get_id(), true);

			foreach ($categories as $category) {
				if (!isset($children[$category['id']])) {
					$json_return[]['value'] = $category['name'];
				}
			}
		} else {
			foreach ($categories as $category) {
				$json_return[]['value'] = $category['name'];
			}
		}

		echo json_encode($json_return);
	}

	/**
	 * Deletes the category and moves its items and sub categories to another category
	 * @return json object with all error/success messages
	 */
	public function delete() {
		if ($this->input->post('ajax') === FALSE) {
			return;
		}

		$json_return['success'] = FALSE;


		// Validate
		$this->load->library('form_validation');
		$this->form_validation->set_rules('move_to_name', 'Delete Name', 'trim|required|min_length[3]|max_length[50]');
		if($this->form_validation->run() === FALSE) {
			get_errors($json_return);
			echo json_encode($json_return);
			return;
		}

		// Check so that the move to and delete exists
		$move_to_category = $this->category->get_by_name($this->input->post('move_to_name'), $this->user_info->get_id());
		if ($move_to_category === FALSE || !isset($move_to_category->id)) {
			add_error_json('There doesn\'t exist a category with that name.', $json_return);
			echo json_encode($json_return);
			return;
		}

		// Check so that we are not moving to the same category...
		if ($move_to_category->id == $this->input->post('delete_id')) {
			add_error_json('Cannot move to the same category.', $json_return);
			echo json_encode($json_return);
			return;
		}

		// Check so that we don't move to any of the categories children
		$children = $this->category->get_children_of($this->input->post('delete_id'), $this->user_info->get_id());
		if (isset($children[$move_to_category->id])) {
			add_error_json('Cannot move to a child category.', $json_return);
			echo json_encode($json_return);
			return;
		}

		// Move all items and sub categories
		$move_items_ok = $this->item->move_items($this->input->post('delete_id'), $move_to_category->id);
		if ($move_items_ok === $this->item->ERROR_DB) {
			add_error_json('Error while moving items to another parent, aborting. Please contact an administrator!', $json_return);
			echo json_encode($json_return);
			return;
		}

		// Change parents for sub categories
		$change_parents_ok = $this->category->change_parents($this->input->post('delete_id'), $move_to_category->id, $this->user_info->get_id());
		if ($change_parents_ok === $this->category->ERROR_DB) {
			add_error_json('Error while changing parents, aborting. Please contact an administrator!', $json_return);
			echo json_encode($json_return);
			return;
		}

		// Delete
		$delete_ok = $this->category->delete($this->input->post('delete_id'), $this->user_info->get_id());
		if ($delete_ok === $this->category->ERROR_DB) {
			add_error_json('Error deleting category, aborting. Please contact administrator!', $json_return);
			echo json_encode($json_return);
			return;
		}

		set_success_json('Successfully deleted category ' . $this->input->post('delete_name'), $json_return);
		$json_return['success'] = true;
		$json_return['move_to_id'] = $move_to_category->id;
		echo json_encode($json_return);
	}

	/**
	 * Sets the category information for json
	 * @param $category_row the row to get the information from
	 * @param $category reference to the category
	 */
	private function _set_category(&$category_row, &$category) {
		// Get id of the element that is alphabetically just before..
		// This is used for the ajax update, as to where the new category should
		// be inserted. If no element is found, it should be inserted directly after
		// its parent
		$before_id = $this->category->get_category_before($category_row->name, $category_row->parent_id, $this->user_info->get_id());
		if ($before_id !== FALSE) {
			$category['before_id'] = $before_id;
		} else {
			// It is first, if parent is NULL then it's first, else it just after its parent
			if ($category_row->parent_id !== NULL) {
				$category['before_id'] = $category_row->parent_id;
			} else {
				$category['before_id'] = 'FIRST';
			}
		}

		$category['name'] = $category_row->name;
		$category['id'] = $category_row->id;
		$category['parent_id'] = $category_row->parent_id;
	}
}
