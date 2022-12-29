<table id='observations' class='display responsive nowrap' style='width:100%'>
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
        </tr>
    </thead>
    <tbody>
        <?php
        for ($i = 0; $i < 50; $i++) {
            $first1 = $first_names[array_rand($first_names, 1)];
            $last1 = $last_names[array_rand($last_names, 1)];
            $first2 = $first_names[array_rand($first_names, 1)];
            $last2 = $last_names[array_rand($last_names, 1)];
            $timestamp = mt_rand(1, time());
            $date = date("Y/m/d", $timestamp);
        ?>
            <tr>
                <td><?php echo $programs[array_rand($programs, 1)]; ?></td>
                <td><?php echo $date; ?></td>
                <td><?php echo $settings[array_rand($settings, 1)]; ?></td>
                <td>
                    <ul>
                        <?php
                        $FOO_ids = array_rand($FOOs, rand(1, 5));
                        if (is_array($FOO_ids)) {
                            foreach ($FOO_ids as $id) { ?>
                                <li><?php echo $FOOs[$id]; ?></li>
                            <?php }
                        } else { ?>
                            <li><?php echo $FOOs[rand(1, 5)]; ?></li>
                        <?php }
                        ?>
                    </ul>
                </td>
                <td><?php echo $complexities[array_rand($complexities, 1)]; ?></td>
                <td><?php echo $strengths[array_rand($strengths, 1)]; ?></td>
                <td><?php echo $AFIs[array_rand($AFIs, 1)]; ?></td>
                <td><?php echo $summaries[array_rand($summaries, 1)]; ?></td>
                <td><?php echo $PGYs[array_rand($PGYs, 1)]; ?></td>
                <td><?php echo $first1 . " " . $last1; ?></td>
                <td><?php echo $first2 . " " . $last2; ?></td>
                <td><?php echo rand(1, 999); ?></td>
                <td><?php echo rand(1, 999); ?></td>
                <td><?php echo rand(1, 999); ?></td>
                <td><?php echo $first1 . $last1 . "@gmail.com"; ?></td>
                <td><?php echo rand(1, 999); ?></td>
                <td><?php echo $first2 . $last2 . "@gmail.com"; ?></td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>