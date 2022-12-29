(function ($) {
    'use strict';

    $(document).ready(function () {
        let observation_table = $('#observations').DataTable();
        if (observation_table.context.length > 0) {
            let data = observation_table.data();
            create_export_buttons(data);
        }
    });

    function create_export_buttons(data) {
        let csv = "<a id='csv_button' class='export_button' href='#'>CSV</a>";
        let xml = "<a id='xml_button' class='export_button' href='#'>XML</a>";
        let json = "<a id='json_button' class='export_button' href='#'>JSON</a>";
        let export_container = "<div class='export_container'><div>Export Data:</div><span>" + csv + xml + json + "</span></div>";
        $(export_container).insertBefore('#observations_wrapper');

        $('#xml_button').click({ format: 'xml', data: data }, export_table);
        $('#csv_button').click({ format: 'csv', data: data }, export_table);
        $('#json_button').click({ format: 'json', data: data }, export_table);

        var instructions = "Copy to device clipboard:";
        addClipboardExportOptions(instructions, data);
    }

    function constructJSON(data) {
        var json = {};
        json['docc-report'] = {
            'siteurl': document.location.host,
            'data': {}
        };
        for (const index in data) {
            if (isNaN(index)) continue;
            const entry = data[index];
            if (typeof entry !== 'object') continue;
            let entryNum = 'entry-' + entry[11];
            json['docc-report']['data'][entryNum] = {
                'program-name': entry[0],
                'program-id': entry[12],
                'resident-id': entry[13],
                'resident-name': entry[9],
                'resident-email': entry[14],
                'faculty-id': entry[15],
                'faculty-name': entry[10],
                'faculty-email': entry[16],
                'date-of-observation': entry[1],
                'setting': entry[2],
                'focus-of-observation': remove_html(entry[3]),
                'complexity': entry[4],
                'strengths': entry[5],
                'afi': entry[6],
                'summary': entry[7],
                'pgy': entry[8]
            }
            if (entry[17] !== "Not observed") json['docc-report']['data'][entryNum]['history-taking'] = entry[17];
            if (entry[18] !== "Not observed") json['docc-report']['data'][entryNum]['physical-examination'] = entry[18];
            if (entry[19] !== "Not observed") json['docc-report']['data'][entryNum]['counseling'] = entry[19];
            if (entry[20] !== "Not observed") json['docc-report']['data'][entryNum]['breaking-bad-news'] = entry[20];
            if (entry[21] !== "Not observed") json['docc-report']['data'][entryNum]['clinical-reasoning'] = entry[21];
            if (entry[22] !== "Not observed") json['docc-report']['data'][entryNum]['hand-off'] = entry[22];
        }
        return json;
    }

    function export_table(event) {
        var json = constructJSON(event.data.data);
        var x2js = new X2JS();
        let filename = "docc-report";
        var ext;

        switch (event.data.format) {
            case "csv":
                var csv = export_csv(json);
                ext = ".csv";
                prompt_export(filename, ext, csv);
                break;
            case "xml":
                var xml = x2js.json2xml_str(json);
                ext = ".xml";
                prompt_export(filename, ext, xml);
                break;
            case "json":
                var json_str = JSON.stringify(json);
                ext = ".json";
                prompt_export(filename, ext, json_str);
            default:
            // nothing
        }
    }

    function addClipboardExportOptions(instructions, data) {
        let $csvClipboard = $("<a id='csvClipboard' class='export_button' href='#'>CSV</a>");
        let $xmlClipboard = $("<a id='xmlClipboard' class='export_button' href='#'>XML</a>");
        let $jsonClipboard = $("<a id='jsonClipboard' class='export_button' href='#'>JSON</a>");
        let $span = $("<span></span>");
        $span.append($csvClipboard);
        $span.append($xmlClipboard);
        $span.append($jsonClipboard);
        let $clipboard_container = $("<div class='clipboard_container'><div>" + instructions + "</div></div>");

        $clipboard_container.append($span);

        let $downloadNote = $("<div class='small-note'>* download as CSV, XML, or JSON from the desktop version of this app</div>");

        let $download_report_message = $("<div class='download_report_message'></div>");

        $download_report_message.append($clipboard_container);
        $download_report_message.append($downloadNote);

        $download_report_message.insertBefore($('.export_container').get(0));

        var json = constructJSON(data);

        $xmlClipboard.click(function (event) {
            var x2js = new X2JS();
            var xml = x2js.json2xml_str(json);
            copyToClipboard(this, xml);
            $(this).addClass('clicked', 1000);

            $("#query-message").addClass('alert-info');
            $("#query-message-title").text("Copied to Clipboard. ");
            $("#query-message-body").text("Open a compatible app and paste into a text input.");
            $("#query-message").show();
        });
        $csvClipboard.click(function (event) {
            var csv = export_csv(json);
            copyToClipboard(this, csv);
            $(this).addClass('clicked', 1000);

            $("#query-message").addClass('alert-info');
            $("#query-message-title").text("Copied to Clipboard. ");
            $("#query-message-body").text("Open a compatible app and paste into a text input.");
            $("#query-message").show();
        });
        $jsonClipboard.click(function (event) {
            var json_str = JSON.stringify(json);
            copyToClipboard(this, json_str);
            $(this).addClass('clicked', 1000);

            $("#query-message").addClass('alert-info');
            $("#query-message-title").text("Copied to Clipboard. ");
            $("#query-message-body").text("Open a compatible app and paste into a text input.");
            $("#query-message").show();
        });
    }

    function copyToClipboard(element, text) {
        var $input = $('<textarea id="clipboard"></textarea>');

        $input.text(text);

        $input.appendTo(element).css('opacity', 0);

        /* Get the text field */
        var copyText = $input.get(0);

        /* Select the text field */
        copyText.select();
        copyText.setSelectionRange(0, 99999); /*For mobile devices*/

        /* Copy the text inside the text field */
        document.execCommand("copy");

        $input.remove();
    }

    function export_csv(data) {
        var csv;
        var entries = Object.entries(data['docc-report']['data']);
        if (entries.length === 0) return "";
        var header = "entry-id," + Object.keys(entries[0][1]).join(',') + "\n";
        var body = "";
        entries.forEach((entry) => {
            body += entry[0] + "," + Object.values(entry[1]) + "\n";
        });
        csv = header + body;
        return csv;
    }

    function prompt_export(filename, ext, content) {
        var element = document.createElement('a');
        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(content));
        element.style.display = 'none';
        element.setAttribute('download', filename + ext);
        document.body.appendChild(element);
        element.click();
        document.body.removeChild(element);
    }
    
    function remove_html(value) {
        value = value.replaceAll('<ul>', '"');
        value = value.replaceAll('<li>', '');
        value = value.replaceAll('</li>', '\,');
        value = value.replaceAll('</ul>', '"');
        value = value.replace(/\,([^\,]*)$/, '$1');
        return value;
    }
})(jQuery);