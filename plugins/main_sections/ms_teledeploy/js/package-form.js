(function ($) {
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

    $(document).ready(function () {
        var $form = $('form#create_pack');

        if ($form.length) {
            // Handle input changes

            $form.find('input[name=OS]').change(function () {
                $form.find('.form-frame-user-messages').toggle(this.id == 'OS_WINDOWS');
            });

            $form.find('input[name=ACTION]').change(function () {
                $form.find('label[for=ACTION_INPUT]').html($form.find('.action-input-' + $(this).val()).html());
            });

            $form.find('#DEPLOY_SPEED').change(function () {
                if ($(this).val() == 'CUSTOM') {
                    $form.find('#PRIORITY, #NB_FRAGS').prop('disabled', false);
                } else {
                    $form.find('#PRIORITY, #NB_FRAGS').prop('disabled', true);
                }
            });

            $form.find('#NOTIFY_USER').change(function () {
                $form.find('.notify-fields').toggle(this.checked);
            });

            $form.find('#NEED_DONE_ACTION').change(function () {
                $form.find('.done-action-fields').toggle(this.checked);
            });

            $form.find('#REDISTRIB_USE').change(function () {
                $form.find('.redistrib-fields').toggle(this.checked);
            });

            // Handle file upload
            $form.find('#FILE').fileupload({
                url: 'ajax.php' + window.location.search,
                dataType: 'json',
                done: function (e, data) {
                    var fileData = data.result;

                    $form.find('.package-progress-bar').hide().find('.progress-bar').css('width', '0%');
                    $form.find('input[type=submit]').attr('disabled', false);

                    if (fileData.status == 'success') {
                        // Display file info
                        $form.find('.file-type span').text(fileData.type);
                        $form.find('.file-size span').text(fileData.size);
                        $form.find('.file-info').show();
                    } else {
                        // Display error message
                        addFieldError($form.find('.field-FILE'), fileData.message);
                        $form.find('.file-field').show();
                    }
                },
                fail: function (e, data) {
                    addFieldError($form.find('.field-FILE'), 'An error has occurred during file upload');
                    $form.find('.file-field').show();
                    $form.find('.package-progress-bar').hide().find('.progress-bar').css('width', '0%');
                    $form.find('input[type=submit]').attr('disabled', false);
                },
                progressall: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $form.find('.package-progress-bar .progress-bar').css('width', progress + '%');
                }
            }).change(function () {
                $form.find('.file-field').hide();
                $form.find('.package-progress-bar').show();
                $form.find('input[type=submit]').attr('disabled', true);
                removeFieldErrors($form.find('.field-FILE'));
            });
        }
    });
})(jQuery);