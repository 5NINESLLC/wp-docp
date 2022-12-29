<!-- <h3>My Programs</h3> -->
<table id='programs-table'>
    <thead>
        <tr>
            <th>Program</th>
            <th>Broadcast Message</th>
            <th>Support Contact</th>
        </tr>
    </thead>
    <tbody>
        <?php

        if (count($tableData) === 0) {
            $notice = Docc_Programs_Shortcodes::IsProgramDirector() ? 'Click "Programs" in the menu to create a Program.' : "You have not been invited to any Programs yet.";
        ?>
            <tr>
                <td colspan="3" style="text-align: center; height: 55px;"><i><?php echo $notice; ?></i></td>
            </tr>
            <?php
        } else {
            foreach ($tableData as $row) {
            ?>
                <tr>
                    <td><?php echo get_the_title($row[0]); ?></td>
                    <td><?php echo $row[1]; ?></td>
                    <td><a href="mailto:<?php echo $row[2]; ?>"><?php echo $row[2] ?></a></td>
                </tr>
        <?php
            }
        }

        ?>
    </tbody>
</table>