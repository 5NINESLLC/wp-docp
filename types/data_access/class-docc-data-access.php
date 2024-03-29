<?php

/**
 * Data Access
 *
 * @link       https://design.garden
 * @since      1.0.0
 *
 * @package    Docc
 * @subpackage Docc/Data_Access
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Docc
 * @subpackage Docc/Data_Access
 * @author     Anthony Jacobs <tony@design.garden>
 */
class Data_Access extends Docc_Controller
{
    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name . "-data-access", plugin_dir_url(__FILE__) . 'css/docc-data-access.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name . "-data-access", plugin_dir_url(__FILE__) . 'js/docc-data-access.js', array('jquery'), $this->version, false);
    }

    public function disable_new_admins($all_roles)
    {
        unset($all_roles['administrator']);

        return $all_roles;
    }

    public function wp_ajax_scrub_phi_data()
    {
        $phi_entries = $this->get_user_phi_entries();

        $this->scrub_user_phi_data($phi_entries, "User Scrubbing");

        wp_die();
    }

    private function scrub_user_phi_data($entries, $action)
    {
        foreach ($entries as $entry) {
            $phi_entry = $this->check_for_phi($entry);

            if ($phi_entry['contains_phi']) $this->scrub_phi_entry($entry, $phi_entry, $action);
        }
    }

    private function scrub_phi_entry($entry, $phi_entry, $action)
    {
        foreach ($phi_entry as $field => $phi) {
            if ($field === 'contains_phi') continue;
            foreach ($phi[1] as $entity) {
                if ($entity->Type === 'PHONE_OR_FAX') $entity->Type = 'PHONE';
                $entry[$field] = str_replace($entity->Text, "[" . $entity->Type . "]", $entry[$field]);
            }
        }

        GFAPI::update_entry($entry);

        $this->send_phi_email($entry, $phi_entry);

        $this->log_phi_access($entry['id'], $phi_entry, $action);
    }

    private function log_phi_access($entry_id, $phi_entry, $action)
    {
        $phi_fields = [];

        foreach ($phi_entry as $field => $phi) {
            if ($field === 'contains_phi') continue;
            foreach ($phi[1] as $entity) {
                $phi_fields[] = $entity->Type;
            }
        }
        
        $logDirPath = '../hipaa_logs';
        $logFilePath = $logDirPath . '/log.txt';

        if (!is_dir($logDirPath)) {
            mkdir($logDirPath, 0755, true);
        }

        $logEntry = $action . ": Entry " . $entry_id . " [" . implode(", ", $phi_fields) . "]" . " on " . date('Y-m-d H:i:s') . PHP_EOL;

        file_put_contents($logFilePath, $logEntry, FILE_APPEND);
    }

    private function send_phi_email($entry, $phi_entry)
    {
        if (!isset($entry['created_by']) || $entry['created_by'] === "") return;

        $user = get_userdata($entry['created_by']);
        if (!$user) return;

        $to = $user->user_email;
        $subject = 'An observation you created contains PHI';
        $message = $this->format_phi_email($entry, $phi_entry);
        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($to, $subject, $message, $headers);
    }

    private function format_phi_email($entry, $phi_entry)
    {
        $message = '<html><body>';
        $message .= '<h1>Alert: PHI Detected in Your Submission</h1>';
        $message .= '<p>Dear User,</p>';
        $message .= '<p>We have detected that your recent submission in our system contains Protected Health Information (PHI). For privacy and compliance reasons, we have taken steps to secure this information.</p>';

        if (isset($phi_entry) && !empty($phi_entry)) {
            $message .= '<p>The following PHI elements were detected in your submission:</p>';
            $message .= '<ul>';

            foreach ($phi_entry as $field => $phi) {
                if ($field === 'contains_phi' || empty($phi[1])) continue;

                $message .= '<li>' . $entry[$field] . '</li>';
            }

            $message .= '</ul>';
        } else {
            $message .= '<p>No specific PHI details are available, but please review your submission for potential PHI.</p>';
        }

        $message .= '<p>See info about PHI and how our app handles it here: <a href="https://docc.acgme.org/hipaa">HIPAA Compliance</a></p>';
        $message .= '<p>Please be mindful of the information you share and avoid including any sensitive health information in your submissions.</p>';
        $message .= '<p>Thank you for your attention to this important matter.</p>';
        $message .= '</body></html>';

        return $message;
    }

    private function exec_phi_scrubber($entry_text)
    {
        if (empty($entry_text) || !$entry_text) return [];

        $entry_text = escapeshellarg($entry_text);

        $command = "ssh -i ~/TonyDesktop.pem ec2-user@44.202.217.41 aws comprehendmedical detect-phi --text \'{$entry_text}\'";

        exec("$command", $output);
        
        $jsonString = implode("", $output);

        $jsonObject = json_decode($jsonString);

        return $jsonObject;
    }

    public function docc_delete_old_data()
    {
        $users = get_users(['meta_key' => 'delete_old_data', 'meta_value' => true]);

        $form = $this->GetGravityFormByTitle("New Observation");
        
        foreach ($users as $user)
        {
            $five_years_ago = date('Y-m-d', strtotime('-5 years'));
            $search_criteria = [
                'status' => 'active', 
                'field_filters' => [
                    ['key' => 'created_by', 'value' => $user->ID],
                    ['key' => 'date_updated', 'value' => $five_years_ago, 'operator' => '<=']
                ]
            ];
            $entries = GFAPI::get_entries($form['id'], $search_criteria);

            foreach ($entries as $entry) GFAPI::delete_entry($entry['id']);
        }

        wp_die();
    }

    public function docc_phi_scrubber()
    {
        $entries = $this->get_daily_phi_entries();

        $this->scrub_user_phi_data($entries, "Daily Scrubbing");

        wp_die();
    }

    public function wp_ajax_toggle_delete_old_data()
    {
        if (!isset($_GET['isChecked'])) wp_die();

        $user_id = get_current_user_id();
        $isChecked = $_GET['isChecked'] === 'true' ? true : false;

        update_user_meta($user_id, 'delete_old_data', $isChecked);

        echo $isChecked ? 'Enabled' : 'Disabled';

        wp_die();
    }

    public function wp_ajax_get_delete_old_data_setting()
    {
        $user_id = get_current_user_id();

        $delete_old_data = get_user_meta($user_id, 'delete_old_data', true);

        echo $delete_old_data;

        wp_die();
    }

    private function check_for_phi($entry) {
        $phi_fields = [11, 13, 15];

        $entry_phi['contains_phi'] = false;

        foreach ($phi_fields as $field) $entry_phi[$field] = $this->check_phi_fields($entry[$field]);

        foreach ($entry_phi as $phi) if (isset($phi[0]) && $phi[0]) $entry_phi['contains_phi'] = true;

        return $entry_phi;
    }

    private function check_phi_fields($entry_text)
    {
        $scrubber_result = $this->exec_phi_scrubber($entry_text);

        if (isset($scrubber_result->Entities) && !empty($scrubber_result->Entities)) return [true, $scrubber_result->Entities];
        
        return [false, []];
    }

    private function get_user_phi_entries()
    {
        $user_id = get_current_user_id();
        $search_criteria = ['status' => 'active', 'field_filters' => [['key' => 'created_by', 'value' => $user_id]]];
        $form = $this->GetGravityFormByTitle("New Observation");
        $entries = GFAPI::get_entries($form['id'], $search_criteria);

        $phi_fields = [];

        foreach ($entries as $entry) {
            $phi_entry = $this->check_for_phi($entry);

            if ($phi_entry['contains_phi'])
            {
                $phi_fields[] = $entry;
                $this->log_phi_access($entry['id'], $phi_entry, "User Access");
            }
        }


        return $phi_fields;
    }

    private function get_daily_phi_entries()
    {
        $search_criteria = ['status' => 'active', 'field_filters' => [['key' => 'date_created', 'value' => date('Y-m-d', strtotime('-1 day')), 'operator' => '>=']]];
        $form = $this->GetGravityFormByTitle("New Observation");
        $entries = GFAPI::get_entries($form['id'], $search_criteria);

        return $entries;
    }

    private function highlight_phi($entry, $phi_entry)
    {
        foreach ($phi_entry as $field => $phi) {
            if ($field === 'contains_phi') continue;
            foreach ($phi[1] as $entity) {
                $entry[$field] = str_replace($entity->Text, "<span class='phi-highlight'>" . $entity->Text . "</span>", $entry[$field]);
            }
        }

        return $entry;
    }

    public function observations_with_phi($atts)
    {
        $form = $this->GetGravityFormByTitle("New Observation");
        $entries = $this->get_user_phi_entries();

        foreach ($entries as &$entry) {
            $phi_entry = $this->check_for_phi($entry);

            if ($phi_entry['contains_phi']) $entry = $this->highlight_phi($entry, $phi_entry);
        }

        return $this->DisplayObservations($form, $entries);
    }

    private function DisplayObservations($form, $entries)
    {
        return $this->Partial("tables/display_observations.php", compact("form", "entries"));
    }

    protected function format_date($date)
    {
        if ($date !== "" && $date !== null) {
            $centralTimezone = new DateTimeZone('America/Chicago');
            $date_utc = new DateTime($date, new DateTimeZone('UTC'));
            $date_utc->setTimezone($centralTimezone);
            return $date_utc->format('m/d/Y');
        }
        return $date;
    }

    public function gform_after_submission($entry, $form)
    {
        if ($form['title'] !== "New Observation") return;

        $phi_entry = $this->check_for_phi($entry);

        if ($phi_entry['contains_phi']) $this->scrub_phi_entry($entry, $phi_entry, "GF Submission");
    }
}