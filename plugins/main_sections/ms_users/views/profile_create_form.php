<?php

require_once 'require/function_users.php';

function show_profile_create_form() {
	global $l;
	
	?>
	
	<div class="form-frame form-frame-create-profile">
		<h3><?php echo $l->g(1399) ?></h3>
		
		<?php
		
		$field_options = array('field_class' => 'big-label');
		
		echo open_form('create-profile', '#');
		
		show_form_field(array(), array(), 'input', 'name', $l->g(1396), $field_options);
		show_form_field(array(), array(), 'input', 'label', $l->g(1397), $field_options);
		show_form_field(array(), array(), 'select', 'duplicate_profile', $l->g(1398), array_merge($field_options, array(
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