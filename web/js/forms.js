// Checks so that the default values aren't still there when submitting
formSubmit = function() {
	$("input").each(function(index) {
		var defaultValue = $(this).prop("alt");
		var value = $(this).prop("value");
		if (defaultValue == value) {
			$(this).prop("value", "");
		}
	});

	return true;
}

// Set default style if the input is default and set default objects (if the user pressed submit)
inputLoad = function($input) {
	var defaultValue = $input.prop("alt");
	var value = $input.prop("value");

	// Re-set the default values if the fields are empty
	if (defaultValue != "" && value == "") {
		$input.prop("value", defaultValue);
		value = defaultValue;

		// Unencrypt if the type is password
		if ($input.prop("placeholder") == "password") {
			$input.prop("type", "text");
		}
	}
	
	// Colorize the input input field
	if (defaultValue == value) {
		$input.addClass("form_default");
	}
}

// Reset the fields in the form
formReset = function($formId, $focusId) {
	$('#' + $formId).find("input").not(':button, :submit, :reset, [type="hidden"]').each(function() {
		$(this).val('');

		if ($focusId !== undefined) {
			if ($focusId != $(this).prop('id')) {
				inputLoad($(this));
			} else {
				$(this).focus();
			}
		} else {
			inputLoad($(this));
		}
	});
}
	
$(document).ready(function() {
	// Set the default values
	$("input").each(function() {
		// Always clear the password fields
		if ($(this).prop("placeholder") == "password") {
			$(this).prop("value", "");
		}

 		inputLoad($(this));
	});

	// Clear the field if the default value is set
	$("input").live('focusin', function() {
		var defaultValue = $(this).prop("alt");
		var value = $(this).prop("value");
		if (defaultValue != "" && defaultValue == value) {
			$(this).prop("value", "");

			// Also 'encrypt' if the type is password
			if ($(this).prop("placeholder") == "password") {
				$(this).prop("type", "password");
			}

			// Use regular color on the form
			$(this).removeClass("form_default");
		}
	});

	// Restore the field if the default value is set and no input has been done
	$("input").live('focusout', function() {
		inputLoad($(this));
	});

	// Be sure that the default values aren't still there when submitting
	$("form").live('submit', formSubmit);
});

// Create buttons
$(document).ready(function() {
	$('.button').button();
	$('input[type="submit"]').button();
});
