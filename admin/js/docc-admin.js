(function ($) {
    'use strict';

    $(document).ready(function () {
        if ($('#program_meta').length) setup_export_user_programs_button();
        if ($('#generate-reset-link').length) setup_get_password_reset_link_button();
    });

    function setup_export_user_programs_button() {
        let export_button = "<a id='export_button' class='export_button' href='#'>Export Users</a>";
        let export_container = "<div class='export_container'><div><span>" + export_button + "</span></div>";
        $(export_container).insertAfter("#program_meta");

        $('#export_button').click(function() {
            export_user_programs(filename, JSON.stringify(users));
        });
    }

    function export_user_programs(filename, content) {
        var element = document.createElement('a');
        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(content));
        element.style.display = 'none';
        element.setAttribute('download', filename + " Users.json");
        document.body.appendChild(element);
        element.click();
        document.body.removeChild(element);
    }

    function setup_get_password_reset_link_button() {
        var user_id = new URL(window.location.href).searchParams.get("user_id");
        let copy_reset_link_button = $('#generate-reset-link').clone();
        copy_reset_link_button.attr('id', 'copy-password-reset-link');
        copy_reset_link_button.css('margin-left', '5px');
        copy_reset_link_button.text("Copy Reset Link");
        copy_reset_link_button.click(function() {
            return $.ajax({
                url: ajaxurl,
                method: 'GET',
                data: {
                    action: 'get_password_reset_link',
                    user_id: user_id
                },
            }).success(function(data) {
                const copy = document.createElement('textarea');
                copy.value = data;
                document.body.appendChild(copy);
                copy.select();
                document.execCommand('copy');
                document.body.removeChild(copy);
                let success_text = "<span id='link-copied' style='margin-left:5px'>Reset link copied to clipboard</span>";
                if (!$('#link-copied').length) {
                    $(success_text).insertAfter('#copy-password-reset-link');
                }
            });
        });
        $(copy_reset_link_button).insertAfter('#generate-reset-link');
    }
    
})(jQuery);