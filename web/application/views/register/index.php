<div id="middle_form-wrapper">
<div id="middle_form" class="fine_border">
<h1 style="text-align: center;"><?php echo $register_type; ?> Registration</h1>
<?php
	echo form_open('register/index/' . $register_type);
?>
<fieldset>
	<legend>Personal Information</legend>
	<?php
	$input['class'] = 'middle_form big';
	$input['type'] = 'text';
	$input['maxlength'] = '25';

	$input['name'] = 'first_name';
	$input['alt'] = 'First Name';
	$input['value'] = set_value('first_name', $input['alt']);
	echo form_input($input);
	echo '<br />';

	$input['name'] = 'last_name';
	$input['alt'] = 'Last Name';
	$input['value'] = set_value('last_name', $input['alt']);
	echo form_input($input);
	echo '<br />';

	$input['maxlength'] = '50';
	$input['name'] = 'email';
	$input['alt'] = 'Email Address';
	$input['value'] = set_value('email', $input['alt']);
	echo form_input($input);
	echo '<br />';
	?>

</fieldset>

<fieldset>
	<legend>Login Information</legend>
	<?php
	$input['maxlength'] = '25';
	$input['name'] = 'username';
	$input['alt'] = 'Username';
	$input['value'] = set_value('username', $input['alt']);
	echo form_input($input);
	echo '<br />';

	$input['maxlength'] = '50';
	$input['name'] = 'password';
	$input['placeholder'] = 'password';
	$input['alt'] = 'Password';
	$input['value'] = set_value('password', $input['alt']);
	echo form_input($input);
	echo '<br />';
	
	$input['maxlength'] = '50';
	$input['alt'] = 'Confirm Password';
	$input['placeholder'] = 'password';
	$input['name'] = 'password_confirm';
	$input['value'] = set_value('password_confirm', $input['alt']);
	echo form_input($input);
	echo '<br />';
	?>
	
</fieldset>

	<div style="text-align: center">
	
		<?php echo form_submit('register', 'Create Account', 'class="big"'); ?>
	</div>
<?php
	echo form_close();
?>
</div>
</div>
<script type="text/javascript">
	var $messages = $('#messages');
	$('h1').after($messages);
	$('#content').remove($messages);
</script>
