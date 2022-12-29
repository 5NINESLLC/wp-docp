<table>
    <thead>
        <tr>
            <th>Name of Program</th>
            <th>Support Contact</th>
            <th>Broadcast Message</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($programs as $program) {
            $residents = get_post_meta($program->ID, 'residents', true);
            if (is_array($residents) && in_array($user->ID, $residents)) {
                $support_contact = get_post_meta($program->ID, 'support_contact', true);
                $broadcast_message = get_post_meta($program->ID, 'broadcast_message', true);
        ?>
                <tr>
                    <td><?php echo get_the_title($program->ID); ?></td>
                    <td>
                        <div><a href="mailto:<?php echo $support_contact; ?>"><?php echo $support_contact; ?></a></div>
                    </td>
                    <td>
                        <div><?php echo $broadcast_message; ?></div>
                    </td>
                </tr>
        <?php
            }
        }
        ?>
    </tbody>
</table>