<table id='observations' class='display responsive nowrap' style='max-width:100%'>
    <thead>
        <tr>
            <th>Program</th>
            <th>Date Of Observation</th>
            <th>Setting</th>
            <th>Type (Focus of Observation)</th>
            <th>Complexity</th>
            <th>Strengths</th>
            <th>AFIs</th>
            <th>Summary</th>
            <th>PGY</th>
            <th>Resident/Fellow Name</th>
            <th>Faculty Member</th>
            <th>Entry ID</th>
            <th>Program ID</th>
            <th>Resident ID</th>
            <th>Resident Email</th>
            <th>Faculty ID</th>
            <th>Faculty Email</th>
            <th class="hidden-table-entry">History Taking</th>
            <th class="hidden-table-entry">Physical Examination</th>
            <th class="hidden-table-entry">Counseling</th>
            <th class="hidden-table-entry">Breaking Bad News</th>
            <th class="hidden-table-entry">Clinical Reasoning</th>
            <th class="hidden-table-entry">Hand-Off</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($entries as $entry) {
            $label = "Program ID";
            $field_id = Docc_Public::GetFieldId($form, $label);
            $program = DOCC_Programs::get_program_by_name($entry[$field_id]);
            $program_id = $program === null? 0 : $program->ID;
            $label = "Resident Email";
            $field_id = Docc_Public::GetFieldId($form, $label);
            $resident_user = get_user_by('email', $entry[$field_id]);
            $resident_id = $resident_user === false ? 0 : get_user_by('email', $entry[$field_id])->ID;
            $label = "Faculty Email";
            $field_id = Docc_Public::GetFieldId($form, $label);
            $faculty_user = get_user_by('email', $entry[$field_id]);
            $faculty_id = $faculty_user === false ? 0 : $faculty_user->ID;
        ?>
            <tr>
                <td>
                    <?php
                    $label = "Program ID";
                    $field_id = Docc_Public::GetFieldId($form, $label);
                    echo $entry[$field_id];
                    ?>
                </td>
                <td>
                    <?php
                    echo $this->format_date($entry['date_created']);
                    ?>
                </td>
                <td>
                    <?php
                    $label = "Setting";
                    $field_id = Docc_Public::GetFieldId($form, $label);
                    echo $entry[$field_id];
                    ?>
                </td>
                <td>
                    <ul>
                        <?php
                        for ($i = 1; $i < 5; $i++) {
                            if ($entry["5.$i"]) { // TODO: lookup entries with multiple values
                        ?>
                                <li><?php echo $entry["5.$i"]; ?></li>
                        <?php
                            }
                        }
                        ?>
                    </ul>
                </td>
                <td>
                    <?php
                    $label = "Complexity of Encounter";
                    $field_id = Docc_Public::GetFieldId($form, $label);
                    echo $entry[$field_id];
                    ?>
                </td>
                <td>
                    <?php
                    $label = "What, if anything, did you observe the resident do well?";
                    $field_id = Docc_Public::GetFieldId($form, $label);
                    echo $entry[$field_id];
                    ?>
                </td>
                <td>
                    <?php
                    $label = "What did the resident do incorrectly or what should they do differently? How should they do that differently?";
                    $field_id = Docc_Public::GetFieldId($form, $label);
                    echo $entry[$field_id];
                    ?>
                </td>
                <td>
                    <?php
                    $label = "Based on your observations, in 1-2 sentences, how would you summarize the big picture of this learner’s skills in this scenario? That is, how would you synthesize your observations into an overarching theme?";
                    $field_id = Docc_Public::GetFieldId($form, $label);
                    echo $entry[$field_id];
                    ?>
                </td>
                <td>
                    <?php
                    $label = "PGY";
                    $field_id = Docc_Public::GetFieldId($form, $label);
                    echo $entry[$field_id];
                    ?>
                </td>
                <td>
                    <?php
                    echo $entry['2.3'] . " " . $entry['2.6'];
                    ?>
                </td>
                <td>
                    <?php
                    echo $entry['1.3'] . " " . $entry['1.6'];
                    ?>
                </td>
                <td>
                    <?php
                    echo $entry['id'];
                    ?>
                </td>
                <td>
                    <?php
                    echo $program_id;
                    ?>
                </td>
                <td>
                    <?php
                    echo $resident_id;
                    ?>
                </td>
                <td>
                    <?php
                    $label = "Resident Email";
                    $field_id = Docc_Public::GetFieldId($form, $label);
                    echo $entry[$field_id];
                    ?>
                </td>
                <td>
                    <?php
                    echo $faculty_id;
                    ?>
                </td>
                <td>
                    <?php
                    $label = "Faculty Email";
                    $field_id = Docc_Public::GetFieldId($form, $label);
                    echo $entry[$field_id];
                    ?>
                </td>
                <!-- 19 63 64 65 66 67 -->
                <td class="hidden-table-entry">
                    <?php
                    $label = "Based on this single observation of history taking, please provide an overall judgement of this learner.";
                    $field_id = Docc_Public::GetFieldId($form, $label);
                    if (!empty($entry[$field_id])) echo $entry[$field_id];
                    else echo "Not observed";
                    ?>
                </td>
                <td class="hidden-table-entry">
                    <?php
                    $label = "Based on this single observation of physical examination, please provide an overall judgement of this learner.";
                    $field_id = Docc_Public::GetFieldId($form, $label);
                    if (!empty($entry[$field_id])) echo $entry[$field_id];
                    else echo "Not observed";
                    ?>
                </td>
                <td class="hidden-table-entry">
                    <?php
                    $label = "Based on this single observation of counseling / shared decision making, please provide an overall judgement of this learner.";
                    $field_id = Docc_Public::GetFieldId($form, $label);
                    if (!empty($entry[$field_id])) echo $entry[$field_id];
                    else echo "Not observed";
                    ?>
                </td>
                <td class="hidden-table-entry">
                    <?php
                    $label = "Based on this single observation of breaking bad news, please provide an overall judgement of this learner.";
                    $field_id = Docc_Public::GetFieldId($form, $label);
                    if (!empty($entry[$field_id])) echo $entry[$field_id];
                    else echo "Not observed";
                    ?>
                </td>
                <td class="hidden-table-entry">
                    <?php
                    $label = "Based on this single observation of clinical reasoning, please provide an overall judgement of this learner.";
                    $field_id = Docc_Public::GetFieldId($form, $label);
                    if (!empty($entry[$field_id])) echo $entry[$field_id];
                    else echo "Not observed";
                    ?>
                </td>
                <td class="hidden-table-entry">
                    <?php
                    $label = "Based on this single observation of hand-off, please provide an overall judgement of this learner.";
                    $field_id = Docc_Public::GetFieldId($form, $label);
                    if (!empty($entry[$field_id])) echo $entry[$field_id];
                    else echo "Not observed";
                    ?>
                </td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>