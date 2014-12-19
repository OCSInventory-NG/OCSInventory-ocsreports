<?php

require_once 'require/function_users.php';

function show_profile_create_form() {
	global $l;
	
	?>
	
	<div class="form-frame form-frame-create-profile">
		<h3>Create profile</h3>
		
		<?php
		
		$field_options = array('field_class' => 'big-label');
		
		echo open_form('create-profile', '#');
		
		// TODO translate
		show_form_field(array(), array(), 'input', 'name', 'Identifier (ex: admin)', $field_options);
		show_form_field(array(), array(), 'input', 'label', 'Display name (ex: Administrators)', $field_options);
		show_form_field(array(), array(), 'select', 'duplicate_profile', 'Copy profile data', array_merge($field_options, array(
			'options' => get_profile_labels()
		)));
		
		?>
		
		<div class="form-buttons">
			<input type="submit" value="<?php echo $l->g(1363) ?>"/>
			<input type="reset" value="<?php echo $l->g(1364) ?>"/>
		</div>
		
		<?php echo close_form() ?>
	</div>
	
	<?php
}

?>