(function($) {
	var counter = 0,
		forms = {};
	
	function addFieldError($field, errorMessage) {
		if (!$field.hasClass('field-has-errors')) {
			$field.addClass('field-has-errors');
			$field.prepend($('<ul/>', {'class': 'field-error-list'}));
		}

		$field.find('.field-error-list').append($('<ul/>').text(errorMessage));
	}

	function removeFieldErrors($field) {
		$field.removeClass('field-has-errors').find('.field-error-list').remove();
	}
	
	function buildPackageForm($uploadingPackages, $tpl, filename) {
		var base, ext, match,
			$pkgForm = $tpl.clone()
				.attr('id', 'package-form-'+counter)
				.data('filename', filename),
			$osContainer = $pkgForm.find('.field-os .radio-container');
		
		if (match = filename.match(/^(.+)\.zip$/i)) {
			base = match[1];
			ext = 'zip';
			
			$osContainer.hide().find('#os_WINDOWS').prop('checked', true);
			$('<span/>').text('Windows').insertBefore($osContainer);
		} else if (match = filename.match(/^(.+)\.tar\.gz$/i)) {
			base = match[1];
			ext = 'tar.gz';

			$osContainer.find('#os_WINDOWS, label[for=os_WINDOWS]').hide();
			$osContainer.find('#os_LINUX').prop('checked', true);
			
			$pkgForm.find('.notify-container, .post-exec-container').hide();
		} else if (match = filename.match(/^(.+)\.apk$/i)) {
			base = match[1];
			ext = 'apk';

			$osContainer.hide().find('#os_ANDROID').prop('checked', true);
			$('<span/>').text('Android').insertBefore($osContainer);
			
			$pkgForm.find('.notify-container, .post-exec-container').hide();
		}
		
		$pkgForm.find('.package-title').text(filename);
		
		$pkgForm.find('input[name=name]').val(base);
		
		$pkgForm.find('input, textarea, select').each(function() {
			var $this = $(this), id = $this.attr('id');
			
			if (id) {
				$this.attr('id', id+'-'+counter);
			}
		});

		$pkgForm.find('label').each(function() {
			var $this = $(this), for_ = $this.attr('for');
			
			if (for_) {
				$this.attr('for', for_+'-'+counter);
			}
		});
		
		$pkgForm.find('input[name=action]').change(function() {
			$pkgForm.find('.field-actionParam label').text($pkgForm.find('.actionParam-'+this.value).text());
		});
		
		$pkgForm.find('input[name=activate]').change(function() {
			$pkgForm.find('.package-activation').toggle($(this).prop('checked'));
		});
		
		$pkgForm.find('input[name=toggleAdvanced]').change(function() {
			$pkgForm.find('.package-advanced-options').toggle($(this).prop('checked'));
		});
		
		$pkgForm.find('input[name=showFragments]').change(function() {
			$pkgForm.find('.fragments-fields').toggle($(this).prop('checked'));
		});
		
		$pkgForm.find('input[name=useNotif]').change(function() {
			$pkgForm.find('.notify-fields').toggle($(this).prop('checked'));
		});
		
		$pkgForm.find('input[name=useRedistrib]').change(function() {
			$pkgForm.find('.redistrib-fields').toggle($(this).prop('checked'));
		});
		
		$pkgForm.find('input[name=usePostExec]').change(function() {
			$pkgForm.find('.post-exec-fields').toggle($(this).prop('checked'));
		});
		
		$pkgForm.appendTo($uploadingPackages).show();
		forms[filename] = $pkgForm;
		counter++;
	}
	
	function displaySize(bytes) {
		if (bytes < 1000) {
			return bytes+' B';
		} else if (bytes < 1000 * 1000) {
			return (Math.round(bytes / (100)) / 10)+' kB';
		} else if (bytes < 1000 * 1000 * 1000) {
			return (Math.round(bytes / (100 * 1000)) / 10)+' MB';
		} else {
			return (Math.round(bytes / (100 * 1000 * 1000)) / 10)+' GB';
		}
	}
	
	function submitPackageForm(filename, $form) {
		$form.find('input[type=submit]').prop('disabled', true);
		
		$form.find('.field').each(function() {
			removeFieldErrors($(this));
		});

		$form.find('.error').remove();
		
		$.ajax({
			url: 'ajax.php?function=build_package',
			type: 'POST',
			data: $form.serializeArray(),
		}).done(function(data) {
			if (data.status == 'error') {
				$form.find('input[type=submit]').prop('disabled', false);
				
				if (data.errors) {
					for (key in data.errors) {
						$.each(data.errors[key], function(i, err) {
							addFieldError($form.find('.field-'+key), err);
						});
					}
				}

				$('<div class="error"/>')
					.text(data.message)
					.insertAfter($form.find('.package-info'));
			} else if (data.status == 'warning') {
				delete forms[filename];
				$form.replaceWith(
					$('<div class="warning"/>').html('Package successfully created with timestamp '+data.timestamp+'<br>'+data.message)
				);
			} else {
				delete forms[filename];
				$form.replaceWith(
					$('<div class="success"/>').text('Package successfully created with timestamp '+data.timestamp)
				);
			}
		}).fail(function() {
			$form.find('input[type=submit]').prop('disabled', false);
			
			$('<div class="error"/>')
				.text('An error occurred while submitting the form. Please check your internet connection.')
				.insertAfter($form.find('.package-info'));
		});
	}

	$(document).ready(function() {
		var $filesForm = $('form#package-files'),
			$pkgTplForm = $('form#package-form'),
			$uploadingPackages = $('.uploading-packages'),
			$uploadedPackages = $('.uploaded-packages');

		if ($filesForm.length) {
			$filesForm.find('#packageFile').fileupload({
				url: 'ajax.php?function=build_package',
				dataType: 'json',
				submit: function(e, data) {
					var filename = data.files[0].name;
					
					if (filename in forms) {
						e.stopPropagation();
						e.stopImmediatePropagation();
						e.preventDefault();
					} else if (!/(\.zip|\.tar\.gz|\.apk)$/i.test(filename)) {
						e.stopPropagation();
						e.stopImmediatePropagation();
						e.preventDefault();
						
						$('<div class="error"/>')
							.text('Error while uploading file '+filename+' : Invalid file type (should be zip, tar.gz or apk)')
							.insertBefore($uploadedPackages);
					} else {
						buildPackageForm($uploadingPackages, $pkgTplForm, filename);
					}
				},
				done: function (e, data) {
					var fileData = data.result,
						filename = data.files[0].name,
						$form = forms[filename];

					if (fileData.status == 'success') {
						$form.find('.package-progress-bar').hide().find('.progress-bar').css('width', '0%');
						$form.find('.package-title').text(filename+' ('+displaySize(fileData.size)+')');
						$form.find('.package-data').show();

						$form.find('input[name=timestamp]').val(fileData.timestamp);

						$form.find('.field-info_url .comment_after, .field-fragments_url .comment_after').text('/'+fileData.timestamp);

						$form.find('input[name=fragSize]').val(fileData.size);
						$form.find('input[name=numFrags]').val(1);
						
						$form.submit(function(e) {
							e.preventDefault();
							e.stopPropagation();
							
							submitPackageForm(filename, $form);
						});
						
						$form.appendTo($uploadedPackages);
					} else {
						delete forms[filename];
						$form.remove();
						
						$('<div class="error"/>')
							.text('Error while uploading file '+filename+' : '+fileData.message)
							.insertBefore($uploadedPackages);
					}
				},
				fail: function (e, data) {
					var filename = data.files[0].name,
						$form = forms[filename];
					
					delete forms[filename];
					$form.remove();
					
					$('<div class="error"/>')
						.text('Error while uploading file '+filename+' : '+data.errorThrown)
						.insertBefore($uploadedPackages)
				},
				progress: function (e, data) {
					var progress = parseInt(data.loaded / data.total * 100, 10),
						filename = data.files[0].name,
						$form = forms[filename];
					
					$form.find('.package-progress-bar .progress-bar').css('width', progress+'%');
				}
			});
		}
	});
}) (jQuery);