<table id='observations' class='display responsive nowrap'>
    <thead>
        <tr>
            <th>Name of Resident</th>
            <th></th>
            <th>Date</th>
            <th>Progress</th>
            <th>Focus of Observation</th>
        </tr>
    </thead>
    <?php

    foreach ($entries as $entry) {
        $focusOfObservationHtmlList = $this->GetField($entry);

        echo $this->HtmlTableRow($entry['2.3'] . ' ' . $entry['2.6'], $this->HtmlAnchor("continue", $entry['resume_url']), $entry['date_saved'], $entry['partial_entry_percent'] . '%', $focusOfObservationHtmlList);
    }

    ?>
</table>