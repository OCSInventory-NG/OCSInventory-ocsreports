<?php

function show_package_form($activate_info_url, $activate_frag_url) {
	global $l;

	?>
	
	<h3><?php echo $l->g(435) ?></h3>
	
	<div class="build-package-container">
		<div class="uploaded-packages"></div>
		<div class="uploading-packages"></div>
		
		<?php echo open_form('package-files', '#', 'enctype="multipart/form-data"') ?>
			<div class="form-frame form-column">
				<h4>Package archives</h4>
				
				<div class="field field-nolabel file-field">
					<?php show_form_input('packageFile', array(
						'type' => 'file',
						'attrs' => array(
							'accept' => 'application/zip,application/gzip,application/vnd.android.package-archive,.apk',
							'multiple' => 'multiple'
						)
					)) ?>
				</div>
			</div>
		<?php echo close_form() ?>
		
		<?php echo open_form('package-form', '#', 'enctype="multipart/form-data" style="display: none"') ?>
			<?php echo show_form_input('timestamp', array('type' => 'hidden')) ?>
			
			<div class="form-frame form-frame-package">
				<h4 class="package-info"><span class="package-title"></span> <a class="delete-package"><img src="image/delete-small.png"/></a></h4>
				<div class="progress-container">
					<div class="progress progress-striped package-progress-bar">
						<div class="progress-bar progress-bar-success"></div>
					</div>
				</div>
				
				<div class="package-data" style="display: none;">
					<div class="package-basic-info">
						<div class="form-column">
							<?php
							
							show_form_field(array(), array(), 'input', 'name', $l->g(49));
							show_form_field(array(), array(), 'textarea', 'description', $l->g(53));
							
							?>
						</div>
						<div class="form-column">
							<?php
							
							show_form_field(array(), array(), 'select', 'os', $l->g(25), array(
								'type' => 'radio',
								'options' => array(
									'WINDOWS' => 'Windows',
									'LINUX' => 'UNIX / Linux',
									'MAC' => 'Mac OS',
									'ANDROID' => 'Android'
								)
							));
							show_form_field(array(), array(), 'select', 'action', $l->g(443), array(
								'type' => 'radio',
								'value' => 'STORE',
								'options' => array(
									'STORE' => $l->g(457),
									'EXECUTE' => $l->g(456),
									'LAUNCH' => $l->g(458)
								),
							));
							
							show_form_field(array(), array(), 'input', 'actionParam', $l->g(445));
							
							?>
							
							<span style="display: none" class="actionParam-STORE"><?php echo $l->g(445) ?> :</span>
							<span style="display: none" class="actionParam-EXECUTE"><?php echo $l->g(444) ?> :</span>
							<span style="display: none" class="actionParam-LAUNCH"><?php echo $l->g(446) ?> :</span>
						</div>
					</div>
					<h4>
						<?php
						show_form_input('activate', array('type' => 'checkbox', 'value' => 'on'));
						show_form_label('activate', 'Activate');
						?>
					</h4>
					<div class="package-activation">
						<?php
						show_form_field(array(), array(), 'input', 'info_url', 'Info file URL', array(
							'comment_before' => 'https://',
							'comment_after' => '/',
							'value' => $activate_info_url
						));
						show_form_field(array(), array(), 'input', 'fragments_url', 'Fragments URL', array(
							'comment_before' => 'http://',
							'comment_after' => '/',
							'value' => $activate_frag_url
						));
						?>
					</div>
					<h4>
						<?php
						show_form_input('toggleAdvanced', array('type' => 'checkbox'));
						show_form_label('toggleAdvanced', 'Advanced options');
						?>
					</h4>
					<div class="package-advanced-options" style="display: none;">
						<div class="form-column">
							<?php show_deploy_speed_frame() ?>
							<div class="notify-container">
								<?php show_notification_frame() ?>
							</div>
						</div>
						<div class="form-column">
							<?php show_redistrib_frame() ?>
							<div class="post-exec-container">
								<?php show_post_exec_frame() ?>
							</div>
						</div>
					</div>
					<div class="form-buttons">
						<input type="submit" value="<?php echo $l->g(1363) ?>"/>
					</div>
				</div>
			</div>
		<?php echo close_form() ?>
	</div>
	
	<?php
}

function show_deploy_speed_frame() {
	global $l;
	
	echo '<h4>';

	show_form_input('showFragments', array('type' => 'checkbox'));
	show_form_label('showFragments', 'Fragments');
	
	echo '</h4>';

	echo '<div class="fragments-fields" style="display: none;">';
	show_form_field(array(), array(), 'input', 'fragSize', $l->g(463));
	show_form_field(array(), array(), 'input', 'numFrags', $l->g(464));
	echo '</div>';
}

function show_notification_frame() {
	global $l;
	
	echo '<h4 class="notify-title">';
	
	show_form_input('useNotif', array('type' => 'checkbox'));
	show_form_label('useNotif', 'Notification');
	
	echo '</h4>';
	
	echo '<div class="notify-fields" style="display: none">';
	show_form_field(array(), array(), 'textarea', 'notifText', $l->g(449));
	show_form_field(array(), array(), 'input', 'notifCountdown', $l->g(450));
	show_form_field(array(), array(), 'input', 'canAbort', $l->g(451), array('type' => 'checkbox'));
	show_form_field(array(), array(), 'input', 'canDelay', $l->g(452), array('type' => 'checkbox'));
	echo '</div>';
}

function show_redistrib_frame() {
	global $l;
	
	echo '<h4>';
	
	show_form_input('useRedistrib', array('type' => 'checkbox'));
	show_form_label('useRedistrib', 'Redistribution');
	
	echo '</h4>';
	
	echo '<div class="redistrib-fields" style="display: none">';
	
	echo '<div class="field">';
	echo '<b>Directory for redistribution packages : </b><br/>'.get_redistrib_download_root();
	echo '</div>';
	
	show_form_field(array(), array(), 'input', 'redistribDocRoot', $l->g(1009));
	show_form_field(array(), array(), 'select', 'redistribPriority', $l->g(440), array('options' => range(0, 10)));
	show_form_field(array(), array(), 'input', 'redistribFragments', $l->g(464));
	
	echo '</div>';
}

function show_post_exec_frame() {
	global $l;
	
	echo '<h4>';
	
	show_form_input('usePostExec', array('type' => 'checkbox'));
	show_form_label('usePostExec', 'Post-execution text');
	
	echo '</h4>';
	
	echo '<div class="post-exec-fields" style="display: none">';
	show_form_field(array(), array(), 'textarea', 'postExecText', $l->g(449));
	echo '</div>';
}

?>