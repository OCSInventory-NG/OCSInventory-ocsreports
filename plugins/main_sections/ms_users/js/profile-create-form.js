(function ($) {
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

    function submitForm($form) {
        $form.find('input[type=submit]').prop('disabled', true);

        $form.find('.field').each(function () {
            removeFieldErrors($(this));
        });

        $form.find('.error').remove();

        $.ajax({
            url: 'ajax.php?function=admin_add_profile',
            type: 'POST',
            data: $form.serializeArray(),
        }).done(function (data) {
            if (data.status == 'error') {
                $form.find('input[type=submit]').prop('disabled', false);

                if (data.errors) {
                    for (key in data.errors) {
                        $.each(data.errors[key], function (i, err) {
                            addFieldError($form.find('.field-' + key), err);
                        });
                    }
                }

                $('<div class="error"/>')
                        .text(data.message)
                        .prependTo($form);
            } else {
                $('<div class="success"/>')
                        .text(data.message)
                        .prependTo($form);

                window.location.href = 'index.php?function=admin_profile_details&profile_id=' + data.profile_id;
            }
        }).fail(function () {
            $form.find('input[type=submit]').prop('disabled', false);

            $('<div class="error"/>')
                    .text('An error occurred while submitting the form. Please check your internet connection.')
                    .prependTo($form);
        });
    }

    $(document).ready(function () {
        var $form = $('form#create-profile');

        if ($form.length) {
            $form.submit(function (e) {
                e.preventDefault();
                e.stopPropagation();

                submitForm($form);
            });
        }
    });
})(jQuery);