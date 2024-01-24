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
        echo $this->get_user_phi_entries();

        wp_die();
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

    private function contains_phi($value) {
        return true;
    }

    private function get_user_phi_entries()
    {
        $user_id = get_current_user_id();
        $search_criteria = ['status' => 'active', 'field_filters' => [['key' => 'created_by', 'value' => $user_id]]];
        $paging = array('offset' => 0, 'page_size' => 100);
        $form = $this->GetGravityFormByTitle("New Observation");
        $entries = GFAPI::get_entries($form['id'], $search_criteria);

        $phi_fields = [];

        foreach ($entries as $entry) {
            if ($this->contains_phi($entry)) {
                $phi_fields[] = $entry;
                /*[
                    'entry_id' => $entry['id'],
                    3 => $entry[3],
                    11 => $entry[11],
                    13 => $entry[13],
                    15 => $entry[15]
                ];*/
            }
        }

        return json_encode($phi_fields);
    }

    public function observations_with_phi($atts)
    {
        $form = $this->GetGravityFormByTitle("New Observation");
        $entries = $this->get_user_phi_entries();
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
}