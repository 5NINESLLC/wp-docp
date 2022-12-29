<table id='members-table' class='display responsive nowrap'>
    <thead>
        <tr>
            <th>Members</th>
            <th>Role</th>
        </tr>
    </thead>
    <tbody>

    <?php

    foreach ($user_IDs as $ID) {
        $role = [];
        $user = get_user_by('ID', $ID);
        $login = $user->user_login;
        if (in_array('program_director', (array) $user->roles)) $role[] = "Program Director";
        if (in_array('observer', (array) $user->roles)) $role[] = "Faculty";
        if (in_array('resident', (array) $user->roles)) $role[] = "Resident";
        $role_as_list = implode(', ', $role);

    ?>

        <tr>
            <td><?php echo $login; ?></td>
            <td><?php echo $role_as_list; ?></td>
        </tr>

    <?php

    }

    ?>

    </tbody>
</table>