<div id="new_category_div">
<h3>Create New Category</h3>
<?php
	$category_create_url = 'categories/create';
	echo form_open($category_create_url, 'id="create_form"');
	$input['class'] = '';
	$input['type'] = 'text';
	$input['name'] = 'category_name';
	$input['id'] = $input['name'];
	$input['maxlength'] = '50';
	$input['alt'] = 'Category Name';
	$input['value'] = set_value($input['name'], $input['alt']);
	
	echo form_input($input);
	echo form_submit('create_category', 'Create Category', 'id="create_category"');
	echo form_close();
?>

<script type="text/javascript">
$(document).ready(function() {
	$('#create_category').click(function() {
		formSubmit();

		var form_data = {
			category_name: $('#category_name').val(),
			ajax: true
		};

		$.ajax({
			url: '<?php echo site_url($category_create_url); ?>',
			type: 'POST',
			data: form_data,
			dataType: 'json',
			success: function($json) {
				if ($json === null && $json.success === undefined) {
					addMessage('Return messages is null, contact administrator', 'error');
					return;
				}

				if ($json.success === true) {
					newCategory(form_data.category_name);
				}

				displayAjaxReturnMessages($json);

			},
			error: function($jqXHR, $textStatus, $errorThrown) {
				alert($textStatus + ' ' + $errorThrown);
			}
		});

		formReset('create_form', 'category_name');

		return false;
	});
});
</script>
</div>
