<table class="form-table">
    <!-- <tr>
        <th><label for="programID"><?php //_e("Program ID"); ?></label></th>
        <td>
            <input type="text" name="programID" id="programID" value="<?php //echo esc_attr( get_the_author_meta( 'programID', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php //_e("Assigned on invitation/registration."); ?></span>
        </td>
    </tr> -->
    <tr>
        <th><label for="userPrograms"><?php _e("User Programs"); ?></label></th>
        <td>
            <?php 
            foreach($program_titles as $name)
            {
                echo $name;
                echo "<br>";
            }
            ?>
        </td>
    </tr>
    <!-- <tr>
        <th><label for="demo_mode"><?php //_e("Demo Mode Active"); ?></label></th>
        <td>
            <input type="text" name="demo_mode" id="demo_mode" value="<?php //var_dump(esc_attr( get_the_author_meta( 'demo_user', $user->ID ) )); ?>" class="regular-text" /><br />
            <span class="description"><?php //_e("Assigned on invitation/registration."); ?></span>
        </td>
    </tr>
    <tr>
        <th><label for="default_password_nag"><?php //_e("default_password_nag"); ?></label></th>
        <td>
            <input type="text" name="default_password_nag" id="default_password_nag" value="<?php //var_dump(esc_attr( get_the_author_meta( 'default_password_nag', $user->ID ) )); ?>" class="regular-text" /><br />
            <span class="description"><?php //_e("Assigned on invitation/registration."); ?></span>
        </td>
    </tr> -->
</table>