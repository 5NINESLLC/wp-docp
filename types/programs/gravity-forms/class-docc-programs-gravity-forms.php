<?php

/**
 * Program custom post type
 *
 * @link       https://design.garden
 * @since      1.0.0
 *
 * @package    Docc
 * @subpackage Docc/programs
 */

/**
 * Gravity Forms implementation for Programs
 *
 *
 * @package    Docc
 * @subpackage Docc/programs
 * @author     Anthony Jacobs <tony@design.garden>
 */
class Docc_Programs_GF extends Docc_Controller
{

    public function gform_after_submission($entry, $form)
    {

        switch ($form['title']) {
            case "Invite User to Program":
                $this->gform_after_submission_invite_user($entry, $form);
                break;
            case "Create Program":
                $this->gform_after_submission_create_program($entry, $form);
                break;
            case "Edit Program":
                $this->gform_after_submission_edit_program($entry, $form);
                break;
            case "User Registration":
                $this->gform_after_submission_user_registration($entry, $form);
        }
    }

    public function gform_after_submission_create_program($entry, $form)
    {

        $program_ID = wp_insert_post([
            'post_type' => 'program',
            'post_title' => $this->_getEntryValue($form, $entry, "Program Title"),
            'post_content' => '',
            'post_status' => 'publish',
        ]);
        if ($program_ID) {
            update_post_meta($program_ID, 'support_contact', $this->_getEntryValue($form, $entry, 'Support Contact'));
            update_post_meta($program_ID, 'invitation_message', $this->_getEntryValue($form, $entry, 'Invitation Message'));
            update_post_meta($program_ID, 'broadcast_message', $this->_getEntryValue($form, $entry, 'Broadcast Message'));
            $user = wp_get_current_user();
            update_post_meta($program_ID, 'directors', array_filter([$user->ID]));
        }
    }

    public function gform_after_submission_edit_program($entry, $form)
    {

        $program_ID = intval($this->_getEntryValue($form, $entry, 'Program ID'));

        if (!($program_ID > 0)) return; // TODO: show error message

        $program = get_post($program_ID);

        if ($program === null) return; // TODO: show error message

        $user_id = get_current_user_id();

        if (!$this->UserIsInProgramAsRole($program->ID, $user_id, 'directors')) return;

        if ($program->post_title === "DEMO Program") return;

        wp_update_post(['ID' => $program->ID, 'post_title' => $this->_getEntryValue($form, $entry, 'Program Title')]);

        update_post_meta($program->ID, 'support_contact', $this->_getEntryValue($form, $entry, 'Support Contact'));
        update_post_meta($program->ID, 'invitation_message', $this->_getEntryValue($form, $entry, 'Invitation Message'));
        update_post_meta($program->ID, 'broadcast_message', $this->_getEntryValue($form, $entry, 'Broadcast Message'));
    }

    public function gform_after_submission_invite_user($entry, $form)
    {
        $program = Docc_Programs::get_program_by_name($this->_getEntryValue($form, $entry, 'Program'));
        $this->add_user_to_program(
            $program->ID,
            $this->_getEntryValue($form, $entry, 'Email'),
            $this->_getEntryValue($form, $entry, 'Select role in the program')
        );
    }

    public function gform_after_submission_user_registration($entry, $form)
    {
        if (!isset($entry["1002.1"])) return;
        if ($entry["1002.1"] !== "DEMO") return;
        $program = Docc_Programs::get_program_by_name("DEMO Program");
        $label = "Email";
        $email_id = $this->GetFieldId($form, $label);
        $label = "Select your role in the organization";
        $role_id = $this->GetFieldId($form, $label);
        $this->add_user_to_program($program->ID, $entry[$email_id], $entry[$role_id]);
    }

    public function add_user_to_program($program_id, $email, $user_role)
    {
        if ($program_id === null) return;

        $user = get_user_by('login', $email);
        if (!$user) $user = get_user_by('email', $email);

        if (!$user) return;
        $role = $this->get_role($user_role);

        // if ($role === 'directors') return; // TODO
        $users = get_post_meta($program_id, $role, true);
        if (gettype($users) == "string") $users = [$user->ID];
        else $users[] = $user->ID;
        update_post_meta($program_id, $role, array_filter($users));
        $meta_role = $this->get_meta_role($role);
        $meta_users = get_post_meta($program_id, $meta_role, true);
        $meta_users[] = strval($user->ID);
        update_post_meta($program_id, $meta_role, array_filter($meta_users));
    }

    public function get_meta_role($role)
    {
        switch ($role) {
            case "directors":
                return "director_ids";
            case "faculty":
                return "faculty_ids";
            case "residents":
                return "resident_ids";
        }

        return $role;
    }

    public function get_role($role)
    {
        switch (strtolower($role)) {
            case "program director":
            case "program_director":
                return "directors";
            case "faculty":
            case "observer":
                return "faculty";
            case "resident":
                return "residents";
        }

        return $role;
    }

    public function gform_pre_submission($form)
    {
        switch ($form['title']) {
            case "Invite User to Program":
                $this->populate_invite_message($form);
                $this->verify_user($form);
                break;
        }
    }

    public function populate_invite_message($form)
    {
        $label = "Program";
        $field_id = $this->GetFieldId($form, $label);
        $program_name = rgpost("input_$field_id");
        $program = get_posts([
            'title'  => $program_name,
            'post_type'   => 'program',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ])[0];
        $invitation_message = get_post_meta($program->ID, 'invitation_message', true);
        $label = "Invitation Message";
        $field_id = $this->GetFieldId($form, $label);
        $_POST["input_$field_id"] = $invitation_message;
    }

    public function verify_user($form)
    {
        $label = "Email";
        $field_id = $this->GetFieldId($form, $label);
        $user_email = rgpost("input_$field_id");
        $user = get_user_by('email', $user_email);
        $label = "New User?";
        $field_id = $this->GetFieldId($form, $label);
        $_POST["input_$field_id"] = $user ? "No" : "Yes";
    }

    public function user_role_validation($validation_result)
    {
        $form = $validation_result['form'];

        switch ($form['title']) {
            case "Create Program":
                $label = "Program Title";
                $field_id = $this->GetFieldId($form, $label);
                $program_name = rgpost("input_$field_id");
                if ($this->program_exists($program_name)) {
                    $validation_result['is_valid'] = false;
                    $this->validation_failed($form, $field_id, "A program by this name already exists");
                }
                break;
            case "Invite User to Program":
                $label = "Program";
                $field_id = $this->GetFieldId($form, $label);
                if ($_GET['programID'] !== rgpost("input_$field_id")) {
                    $validation_result['is_valid'] = false;
                    wp_redirect(home_url("my-programs"));
                }
                $label = "Email";
                $field_id = $this->GetFieldId($form, $label);
                $user_email = rgpost("input_$field_id");
                $user = get_user_by('email', $user_email);
                if (!$user) return $validation_result;
                $label = "Select role in the program";
                $field_id = $this->GetFieldId($form, $label);
                $selected_role = $this->get_role_slug_from_name(rgpost("input_$field_id"));
                if (!in_array($selected_role, (array) $user->roles)) {
                    $validation_result['is_valid'] = false;
                    $this->validation_failed($form, $field_id, "The role you have selected is not associated with this user");
                }
                break;
        }

        $validation_result['form'] = $form;
        return $validation_result;
    }

    private function program_exists($program_name)
    {
        if (DOCC_Programs::get_program_by_name($program_name)) return true;
        return false;
    }

    public function validation_failed($form, $field_id, $message)
    {
        foreach ($form['fields'] as &$field) {
            if ($field->id == $field_id) {
                $field->failed_validation = true;
                $field->validation_message = $message;
                break;
            }
        }
    }

    public function get_role_slug_from_name($name)
    {
        switch ($name) {
            case "Program Director":
                return "program_director";
                break;
            case "Faculty":
                return "observer";
                break;
            case "Resident":
                return "resident";
                break;
        }
    }

    public function gform_pre_render_filter($form)
    {
        switch ($form['title']) {
            case "Invite User to Program":
                return $this->filter_programs_dropdown($form);
                break;
            case "New Observation":
                return $this->filter_programs_dropdown($form);
        }
        return $form;
    }

    private function get_random_firstname()
    {
        $first_names = ["Adam", "Bruce", "Charlie", "Dennis", "Earl", "Fergus", "Tom", "Tim", "Carl", "Jon", "Joe"];
        return $first_names[array_rand($first_names, 1)];
    }

    private function get_random_lastname()
    {
        $last_names = ["Jones", "Smith", "Johnson", "Thompson", "Gates", "Brooks", "Lamb", "Gregor", "Marly", "Sigurd"];
        return $last_names[array_rand($last_names, 1)];
    }

    public function filter_programs_dropdown($form)
    {
        $programs = self::get_user_programs(wp_get_current_user());

        foreach ($form['fields'] as $field) {
            if ($field->type != 'select') continue;
            switch ($field['label']) {
                case "Program ID":
                    $choices = [];
                    foreach ($programs as $program) {
                        $choices[] = ['text' => $program->post_title, 'value' => $program->post_title];
                    }
                    $field->placeholder = 'Select a Program';
                    $field->choices = $choices;
                    break;
                case "Select a Resident Name/Email":
                    $choices = [];
                    if (DOCC_Public::demo_mode_active()) {
                        for ($i = 0; $i < 10; $i++) {
                            $first = $this->get_random_firstname();
                            $last = $this->get_random_lastname();
                            $email = $first . $last . "@gmail.com";
                            $choices[] = ['text' => $email . " (" . $first . " " . $last . ")", 'value' => $email];
                        }
                    } else {
                        $residents = [];
                        foreach ($programs as $program) { // TODO: improve how initial list of residents is scraped from form
                            $program_residents = get_post_meta($program->ID, 'residents', true);
                            if (is_array($program_residents))
                                $residents = array_merge($residents, $program_residents);
                        }
                        foreach (array_unique($residents) as $ID) {
                            $user = get_user_by('ID', intval($ID));
                            $meta = get_user_meta($user->ID);
                            $email = $user->user_email;
                            $first = $meta['first_name'][0];
                            $last = $meta['last_name'][0];
                            if ($this->name_is_set($first) && $this->name_is_set($last)) {
                                $choices[] = ['text' => $email . " (" . $first . " " . $last . ")", 'value' => $email];
                            } else {
                                $choices[] = ['text' => $email, 'value' => $email];
                            }
                        }
                    }
                    $field->placeholder = 'Select a Resident';
                    $field->choices = $choices;
                    break;
                default:
                    // nothing
                    break;
            }
        }
        return $form;
    }

    public static function get_user_programs($user)
    {
        $programs = [];
        $all_programs = get_posts([
            'post_type'   => 'program',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ]);
        foreach ($all_programs as $program) {
            $directors = get_post_meta($program->ID, 'directors', true);
            if (is_array($directors) && in_array($user->ID, $directors)) $programs[] = $program;
            $faculty = get_post_meta($program->ID, 'faculty', true);
            if (is_array($faculty) && in_array($user->ID, $faculty)) $programs[] = $program;
            $residents = get_post_meta($program->ID, 'residents', true);
            if (is_array($residents) && in_array($user->ID, $residents)) $programs[] = $program;
        }
        return $programs;
    }

    public function name_is_set($name)
    {
        if ($name == "") return false;
        if (is_null($name)) return false;
        return true;
    }

    public function user_register($user_id)
    {
        $PROGRAM_ID = 'programID';
        $user = get_user_by('ID', $user_id);
        $user_meta = get_user_meta($user->ID);
        if (!isset($user_meta[$PROGRAM_ID])) return;
        $program_name = $user_meta[$PROGRAM_ID][0];
        $program = Docc_Programs::get_program_by_name($program_name);
        $email = $user->email;
        $role = $user->role;
        $this->add_user_to_program($program->ID, $email, $role);
        // delete_user_meta($user->ID, $PROGRAM_ID);
    }

    public function wp_login($user_login, $user)
    {
        $meta = get_user_meta($user->ID);
        if (!array_key_exists('demo_user', $meta) || is_null($meta['demo_user']) || !isset($meta['demo_user']) || count($meta['demo_user']) < 1) $demo_mode = "FULL";
        else $demo_mode = $meta['demo_user'][0];
        // if (!isset($meta['Demo']) || count($meta['Demo']) < 1) $demo_mode = "FULL";
        // else $demo_mode = $meta['Demo'][0];
        // $demo_mode = get_user_meta($user->ID, 'Demo', true);

        $email = $user->user_email;
        $role = $user->roles[0];

        if ($demo_mode === 'DEMO') {
            $program_name = "DEMO Program";
            $DEMO_program = Docc_Programs::get_program_by_name($program_name);
            $user_programs = self::get_user_programs($user);
            $add_to_DEMO = true;
            foreach ($user_programs as $program) {
                if ($program->post_title === $DEMO_program->post_title) $add_to_DEMO = false;
            }
            if ($add_to_DEMO) {
                $this->add_user_to_program($DEMO_program->ID, $email, $role);
            }
        } else {
            $user_programs = self::get_user_programs($user);
            foreach ($user_programs as $program) {
                if ($program->post_title === "DEMO Program") {
                    Docc_Programs::remove_user_from_program($user->ID, $program->post_title);
                }
            }
            $PROGRAM_ID = "programID";
            $program_name = get_user_meta($user->ID, $PROGRAM_ID, true);
            if (is_null($program_name) || $program_name === false || strlen($program_name) === 0) return;
            $program = Docc_Programs::get_program_by_name($program_name);
            if ($program == null || !is_object($program)) return;
            $this->add_user_to_program($program->ID, $email, $role);
            delete_user_meta($user->ID, $PROGRAM_ID);
        }
    }

    public static function GetFieldId($form, string $label)
    {
        foreach ($form['fields'] as $field) if ($label === $field['label']) return $field['id'];

        return null;
    }

    private function _getEntryValue($form, $entry, string $label)
    {
        foreach ($form['fields'] as $field) 
            if ($label === $field->label) 
                if (is_float($entry[$field->id]))
                    return strval($entry[$field->id]);
                else
                    return $entry[$field->id];
        
        return null;
    }
}
