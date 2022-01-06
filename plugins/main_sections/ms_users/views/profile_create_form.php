<?php
/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */
require_once 'require/function_users.php';

function show_profile_create_form() {
	global $l;
	global $protectedPost;

	?>

		<h3><?php echo $l->g(1399) ?></h3>
		
		<?php
		
		$field_options = array('field_class' => 'big-label');
		
		echo open_form('create-profile', '#', '', 'form-horizontal');
		

		formGroup('text', 'name', $l->g(1396), '', '', $protectedPost['name'] ?? "");
		formGroup('text', 'label', $l->g(1397), '', '', $protectedPost['label'] ?? "");
		formGroup('select', 'duplicate_profile', $l->g(1398), '', '', $protectedPost['duplicate_profile'] ?? "admin", '', get_profile_labels(), get_profile_labels());


	?>

		<div class="row">
			<div class="col-md-12">
				<input type="submit"  class="btn btn-success" value="<?php echo $l->g(1363) ?>"/>
				<input type="reset" class="btn btn-danger" value="<?php echo $l->g(1364) ?>"/>
			</div>
		</div>
		
		<?php echo close_form() ?>
	
	<?php
}
?>