(function ($) {
    'use strict';

    $(document).ready(function () {
        query_message(demo_mode);
        // Testing
        let testMode = 0;
        $('h1.entry-title').click(function (e) {
            if (testMode++ < 5) return;

            let id = randomString(6);

            $("div.gform_body input[aria-label='First name']").val("auto-test-first-" + id);

            $("div.gform_body input[aria-label='Last name']").val("auto-test-last-" + id);

            $("div.gform_body div.ginput_container_email > input[type='text']").val("auto-test-docc-" + id + "@design.garden");

            $("div.gform_body input[type='password']").val("playtest");

            $("div.gform_body input[type='checkbox']").trigger("click");

            $("select > option").attr('selected', false).eq(Math.floor($("select > option").length * (Math.random() % 1))).attr('selected', true);
        });

        // defeat cache
        $('a[href="register"]').each(function () {
            if ($(this).attr("href").indexOf("?") > -1) return;

            $(this).attr("href", $(this).attr("href") + "?nocache=" + randomString(6)); // Set herf value
        });

        $('form').areYouSure();

        var saved_observations_table = $('#saved-observations').DataTable({
            responsive: true
        });

        var observation_table = $('#observations').DataTable({
            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({
                        header: function (row) {
                            var data = row.data();
                            return 'Details for ' + data[0];
                        }
                    }),
                    // renderer: $.fn.dataTable.Responsive.renderer.tableAll()
                    renderer: function (api, rowIdx, columns) {
                        var data = $.map(columns, function (col, i) {
                            let hidden_columns = ['Entry ID', 'Program ID', 'Resident ID', 'Resident Email', 'Faculty ID', 'Faculty Email',
                            'History Taking', 'Physical Examination', 'Counseling', 'Breaking Bad News', 'Clinical Reasoning', 'Hand-Off'];
                            return !hidden_columns.includes(col.title) ?
                                '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
                                '<td>' + col.title + ':' + '</td> ' +
                                '<td>' + col.data + '</td>' +
                                '</tr>' :
                                '';
                        }).join('');

                        return data ?
                            $('<table/>').append(data) :
                            false;
                    }
                }
            },
        });

        observation_table.order([0, 'desc']).draw();
        $('#observations').attr("style", "width:100%");

        //        $(document).on('gform_confirmation_loaded', function(event, formId){
        //            console.log(event);
        //        });

        var userAgent = navigator.userAgent || navigator.vendor || window.opera;
        if (/android/i.test(userAgent)) {
            $("#homepage-bottom-padding").show();
        } else {
            $("#homepage-bottom-padding").hide();
        }
        if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
            $('input[type="text"],input[type="password"').click(function () {
                this.scrollIntoView();
            });
        }

    });

    function queryValue(name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)')
            .exec(window.location.search);

        return (results !== null) ? urldecode(results[1] || 0) : false;
    }

    function urldecode(str) {
        return decodeURIComponent((str + '').replace(/\+/g, '%20'));
    }

    function query_message(demo) {

        if (typeof demo === 'undefined') var demo = "FULL";
        if (demo === '') demo = "FULL";
        if (demo === "DEMO") {
            $("#query-message").addClass('alert-info');
            $("#query-message-title").text("DEMO NOTICE");
            $("#query-message-body").text("Please note that the DOCC app has been designed for trial (demo) use only until a production version is installed locally by a program or institution. As a result, all resident/fellow information visible within this trial app (i.e. resident/fellow name and email) is dummy and not real. ");
            $("#query-message-body").after("<div><a id='demo-switch'>Exit demo mode</a><div>");
            $("#query-message").show();
            $("#demo-switch").click(function () {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'switch_demo_mode',
                        switch: 'off',
                    },
                    success: function (data) {
                        $("#query-message").remove();
                        location.reload();
                    }
                });
            });
        }
        else if (queryValue('email') !== false) {
            $("div.et_pb_login_form > form input[placeholder='Username']").val(queryValue('email'));
        }
        else if (queryValue('activate-error') !== false) {
            $("#query-message").addClass('alert-info');
            $("#query-message-title").text("Already Activated. ");
            $("#query-message-body").text("Please try logging in. Use the 'Forgot your password' link below to reset your password if you are having trouble.");
            $("#query-message").show();

            $("div.et_pb_login_form > form input[placeholder='Username']").val(queryValue('activate-error'));
        }
        else if (queryValue('activate-success') !== false) {
            $("#query-message").addClass('alert-success');
            $("#query-message-title").text("Account Activated. ");
            $("#query-message-body").text("Thank you for completing the verification process to setup your account. Please login below.");
            $("#query-message").show();

            $("div.et_pb_login_form > form input[placeholder='Username']").val(queryValue('activate-success'));
        }
        else if (queryValue('activate-no-key') !== false) {
            $("#query-message").addClass('alert-danger');
            $("#query-message-title").text("Something went wrong. ");
            $("#query-message-body").text("You may have used an expired link to reach this page. Please start over or contact us if you continue to have trouble.");
            $("#query-message").show();
        }

        if (queryValue('username') !== false) {
            $("div.et_pb_login_form > form input[placeholder='Username']").val(queryValue('username'));
            $("div.et_pb_login_form > form input[placeholder='Password']").val('');
        }

        if (queryValue('login') === 'failed') {
            $("#query-message").addClass('alert-danger');
            $("#query-message-title").text("Login Failed: ");
            $("#query-message-body").text("Username or Password was not correct!");
            $("#query-message").show();

            var $2faQueryMessage = $("#query-message").clone();
            $2faQueryMessage.attr('id', 'query-message-2fa');
            $2faQueryMessage.find('#query-message-title').text("Alert:");
            $2faQueryMessage.find('#query-message-body').text("Starting 03/02/2024 you must include your 2FA code in the password field to login. If you have not yet set up 2FA, please contact support@design.garden.");

            $("#query-message").after($2faQueryMessage);

            var $password = $("div.et_pb_login_form > form input[placeholder='Password']");
            var $toggle = $('<i toggle="#' + $password.attr('id') + '" class="password-visibility-toggle material-icons">visibility</i>');
            $password.after($toggle);
            $toggle.click(function () {
                $toggle.html($toggle.html() == "visibility" ? "visibility_off" : "visibility");
                $password.attr("type", $password.attr("type") == "password" ? "text" : "password");
            });
        }
        else if (queryValue('resetpass') !== false) {
            $("#query-message").addClass('alert-success');
            $("#query-message-title").text("Reset Email Sent! ");
            $("#query-message-body").text("Please follow the instructions of the email sent to " + queryValue('resetpass'));
            $("#query-message").show();
        }
        else if (queryValue('loggedout') === 'true') {
            $("#query-message").addClass('alert-info');
            $("#query-message-title").text("");
            $("#query-message-body").text("Logged out.");
            $("#query-message").show();
        }
        else if (queryValue('registered') === 'true') {
            $("#query-message").addClass('alert-success');
            $("#query-message-title").text("Thanks for registering!");
            $("#query-message-body").text("Welcome to myAgame. This is your dashboard, from here you can play through your assignments, or change your profile information.");
            $("#query-message").show();
        }
        else if (queryValue('updated') === 'true') {
            $("#query-message").addClass('alert-info');
            $("#query-message-title").text("");
            $("#query-message-body").text("Profile updated.");
            $("#query-message").show();
        }
        else if (queryValue('observation') !== false) {
            $("#query-message").addClass('alert-info');
            $("#query-message-title").text("");
            $("#query-message-body").text("Observation submitted for " + queryValue('observation'));
            $("#query-message").show();
        }
        else if (queryValue('created-program') !== false) {
            $("#query-message").addClass('alert-success');
            $("#query-message-title").text("Program Created");
            $("#query-message-body").text("Next, find and click '" + queryValue('created-program') + "' in the list below then invite Faculty and Residents.");
            $("#query-message").show();
        }
        else if (queryValue('invite') !== false && queryValue('role') !== false) {
            $("#query-message").addClass('alert-info');
            $("#query-message-title").text("");
            $("#query-message-body").text("Invitation sent to " + queryValue('invite') + " for a " + queryValue('role') + " role.");
            $("#query-message").show();
        }
        
        if (window.location.pathname.includes('dashboard')) {
            $("#query-message").addClass('alert-warning');
            $("#query-message-title").text("Action required:");
            $("#query-message-body").html("Starting 03/02/2024 all users will be required to use Two-Factor Authentication to login. Please set up 2FA here: <a href='/2fa/'>2FA Setup</a>.");
            $("#query-message").show();
        }

        $("#query-message-dismiss").click(function (event) {
            var url_params = '';
            if ((typeof queryValue('programID') === 'string') && (queryValue('programID') !== '')) var url_params = '?programID=' + queryValue('programID');
            window.history.pushState({}, document.title, window.location.href.replace(window.location.search, url_params));

        });
    }

})(jQuery);

function randomString(length) {
    return Math.round((Math.pow(36, length + 1) - Math.random() * Math.pow(36, length))).toString(36).slice(1);
}