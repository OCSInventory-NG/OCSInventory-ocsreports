<?php

require_once 'require/function_users.php';

function show_profile_create_form() {
	global $l;
	global $protectedPost;

	?>

		<h3><?php echo $l->g(1399) ?></h3>
		
		<?php
		
		$field_options = array('field_class' => 'big-label');
		
		echo open_form('create-profile', '#', '', 'form-horizontal');
		

		formGroup('text', 'name', $l->g(1396), '', '', $protectedPost['name']);
		formGroup('text', 'label', $l->g(1397), '', '', $protectedPost['label']);
		formGroup('select', 'duplicate_profile', $l->g(1398), '', '', $protectedPost['duplicate_profile'], '', get_profile_labels(), get_profile_labels());


	?>
		
		<div class="form-buttons">
			<input type="submit" value="<?php echo $l->g(1363) ?>"/>
			<input type="reset" value="<?php echo $l->g(1364) ?>"/>
		</div>
		
		<?php echo close_form() ?>
	
	<?php
}

?>