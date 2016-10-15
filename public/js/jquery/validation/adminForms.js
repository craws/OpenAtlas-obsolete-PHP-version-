$(document).ready(function () {

    function OneClickSubmitButton() {
        $('button[type=submit], input[type=submit]').each(function () {
            var theButton = $(this);
            var theForm = theButton.closest('form');
            function tieButtonToForm() {
                theButton.click(function () {
                    theButton.prop('disabled', true);
                    theForm.submit();
                });
            }
            tieButtonToForm();
            // re-wire the event when the form is invalid.
            theForm.submit(function (event) {
                if (!$(this).valid()) {
                    theButton.prop('disabled', false);
                    event.preventDefault();
                    tieButtonToForm();
                }
            });
        });
    }
    OneClickSubmitButton();

    $.validator.addClassRules({
        year: {number: true, min: -4713},
        month: {digits: true, max: 12},
        day: {digits: true, max: 31}
    });

    $("#passwordForm").validate({
        rules: {
            passwordCurrent: {rangelength: [1, 32]},
            password: {rangelength: [8, 256]},
            passwordRetype: {equalTo: "#password"}
        }
    });

    $("#settingsForm").validate({
        rules: {
            mail_transport_password_retype: {equalTo: "#mail_transport_password"},
            mail_from_email: {email: true}
        }
    });

    $("#passwordResetForm").validate({
        rules: {
            email: {email: true}
        }
    });

    $("#testMailForm").validate({
        rules: {
            testMailReceiver: {email: true}
        }
    });

    $("#placeForm").validate({
        rules: {
            easting: {number: true},
            northing: {number: true}
        }
    });

    $("#profileForm").validate({
        rules: {
            email: {email: true}
        }
    });

    $("#userForm").validate({
        rules: {
            username: {rangelength: [1, 32]},
            password: {rangelength: [8, 256]},
            passwordRetype: {equalTo: "#password"},
            email: {email: true}
        }
    });

    $("form").each(function () {
        $(this).validate();
    });
});
