<table>
    <thead>
        <tr>
            <th>Name of Program</th>
            <th>Support Contact</th>
            <th class="hide-500">Broadcast Message</th>
        </tr>
    </thead>
    <tbody>
        <?php

        if (count($programs) === 0) {
        ?>
            <tr>
                <td colspan="3" style="text-align: center; height: 55px;"><i>Click "NEW PROGRAM" above to create a Program.</i></td>
            </tr>
            <?php
        } else {
            foreach ($programs as $program) {
                $support_contact = get_post_meta($program->ID, 'support_contact', true);
                $broadcast_message = get_post_meta($program->ID, 'broadcast_message', true);
            ?>
                <tr>
                    <td><a href="<?php echo get_permalink($program->ID); ?>" title="<?php echo the_title_attribute('echo=0'); ?>" rel="bookmark"><?php echo get_the_title($program->ID); ?></a></td>
                    <td><a href="mailto:<?php echo $support_contact; ?>"><?php echo $support_contact; ?></a></td>
                    <td class="hide-500">
                        <div><?php echo $broadcast_message; ?></div>
                    </td>
                </tr>
        <?php
            }
        }
        ?>
    </tbody>
</table>