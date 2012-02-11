<div id="categories_display">
<script type="text/javascript">
// ----------------------------------
// 				Sortable
// ----------------------------------
$(document).ready(function() {
	// Collapse if expanded
	$('#category_table').bind('sortableextendedstart', function(event, ui) {
		$.gSortCanceled = false;
		if (ui.item.find('.collapsible').length == 1) {
			collapseCategory(ui.item.prop('id'), event);
		}

		// Trash can
		$('#trash-can').stop().fadeTo(TRASH_FADE_TIME, TRASH_OPACITY_SORT).data('state', TRASH_STATE_SORT);
	});

	$('#category_table').bind('sortableextendedstop', function(event, ui) {
		// Trash can
		$('#trash-can').stop().fadeTo(TRASH_FADE_TIME, TRASH_OPACITY_DEFAULT).data('state', TRASH_STATE_DEFULT);
		

		// The element wasn't dropped onto anything and it was canceled
		if ($.gDroppedOnto === false && $.gSortCanceled === true) {
			updatePosition(ui.item.prop('id'), false, false);
		}
		else if ($.gDroppedOnto === false) {
			var $currentCategory = ui.item;
			var $prevCategory = $currentCategory.prev(':visible');
			var $nextCategory = $currentCategory.next(':visible');
			
			// Fix if the next and prev wasn't visible
			if ($nextCategory.length != 1) {
				$nextCategory = $currentCategory.nextUntil(':visible').last().next();
			}
			if ($prevCategory.length != 1) {
				$prevCategory = $currentCategory.prevUntil(':visible').last().prev();
			}


			var $message = 'Curr: (' + $currentCategory.prop('id') + ', ' + $currentCategory.data('parent_id') + ')<br />';
			if ($nextCategory.length != 1) {
				$message += 'Next: (' + $nextCategory.prop('id') + ', ' + $nextCategory.data('parent_id') + ')<br />';
			}
			if ($prevCategory.length != 1) {
				$message += 'Prev: (' + $prevCategory.prop('id') + ', ' + $prevCategory.data('parent_id') + ')<br />';
			}

			// Chose the same as the next category
			if ($nextCategory.length == 1) {
				// If the same parent, just update position instead of changing parent...
				if ($currentCategory.data('parent_id') == $nextCategory.data('parent_id')) {
					updatePosition($currentCategory.prop('id'), false, false);
				} else {
					updateParent($currentCategory.prop('id'), $nextCategory.data('parent_id'));
				}
			} 
			// Else there is no category after, set the parent to null
			else {
				if ($currentCategory.data('parent_id') == 'null') {
					updatePosition($currentCategory.prop('id'), false, false);
				} else {
					updateParent($currentCategory.prop('id'), 'null');
				}
			}
		}
	});

	$('#category_table').bind('sortableextendedchange', function(event, ui) {
		fixOddEvenRows();

		// Update the margin of the placeholder
		var $placeholder = ui.placeholder;
		var $nextCategory = $placeholder.next(':visible');
		if ($nextCategory.length != 1) {
			$nextCategory = $placeholder.nextUntil(':visible').last().next();
		}

		if ($nextCategory.length != 1) {
			$placeholder.css('marginLeft', '0');
		}
		else {
			$placeholder.css('marginLeft', $nextCategory.css('marginLeft'));
		}
	});

	$('#category_table').bind('sortableextendedout', function(event, ui) {
		fixOddEvenRows();
	});
	
	$('#category_table').bind('sortableextendedover', function(event, ui) {
		fixOddEvenRows();
	});

	// Cancel the sorting if dropped when it wasn't over the sorting list
	$('#category_table').bind('sortableextendedbeforestop', function(event, ui) {
		if ($('.ui-sortable-placeholder').css('display') == 'none') {
			$.gSortCanceled = true;
		}
		fixOddEvenRows();
	});
});

// ----------------------------------
// 				Trash Can
// ----------------------------------
var TRASH_OPACITY_SORT = 1.0;
var TRASH_OPACITY_HOVER = 0.6;
var TRASH_OPACITY_DEFAULT = 0.25;
var TRASH_FADE_TIME = 'slow';
var TRASH_STATE_SORT = 'sort';
var TRASH_STATE_HOVER = 'hover';
var TRASH_STATE_DEFULT = 'default';

$(document).ready(function() {
	// Set the trash can to default state in the beginning
	var $trashCan = $('#trash-can');
	$trashCan.css('opacity', TRASH_OPACITY_DEFAULT).data('state', TRASH_STATE_DEFULT);

	// Subscibe to page changed
	$trashCan.addClass('subscriber_pageChanged');
	$trashCan.bind('event.pageChanged', function() {
		trashCanUpdatePos();
	});

	$.gMousePos = new Object;
	$.gMousePos.x = 0;
	$.gMousePos.y = 0;

	// Keep the trash can in the in the sorting area, i.e. not above or below
	$(document).mousemove(function(event) {
		$.gMousePos.x = event.pageX;
		$.gMousePos.y = event.pageY;

		trashCanUpdatePos();
	});

	$trashCan.droppable({
		hoverClass: 'hover',
		tolerance: 'pointer',
		drop: function(event, ui) {
			$.gDroppedOnto = 'trash';
			$('#delete_name').html(ui.draggable.find('#category_name').html());
			$('#delete_id').val($.gSortId);
			$('#delete_dialog').dialog('open');
		}
	});

	// Hide trash can if there are categories
	if ($('#category_table').children().length === 0) {
		$('#trash-can').hide();
	}

	// Autocomplete for category
	$('#move_to_name').autocomplete({
		minLength: 3,
		delay: 10,
		source: function(request, response) {
			request.delete_id = $('#delete_id').val();
			$.ajax({
				url: '<?php echo site_url('categories/get_like_name'); ?>',
				data: request,
				dataType: 'json',
				type: 'post',
				success: response
			});
		}
	});

	// Dialog for deleting category
	$('#delete_dialog').dialog({
		autoOpen: false,
		modal: true,
		buttons: {
			'Delete and Move': function() {
				$('#delete_form').submit();
			},
			'Close': function(event) {
				$(this).dialog('close');
				triggerEvent('pageChanged');
			}
		},
		close: function(event, ui) {
			$('#dialog_messages').html('').css('display', 'none');
			formReset('delete_form');
		}
	});

	// Submit function
	$('#delete_form').submit(function(event) {
		$('#move_to_name').autocomplete('close');

		var $formData = {
			ajax: true,
			delete_name: $('#delete_name').html(),
			delete_id: $('#delete_id').val(),
			move_to_name: $('#move_to_name').val()
		};

		$.ajax({
			url: $('#delete_form').prop('action'),
			type: 'POST',
			data: $formData,
			dataType: 'json',
			success: function($json) {
				if ($json === null || $json.success === undefined) {
					addMessageTo('Return message is null, contact administrator', $('#dialog_messages'), 'error');
					return;
				}

				if ($json.success === true) {
					deleteCategory($formData.delete_id, $json['move_to_id']);
					$('#delete_dialog').dialog('close');
					triggerEvent('pageChanged');
					displayAjaxReturnMessages($json);
				} else {
					displayAjaxReturnMessages($json, $('#dialog_messages'));
				}
			},
			error: function() {
				addMessageTo('Error message, contact an administrator! Please state what went wrong.', $('#dialog_messages'), 'error');
			}
		});

		return false;
	});
});

/**
 * Updates the trash can position. The new position is calculated
 * from the last mouse position and the current category_table
 * position.
 */ 
function trashCanUpdatePos() {
	// Don't move while the dialog is up
	if ($('#delete_dialog:visible').length == 1) {
		return;
	}

	var $categoryTable = $('#category_table');
	var $trashCan = $('#trash-can');
	var $categoryOffset = $categoryTable.offset();
	$categoryOffset.right = $categoryOffset.left + $categoryTable.width();
	$categoryOffset.bottom = $categoryOffset.top + $categoryTable.height();
	var $topMin = $categoryOffset.top;
	var $topMax = $categoryOffset.bottom - $trashCan.height();
	var $trashOffset = {
		left: $categoryOffset.right + 32,
		top: $.gMousePos.y - ($trashCan.height() * 0.5)
	};

	// Fix the top if out of bounds
	if ($trashOffset.top < $topMin) {
		$trashOffset.top = $topMin;
	}
	else if ($trashOffset.top > $topMax) {
		// Only check top max if max is larger than min. We don't want to push up
		// the trash can causing flickering.
		if ($topMax < $topMin) {
			$trashOffset.top = $topMin;
		} else {
			$trashOffset.top = $topMax;
		}
	}

	// Set position
	$trashCan.offset($trashOffset);

	// Also update the droppable position, if we have 'travelled' to far
	if ($.gSorting === true && ($.gTrashUpdatePosY === undefined || $.gTrashUpdatePosY - $trashOppset.top > $trashCan.height * 0.5)) {
		$categoryTable.sortableExtended('updateDroppables', event);
	}

	// Update opacity if we're not sorting
	if ($.gSorting === false) {
		var $inside = mouseInside($categoryOffset, $.gMousePos.x, $.gMousePos.y);

		if ($inside && $trashCan.data('state') == TRASH_STATE_DEFULT) {
			$trashCan.stop().fadeTo(TRASH_FADE_TIME, TRASH_OPACITY_HOVER).data('state', TRASH_STATE_HOVER);
		} else if (!$inside && $trashCan.data('state') == TRASH_STATE_HOVER) {
			$trashCan.stop().fadeTo(TRASH_FADE_TIME, TRASH_OPACITY_DEFAULT).data('state', TRASH_STATE_DEFULT);
		}
	}
}

// ----------------------------------
// 		Expandable / Collapsible
// ----------------------------------
$(document).ready(function() {
	$('.expandable').live('click', function(event) {
		expandCategory($(this).parent().prop('id'), event);
	});
	$('.collapsible').live('click', function(event) {
		collapseCategory($(this).parent().prop('id'), event);
	});
});

/**
 * Expands the specified category
 * @param $id id of the category to expand
 * @param event event information
 */ 
function expandCategory($id, event) {
	$('.parent_' + $id).css('display', 'inline');
	
	fixOddEvenRows();
	fixSortableWidth();
	
	// Change parent to collapsible class
	$('#' + $id).find('.expandable').each(function() {
		$(this).removeClass('expandable');
		$(this).addClass('collapsible');
	});

	if (event !== undefined) {
		triggerEvent('pageChanged');
	}
}

/**
 * Collapses the specified category
 * @param $id id of the category to collapse
 */ 
function collapseCategory($id, event) {
	// Hide children
	$('.parent_' + $id).each(function() {
		$(this).css('display', 'none');

		// Hide it's children
		collapseCategory($(this).prop('id'), event);
	});

	// Change to expandable class
	$('#' + $id).find('.collapsible').each(function() {
		$(this).removeClass('collapsible');
		$(this).addClass('expandable');
	});

	fixOddEvenRows();

	if (event !== undefined) {
		triggerEvent('pageChanged');
	}
}

// ----------------------------------
// 				Droppable
// ----------------------------------
jQuery(function() {
	jQuery.fn.droppableSetup = function droppableSetup() {
		this.each(function() {
			var $expandCategoryTimeout;

			var EXPAND_TIMEOUT = 800;
			
			$(this).droppable({
				hoverClass: 'hovered',
				tolerance: 'pointer',
				drop: function(event, ui) {
					clearTimeout($expandCategoryTimeout);

					$.gDroppedOnto = $(this).prop('id');

					// If the element was dropped onto this
					if ($.gSortId !== false) {
						updateParent($.gSortId, $.gDroppedOnto);
						expandCategory($.gDroppedOnto, event);
					}
				},
				// Set the element as droppable after EXPAND_TIMEOUT
				over: function(event, ui) {
					// Only expand the category if it can be expanded
					var $expandId = $(this).prop('id');
					if ($(this).find('.expandable').length == 1) {
						$expandCategoryTimeout = setTimeout(function() {expandCategory($expandId, event)}, EXPAND_TIMEOUT);
					}

					// hide placeholder
					$('.ui-sortable-placeholder').css('visibility', 'hidden');
				},
				// Remove the timing function if it was not set
				out: function(event, ui) {
					clearTimeout($expandCategoryTimeout);
					$.gDroppedOnto = false;

					// Show placeholder
					$('.ui-sortable-placeholder').css('visibility', 'visible');
				}
			});
		});
		return this;
	};
});

$.gDroppedOnto = false;

$(document).ready( function() {
	// Should only be able to drop onto it once it has been expanded
	$('.droppable').droppableSetup();
});

// ----------------------------------
// 				Add Row
// ----------------------------------
function newCategory($name) {
	var formData = {
		category_name: $name,
		ajax: true
	};

	$.ajax({
		url: "<?php echo site_url('categories/get_by_name'); ?>",
		type: 'POST',
		data: formData,
		dataType: 'json',
		success: function($json) {
			if ($json === null && $json.success === undefined) {
				addMessage('Return messages is null, contact administrator', 'error');
				return;
			}

			// Skip all errors
			if ($json.success === true && $json.category !== null) {
				insertCategory($json.category, false);
			}

			displayAjaxReturnMessages($json);
		}
	});
}

function prependRow($category) {
	// Prepend to the table if elements already exists
	if ($('#category_table').children().length !== 0) {
		var $firstRow = $('#category_table > div.row').first();

		$html = getCategoryHtml($category.id, $category.name, $category.parent_id, 'odd', 0, false);
		$firstRow.before($html);
	} else {
		var $tr_html = getCategoryHtml($category.id, $category.name, $category.parent_id, 'odd', 0, false);
		$('#category_table').append($tr_html);
	}
}

/**
 * Inserts a row in the table. Automatically updates the color of the other rows.
 * @param $category object with all category information
 * @param $hide if the new category should be hidden or not
 */ 
function insertCategory($category, $hide) {
	if ($category.before_id == 'FIRST') {
		prependRow($category);
	} else {
		var $id_found = false;
		var $before_element = null;
		$('div.row').each(function() {
			// Locate the right place to insert the category in (alphabetically and right parent)
			if(!$id_found && $(this).prop('id') == $category.before_id) {
				$id_found = true;
				$before_element = $(this);
			} else {
				return;
 			}
		});

		// Insert the new category
		if ($before_element !== null) {
			// Get the indentation from the before element
			var $indent = calculateIndentIndex(parseInt($before_element.css('marginLeft')));

			// Add more indentation if the before element is the parent, happens when the inserted
			// first amongst the childs
			if ($before_element.prop('id') == $category.parent_id) {
				$indent++;
			}
			
			var $tr_html = getCategoryHtml($category.id, $category.name, $category.parent_id, 'even', $indent, $hide);
			$before_element.after($tr_html);
		}
	}

	// Add it as droppable and set parent
	$('#' + $category.id).droppableSetup();
	var $parentIdFixed = $category.parent_id;
	if ($parentIdFixed === null) {
		$parentIdFixed = 'null';
	}
	setParent($category.id, $parentIdFixed);

	fixOddEvenRows();

	// Show trash (if it was hidden), and update position
	$('#trash-can').show();
}

/**
 * Updates the parent of the specified category
 * @param $id id of the category to update
 * @param $newParentId id of the new parent
 */ 
function updateParent($id, $newParentId) {
	// Don't update if the category already has that parent
	if ($('#' + $id).data('parent_id') == $newParentId) {
		return;
	}

	var $formData =  {
		id: $id,
		ajax: true,
		variable_name: 'parent_id',
		variable_data: $newParentId
	};

	$.ajax({
		url: '<?php echo site_url('categories/update'); ?>',
		type: 'POST',
		data: $formData,
		dataType: 'json',
		success: function($json) {
			if ($json === null || $json.success === undefined) {
				addMessage('Return message is null, contact administrator', 'error');
				return;
			}

			// Always update the position, even if it failed
			updatePosition($id, false, $json.success);

			displayAjaxReturnMessages($json);
		}
	});
}

/**
 * Updates the children position.
 * @param $id id of the parent whos children we want to update
 */
function updateChildrenPositions($id) {
	// Find first category with the parent
	var $firstChild = $('.parent_' + $id + ':first');

	// Save all next categories until a visible category. All categories that are
	// hidden should be the child of the parent category
	var $allChildren = $firstChild.nextUntil('*:visible');


	// Move the categories after the parent
	$('#' + $id).after($allChildren).after($firstChild);


	// Fix indentation of the children
	var $parentIndent = calculateIndentIndex(parseInt($('#' + $id).css('marginLeft')));
	var $firstChildIndent = calculateIndentIndex(parseInt($firstChild.css('marginLeft')));

	// The difference should always be 1
	var $diffIndent = ($parentIndent + 1) - $firstChildIndent;
	var $diffMargin = calculateMargin($diffIndent);

	// Apply the difference in margin to all children
	$firstChild.css('marginLeft', $diffMargin + calculateMargin($firstChildIndent));
	$allChildren.each(function() {
		var $childMargin = parseInt($(this).css('marginLeft'));
		$(this).css('marginLeft', $childMargin + $diffMargin);
	});
}

/**
 * Updates the position of category
 * @param $id id of the category to update
 * @param $hide if the category should be hidden or not
 * @param $updateChildren if children should be update or not
 */
function updatePosition($id, $hide, $updateChildren) {
	var $oldPos = $('#' + $id);

	var $formData = {
		id: $id,
		ajax: true
	};

	$.ajax({
		url:'<?php echo site_url('categories/get_by_id'); ?>',
		type: 'POST',
		data: $formData,
		dataType: 'json',
		success: function($json) {
			if ($json === null && $json.success === undefined) {
				addMessage('Return messages is null, contact administrator', 'error');
				return;
			}

			if ($json.success) {
				// Remove the old position
				$oldPos.remove();

				insertCategory($json.category, $hide);

				if ($updateChildren) {
					updateChildrenPositions($id);
				}
			}

			displayAjaxReturnMessages($json);
		}
	});
}

/**
 * Deletes a category and moves its children to the new parent
 * @param $deleteId id of the deleted category
 * @param $newParent id of the new parent for the children
 */ 
function deleteCategory($deleteId, $newParent) {
	$('#' + $deleteId).remove();

	// Check if we should hide or show the first children
	$hide = true;
	if ($('#' + $newParent).find('.collapsible').length == 1) {
		$hide = false;
	}

	// Update all the children
	$('.parent_' + $deleteId).each(function() {
		updatePosition($(this).prop('id'), $hide);
	});

	// Hide trash-can if no categories are left
	if ($('#category_table').children().length === 0) {
		$('#trash-can').hide();
	}
}

/**
 * Convert indentation index to margin in pixels
 * @param $indent_index the indentation index
 * @return left margin for the indentation
 */
function calculateMargin($indent_index) {
	return $indent_index * 30;
}

/**
 * Convert left margin in pixels to the indentation index
 * @param $left_margin the margin to the left
 * @return indentation index
 */ 
function calculateIndentIndex($left_margin) {
	return $left_margin / 30;
}

/**
 * Returns a html row (tr) with proper indent etc. The row is always set as expandable
 * @param $category_id id of the category
 * @param $category_name name of the category
 * @param $parent_id id of the parent
 * @param $tr_class which class the tr should have (odd/even)
 * @param $indent the indentation number, 0 is for categories with no parent.
 * @param $hide if the element should be displayed or not
 * @return html row (tr) with proper indent.
 */ 
function getCategoryHtml($category_id, $category_name, $parent_id, $tr_class, $indent, $hide) {
	var $left_margin = calculateMargin($indent);
	var $display = 'display: ';
	if ($hide) {
		$display += 'none;';
	} else {
		$display += 'inline;';
	}

	var $htmlRow = '<div id="'+ $category_id + '" class="parent_' + $parent_id + ' row droppable ' + $tr_class + '" style="margin-left: ' + $left_margin + 'px;' + $display + '"><div class="expandable table_left"></div>' +
		'<div id="category_name" contenteditable="true" class="table_right">' + $category_name + '</div>' +
		'</div>';
	return $htmlRow;
}

/**
 * Set the parent of a category
 * @param $id id of the category
 * @param $parentId id of the parent
 */ 
function setParent($id, $parentId) {
	$('#' + $id).data('parent_id', $parentId);
}

/**
 * A success callback, called when ajax editing was successfull
 * @param $id id of the successfully edited object
 * @param $field the field that was edited
 */ 
function success_edit_callback($id, $field) {
	updatePosition($id, false, false);
}

// ----------------------------------
// 			Fix odd/even rows
// ----------------------------------
/**
 * Fix all the rows so that they actually have odd/even classes
 */ 
function fixOddEvenRows() {
	$('div.row:visible:odd').each(function() {
		set_class_odd_even($(this), 'odd');
	});
	$('div.row:visible:even').each(function() {
		set_class_odd_even($(this), 'even');
	});
}
/**
 * Switches the odd/even class on the specified category
 * @param $element the category to switch the odd/even class on
 */ 
function switch_class_odd_even($element) {
	if ($element.hasClass('odd')) {
		$element.removeClass('odd');
		$element.addClass('even');
	} else {
		$element.removeClass('even');
		$element.addClass('odd');
	}
}

/**
 * Returns the odd/even class of the element
 * @param $element the element to get the odd/even class from
 * @return odd,even,none depending if the element has the class odd, even, or none respecively.
 */
function get_class_odd_even($element) {
	if ($element.hasClass('odd')) {
		return 'odd';
	} else if ($element.hasClass('even')) {
		return 'even';
	} else {
		return 'none';
	}
}

/**
 * Sets an odd/even class to the element
 * @param $element the element to set the class on
 * @param $class_type the odd/even class to set
 */
function set_class_odd_even($element, $class_type) {
	if ($class_type == 'odd') {
		$element.removeClass('even');
		$element.addClass('odd');
	} else if ($class_type == 'even') {
		$element.removeClass('odd');
		$element.addClass('even');
	}
}

// ---- BEGIN OUTPUT ----
// document.write('<?php echo form_open('categories/shit'); ?>');
// document.write('<div><input id="category_search" name="category_search" alt="Search Category" value="Search Category" type="text" /><input type="hidden" id="category_id" name="category_search_id" /></div>');
// document.write('<?php echo form_close() ?>');
document.write('<?php echo form_open('categories/update'); ?>');
document.write('<h3>Categories</h3>');
document.write('<div id="trash-can"></div>');
document.write('<div class="sortable table" id="category_table">');
<?php
/**
 * Prints all the categories, recursive function.
 * @param categories array with all the categories in it
 * @param parent_id the subcategories to print, pass NULL to print all categories
 * @param row the number of the current row, acts as a counter to print every other
 * 	row with different classes
 * @param indent the number of indetations we should make, always starts at 0
 * @param hide if the categories should be hidden or not, defaults to false
 * @param set_parents javascript to set the parents of all categories, in data field
 */ 
function print_categories(&$categories, $parent_id = NULL, &$row = 0, $indent = 0, $hide = 'false', &$set_parents = '') {
	if (!isset($categories) || !isset($categories[$parent_id])) {
		return;
	}

	foreach($categories[$parent_id] as $category) {
		$class='';
		if ($hide === 'false') {
			if ($row % 2 == 0) {
				$class='even';
			} else {
				$class='odd';
			}
			$row++;
		}

		// Fix for NULL parent
		$parent_print = $parent_id;
		if ($parent_id === NULL) {
			$parent_print = '\'null\'';
		}

		echo "document.write(getCategoryHtml(" . $category['id'] . ", '" . $category['name'] . "', " . $parent_print . ", '" . $class . "', " . $indent . ", " . $hide . "));\n";

		$set_parents .= 'setParent(' . $category['id'] . ', ' . $parent_print . ");\n";

		// Print subcategories, always hide children on default
		print_categories($categories, $category['id'], $row, $indent+1, 'true', $set_parents);
	}

	if ($parent_id === NULL) {
		echo "$(document).ready( function() {\n";
		echo $set_parents;
		echo "});\n";
	}
}

print_categories($categories);
?>

document.write('</div>');
document.write('<?php echo form_close(); ?>');
// ---- END OUTPUT ----
</script>
<div id="delete_dialog" title="Delete Category">
<div id="dialog_messages" style="margin-bottom: 0.5em;"></div>
Delete <strong id="delete_name"></strong> and move its items and sub categories to:<br /><br />
<?php
	echo form_open('categories/delete', 'id="delete_form"');

	$input['type'] = 'hidden';
	$input['name'] = 'delete_id';
	$input['id'] = $input['name'];
	echo form_input($input);
	
	$input['type'] = 'text';
	$input['name'] = 'move_to_name';
	$input['id'] = $input['name'];
	$input['alt'] = 'Category Name';
	$input['value'] = set_value($input['name'], $input['alt']);
	echo form_input($input);

	echo form_close();
?>
<span class="small">It's not possible move the items to the top category, this would make all items category-less.</span>
</div>
</div>
