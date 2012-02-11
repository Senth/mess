$(document).ready(function() {
	// ----------------------------------
	// 				Editing
	// ----------------------------------
	// Save the field before starting to edit it
	$('*[contenteditable="true"]').live('focusin', function(event) {
		$.gFormEditSave = $(this).html();
		$.gFormEditing = true;
		$.gFormEditElement = this;

		var $element = this;

		// Select all
		event.preventDefault();
		window.setTimeout(function() {
			// All browsers
			if (window.getSelection && document.createRange) {
				var $selection = window.getSelection();
				var $rangeToSelect = document.createRange();
				$rangeToSelect.selectNodeContents($element);

				$selection.removeAllRanges();
				$selection.addRange($rangeToSelect);
			}
			// IE below 9
			else if (document.body.createTextRange) {
				var $rangeToSelect = document.body.createTextRange();
				rangeToSelect.moveToElementText($element);
				rangeToSelect.select();
			}
		}, 1);

		sortableDisable();
	});

	// Reenable sortable when the mouse leaves window
	$('*[contenteditable="true"]').live('mouseout', function() {
		sortableEnable();
	});

	// Disable sortable when the mouse enters the window we're editing
	$('*[contenteditable="true"]').live('mouseover', function() {
		if ($.gFormEditing === true && this == $.gFormEditElement) {
			sortableDisable();
		}
	});

	// Calls the form action if the content was edited
	$('*[contenteditable="true"]').live('focusout', function() {
		// Be sure to remove any <br>
		$(this).find('br').replaceWith('');
		if ($.gFormEditSave != $(this).html()) {
			var $form_action = $(this).parents('form').first().prop('action');
			var $id = $(this).parent().prop('id');

			// Note that it's the parent that holds the id whereas this's id
			// holds the variable name that is being edited

			// Update the element to the database
			if ($form_action !== undefined && $id !== undefined) {
				var $form_data = {
					id: $id,
					variable_name: $(this).prop('id'),
					variable_data: $(this).html(),
					ajax: true
				};

				var $formDefault = $.gFormEditSave;
				var $this = $(this);
				var $field = $(this).prop('id');

				$.ajax({
					url: $form_action,
					type: 'POST',
					data: $form_data,
					dataType: 'json',
					success: function($json) {
						if ($json === null && $json.success === undefined) {
							addMessage('Return messages is null, contact administrator', 'error');
							return;
						}

						// Call success callback
						if ($json.success === true && typeof success_edit_callback == 'function') {
							success_edit_callback($id, $field);
						}
						// Call error callback and set to default
						else if ($json.success === false) {
							$this.html($formDefault);
							if (typeof error_edit_callback == 'function') {
								error_edit_callback($id, $field);
							}
						}

						displayAjaxReturnMessages($json);

					}
				});
			}
		}
		$.gFormEditing = false;
		$.gFormEditElement = null;

		sortableEnable();
	});

	// Handle change events, escape = cancel and restore to default. Enter = end editing and apply
	$('*[contenteditable="true"]').live('keydown', function(event) {
		// Enter pressed, end editing and focusout (focusout applies changes automatically)
		if (event.keyCode == 13) {
			event.preventDefault();
			$(this).blur();
		}
		if (event.keyCode == 27) {
			$(this).html($.gFormEditSave);
			$(this).blur();
		}
	});

	// ----------------------------------
	// 				Sortable
	// ----------------------------------
	$.gSorting = false;

	$('.sortable').sortableExtended({
		helper: fixHelper,
		distance: 15,
		start: function(event, ui) {
			$.gSorting = true;
			$.gSortId = ui.item.prop('id');
			$.gSortWidth = ui.helper.width();
			ui.item.parent().width($.gSortWidth);
			ui.placeholder.css('visibility', 'visible');
		},
		stop: function(event, ui) {
			$.gSorting = false;
			$.gSortId = false;
			ui.item.parent().width('auto');
		}
	});

	$('*[contenteditable="true"]').live('click', function(event) {
		$(this).focus();
		event.preventDefault();
	});
});

// Extended sortable that fixes droppables when they are dynamically updated
(function ($, undefined) {
	$.widget('ui.sortableExtended', $.ui.sortable, {
		_init: function() {
			this.element.data("sortable", this.element.data('sortableExtended'));
			return $.ui.sortable.prototype._init.apply(this, arguments);
		},

		updateDroppables: function(event) {
			if ($.ui.ddmanager) {
				$.ui.ddmanager.prepareOffsets(this, event);
			}
		}
	});
})(jQuery);

function fixSortableWidth() {
	var $helper = $('.ui-sortable-helper');
	if ($helper !== undefined && $helper !== null) {
		var $sorter = $helper.parent();
		if ($sorter !== undefined && $sorter !== null) {
			$sorter.width('auto');
			// Sorter is smaller reset to default width
			if ($sorter.width() <= $.gSortWidth) {
				$sorter.width($.gSortWidth);
			}
			// Sorter is larger set a new width on the sorter
			else {
				$.gSortWidth = $sorter.width();
				$sorter.width($.gSortWidth);
				$helper.width($.gSortWidth);
			}
		}
	}
}

fixHelper = function(event, ui) {
	return $(ui).clone().width($(this).width()).fadeTo(100, 0.7);
}

function sortableDisable() {
	$('.sortable').sortableExtended('option', 'disabled', true);
}

function sortableEnable() {
	$('.sortable').sortableExtended('option', 'disabled', false);
}
