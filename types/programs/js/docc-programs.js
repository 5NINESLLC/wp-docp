(function ($) {
    'use strict';

    $(document).ready(function () {
        setup_invite_to_program_button();
        program_registration_back_button();
        filter_residents_dropdown();
        manage_program_members_table();
        resident_dropdown();
    });

    function resident_dropdown(){
        // resident_dropdown
        $('.resident_dropdown').change(function () {
            let options = $(this).find('option');
            var selected_resident;
            options.each(function (index) {
                if (options[index].selected == true) {
                    selected_resident = options[index].text;
                }
            });
            let resident_name_exists_check = selected_resident.split("(");
            if (resident_name_exists_check.length > 1) {
                let resident_name_without_formatting = resident_name_exists_check[1].replace(")", "");
                let formatted_resident_name = resident_name_without_formatting.split(" ");
                let first = formatted_resident_name[0];
                let last = formatted_resident_name[1];
                // find name inputs and update values
                $('.resident_name_input > div > .name_first > input').val(first);
                $('.resident_name_input > div > .name_last > input').val(last);
            } else {
                // find name inputs and update values to blank
                $('.resident_name_input > div > .name_first > input').val("");
                $('.resident_name_input > div > .name_last > input').val("");
            }
        });
    }

    function manage_program_members_table() {
        var members_table = $('#members-table').DataTable({
            responsive: true
        });
        $('#members-table').attr("style", "width:100%");
    }

    function filter_residents_dropdown() {
        if (typeof demo_mode === 'undefined') var demo_mode = false;
        if (demo_mode === '') demo_mode = false;
        if (demo_mode) return;
        var $program_dropdown = $('select#input_4_68');
        if ($program_dropdown.length < 1) return;
        hide_residents();
        $program_dropdown.change(function () {
            let program_name = this.value;
            if (program_name === '') hide_residents();
            else get_residents(program_name);
        });
    }

    function get_residents(program_name) {
        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: {
                action: 'get_program_residents',
                name: program_name,
            },
            success: function (residents) {
                update_residents(JSON.parse(residents));
            }
        });
    }

    function hide_residents() {
        var $residents_dropdown = $('select#input_4_62');
        if ($residents_dropdown.length < 1) return;
        $residents_dropdown.children().each(function () {
            $(this).hide();
        });
    }

    function update_residents(residents) {
        var $residents_dropdown = $('select#input_4_62');
        if ($residents_dropdown.length < 1) return;
        $residents_dropdown.children().each(function () {
            let resident = this.value;
            if (resident === '') return;
            if (residents.includes(resident)) $(this).show();
            else $(this).hide();
        });
    }

    function program_registration_back_button() {
        var $back_button = $('a#back-button');
        if ($back_button.length === 0) return;
        $back_button.attr("href", "javascript: history.go(1)");
        $back_button.click(function () {
            window.history.back();
        });
    }

    function setup_invite_to_program_button() {
        var $invite_button = $('a#invite-to-program-button');
        if ($invite_button.length === 0) return;
        // let program_slug = get_program_slug(window.location.pathname);
        let $entry_title = $('.entry-title');
        if ($entry_title.length === 0) return;
        let program_name = $entry_title[0].innerHTML;
        if (typeof program_name !== 'string') return;
        $invite_button[0].href += '?programID=' + program_name;
    }

    function get_program_slug(path) {
        let paths_array = path.split('/');
        if (paths_array.length !== 3 && paths_array.length !== 4) return;
        if (paths_array[0] !== "" || paths_array[1] !== "program") return;
        if (typeof paths_array[2] !== 'string') return;
        return paths_array[2];
    }

})(jQuery);