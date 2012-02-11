<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//--------------------- ERRORS ----------------------

/**
 * Error reporter helper
 * @param name unique name of the error
 * @param message message to display when either get_errors(), or get_error($name) is called
 */
function add_error($name, $message) {
	$CI =& get_instance();

	// Retrieve all current errors and append the new error
	$errors =& $CI->session->userdata('errors');
	$errors[$name] = $message;
	$CI->session->set_userdata('errors', $errors);
}

/**
 * Adds an error, not to the session, but to the json return array supplied.
 * @param message message to display
 * @param json the json return array (used in ajax)
 */ 
function add_error_json($message, &$json) {
	$error_message = '<p class="error">' . $message . "</p>\n";
	if (isset($json['error_messages'])) {
		$json['error_messages'] .= $error_message;
	} else {
		$json['error_messages'] = $error_message;
	}
}

/**
 * Retrieves a list of all the errors. These are each formatted into <p class="error">$message</p>
 * @param json array for json return values using ajax.
 * @return if json wasn't set it returns a list with all the errors
 */
function get_errors(&$json = NULL) {
	$CI =& get_instance();
	$errors =& $CI->session->userdata('errors');

	$return_messages = validation_errors('<p class="error">');

	if ($errors !== FALSE) {
		foreach ($errors as $message) {
			$return_messages .= '<p class="error">' . $message . "</p>\n";
		}
	}

	$CI->session->unset_userdata('errors');

	if ($json !== NULL) {
		if (isset($json['error_messages'])) {
			$json['error_messages'] .= $return_messages;
		} else {
			$json['error_messages'] = $return_messages;
		}
	} else {
		return $return_messages;
	}
}

/**
 * Retrieves the specified error if it exists. This is formatted into <p class="error">$message</p>.
 * It also removes the error from the list if you want to display all other errors together with #retrieve_error()
 * @param name the name of the error name to fetch.
 * @return the specified error message if the error exists.
 */ 
function get_error($name) {
	$CI =& get_instance();
	$errors =& $CI->session->userdata('errors');

	$return_message = '';

	if ($errors !== FALSE) {
		if (isset($errors[$name])) {
			$return_message = '<p class="error">' . $errors[$name] . "</p>\n";
			unset($errors[$name]);

			$CI->session->set_userdata('errors', $errors);
		}
	}

	return $return_message;
}
//--------------------- SUCCESS ----------------------
/**
 * Sets a success message
 * @param message the success message to be displayed once #get_success() is called
 */
function set_success($message) {
	$CI =& get_instance();
	$CI->session->set_userdata('success', $message);
}

/**
 * Sets a success message for json return, when using ajax
 * @param message the success message to be set
 * @param json json return array used in ajax calls
 */
function set_success_json($message, &$json) {
	$json['success_message'] = '<p class="success">' . $message . "</p>\n";
}

/**
 * Returns the success message
 * @return the current success message if there are one, else it returns an empty string
 */
function get_success() {
	$CI =& get_instance();

	$return_message = '';

	$success_message = $CI->session->userdata('success');

	if ($success_message !== FALSE) {
		$return_message = '<p class="success">' . $success_message . "</p>\n";
	}

	$CI->session->unset_userdata('success');

	return $return_message;
}
