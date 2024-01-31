(function ($) {
    'use strict';

    $(document).ready(function () {
        scrub_phi_data();
        format_hipaa_table();
        setup_delete_old_data_toggle();
    });

    function scrub_phi_data() {
        var $scrub_button = $('button#scrub-button');
        if ($scrub_button.length === 0) return;
        $scrub_button.click(function () {
            return $.ajax({
                url: ajaxurl,
                type: 'GET',
                data: {
                    action: 'scrub_phi_data'
                },
                success: function(response) {
                    location.reload();
                }
            });
        });
    }

    function format_hipaa_table()
    {
        var hipaa_table = $('#hipaa-table').DataTable({
            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({
                        header: function (row) {
                            var data = row.data();
                            return 'Details for ' + data[0];
                        }
                    }),
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
    
        hipaa_table.order([0, 'desc']).draw();
        $('#hipaa-table').attr("style", "width:100%");
    }

    function setup_delete_old_data_toggle()
    {
        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: {
                action: 'get_delete_old_data_setting'
            },
            success: function(response) {
                var isChecked = response;
                $('#delete-old-data-checkbox').prop('checked', isChecked);
    
                $('#delete-old-data-checkbox').change(function() {
                    var isChecked = $(this).is(':checked');
    
                    $.ajax({
                        url: ajaxurl,
                        type: 'GET',
                        data: {
                            action: 'toggle_delete_old_data',
                            isChecked: isChecked
                        },
                        success: function(response) {
                            console.log(response);
                        }
                    });
                });
            }
        });
    }

})(jQuery);