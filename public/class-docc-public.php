<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://design.garden
 * @since      1.0.0
 *
 * @package    Docc
 * @subpackage Docc/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Docc
 * @subpackage Docc/public
 * @author     Anthony Jacobs <tony@design.garden>
 */
class Docc_Public extends Docc_Controller
{

    private $OBSERVATION_FORM_TITLE = "New Observation";

    private $AllowedSlugs = [
        "",
        "register",
        "demo",
        "resetpass",
        "self-publish"
    ];
    
    public function enqueue_manifest()
    {
        if (!is_page()) return;

        echo "<link rel='manifest' href='".$this->get_plugin_url() . $this->partials_path . 'pwa/manifest.json'."' />";
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        /*
         *  Boostrap: Latest compiled and minified CSS 
         */
        wp_enqueue_style($this->plugin_name . "-bootstrap", 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css', array(), $this->version, 'all');

        /*
         *  https://google.github.io/material-design-icons/
         */
        wp_enqueue_style($this->plugin_name . "-material-design", 'https://fonts.googleapis.com/icon?family=Material+Icons', array(), $this->version, 'all');

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/docc-public.css', array(), $this->version, 'all');

        wp_enqueue_style($this->plugin_name . "-dataTables", "https://cdn.datatables.net/1.10.23/css/jquery.dataTables.min.css", array(), $this->version, 'all');

        wp_enqueue_style($this->plugin_name . "-dataTablesResponsive", "https://cdn.datatables.net/responsive/2.2.7/css/responsive.dataTables.min.css", array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script( $this->plugin_name . "-service-worker", plugin_dir_url(__FILE__) . 'js/service-worker/sw.js', [], $this->version, false);
        wp_enqueue_script( $this->plugin_name . "-register-service-worker", plugin_dir_url(__FILE__) . 'js/register-service-worker.js', [$this->plugin_name . "-service-worker"], $this->version, false);
        wp_localize_script($this->plugin_name . "-register-service-worker", 'localized_vars', ["swUrlPath" => plugin_dir_url(__FILE__) . 'js/service-worker/sw.js']);

        /*
         * Bootstrap: Latest compiled and minified JavaScript
         */
        wp_enqueue_script($this->plugin_name . "-bootstrap", 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js', array('jquery'), $this->version, false);

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/docc-public.js', array('jquery'), $this->version, false);

        wp_enqueue_script($this->plugin_name . "-dataTables", 'https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js', array('jquery'), $this->version, false);

        wp_enqueue_script($this->plugin_name . "-dataTablesResponsive", 'https://cdn.datatables.net/responsive/2.2.7/js/dataTables.responsive.min.js', array('jquery'), $this->version, false);

        wp_enqueue_script($this->plugin_name . "-areYouSure", 'https://cdnjs.cloudflare.com/ajax/libs/jquery.AreYouSure/1.9.0/jquery.are-you-sure.min.js', array('jquery'), $this->version, false);

        wp_enqueue_script($this->plugin_name . "-export-config", plugin_dir_url(__FILE__) . 'js/export.js', array('jquery'), $this->version, false);

        wp_enqueue_script($this->plugin_name . "-x2js", 'https://cdnjs.cloudflare.com/ajax/libs/x2js/1.2.0/xml2json.min.js', array('jquery'), $this->version, false);

        // PWA

        //wp_enqueue_script($this->plugin_name . "-pwa-1", plugin_dir_url(__FILE__) . 'js/pwabuilder-sw.js', array('jquery'), $this->version, false);

        //wp_enqueue_script($this->plugin_name . "-pwa-2", plugin_dir_url(__FILE__) . 'js/pwabuilder-sw-register.js', array('jquery'), $this->version, false);
    }

    /** Observation tables */
    public function observations_table($atts)
    {
        if (self::demo_mode_active()) return $this->ObservationsDEMO();

        $form = $this->GetGravityFormByTitle($this->OBSERVATION_FORM_TITLE);

        $user = wp_get_current_user();

        $search_criteria['status'] = 'active';
        $search_criteria['field_filters'][] = ['key' => 'resume_url', 'value' => false];
        $search_criteria['field_filters'][] = ['key' => 'partial_entry_percent', 'value' => ''];

        if (is_array($atts) && array_key_exists('saved', $atts) && $atts['saved'] === 'saved') {
            // TODO: This has been replaced and should be removed...
            return do_shortcode("[saved_observations]");
        } else if (self::IsResident()) {

            $label = "Resident Email";
            $field_id = self::GetFieldId($form, $label);
            $search_criteria['field_filters'][] = ['key' => $field_id, 'value' => $user->user_email];
        } else if (self::IsObserver()) {

            $search_criteria['field_filters'][] = ['key' => 'created_by', 'value' => $user->ID];
        } else if (self::IsProgramDirector() && is_array($atts) && array_key_exists('option', $atts) && $atts['option'] === 'all') {
            $user_programs = DOCC_Programs_GF::get_user_programs($user);

            foreach ($user_programs as $program) $programs[] = $program->post_title;

            $label = "Program ID";
            $field_id = self::GetFieldId($form, $label);
            $search_criteria['field_filters'][] = ['key' => $field_id, 'operator' => 'in', 'value' => $programs];
        } else if (self::IsProgramDirector()) {
            $search_criteria['field_filters'][] = ['key' => 'created_by', 'value' => $user->ID];
        } else if (self::IsAdministrator()) {

            // do not filter entries

        } else {

            return "No entries to show";
        }

        $paging = array('offset' => 0, 'page_size' => 100);
        $entries = GFAPI::get_entries($form['id'], $search_criteria, null, $paging);
        return $this->DisplayObservations($form, $entries);
    }

    private function DisplayObservations($form, $entries)
    {

        return $this->Partial("tables/display_observations.php", compact("form", "entries"));
    }

    private function ObservationsDEMO()
    {
        $programs = ["Program 1", "Program 2", "Program 3", "Program 4", "Program 5"];
        $settings = ["Clinic", "Lab", "Remote"];
        $FOOs = ["History Taking", "Physical Examination", "Counseling/Shared Decision Making", "Breaking Bad News", "Hand-Off", "Clinical Reasoning"];
        $complexities = ["Low", "Medium", "High"];
        $first_names = ["Adam", "Bruce", "Charlie", "Dennis", "Earl", "Fergus", "Tom", "Tim", "Carl", "Jon", "Joe"];
        $last_names = ["Jones", "Smith", "Johnson", "Thompson", "Gates", "Brooks", "Lamb", "Gregor", "Marly", "Sigurd"];
        $strengths = ["Hard worker", "Problem solver", "Good for morale", "Efficient"];
        $AFIs = ["Managing time", "Staying on task", "Work ethic"];
        $summaries = ["Showed great progress", "Could use some improvement", "Ready for practice"];
        $PGYs = ["PGY"];

        return $this->Partial("tables/observations_demo.php", compact(
            "programs",
            "settings",
            "FOOs",
            "complexities",
            "first_names",
            "last_names",
            "strengths",
            "AFIs",
            "summaries",
            "PGYs"
        ));
    }

    public static function demo_mode_active()
    {
        $user = wp_get_current_user();
        $meta = get_user_meta($user->ID);
        if (!isset($meta['demo_user'])) return false;
        if (count($meta['demo_user']) < 1) return false;
        if ($meta['demo_user'][0] === "DEMO") return true;
        return false;
    }

    /** Private helper functions */

    /**
     * 
     * @param int $form_id 
     * @param int $user_id 
     * @param bool $saved 
     * @return iterable 
     */
    private function GetUserEntries(int $form_id, int $user_id, bool $saved = false)
    {
        $search_criteria = [
            'status'        => 'active',
            'field_filters' => [
                [
                    'key' => 'created_by',
                    'value' => $user_id
                ],
                [
                    'key' => 'resume_url',
                    'operator' => $saved ? '!=' : '=',
                    'value' => false
                ],
                [
                    'key' => 'partial_entry_percent',
                    'operator' => $saved ? '!=' : '=',
                    'value' => false
                ],
            ]
        ];

        return GFAPI::get_entries($form_id, $search_criteria); //, $sorting = null, $paging = null, $total_count = null );
    }

    protected function format_date($date)
    { // TODO: Used by "display_observations.php"
        if ($date !== "" && $date !== null) {
            $y_m_d = explode(' ', $date)[0];
            $date = explode('-', $y_m_d);
            return  $date[1] . "/" . $date[2] . "/" . $date[0];
        }
        return $date;
    }

    public function gform_pre_submission($form)
    {
        switch ($form['title']) {
            case "New Observation":
                $current_user = wp_get_current_user();

                $label = "Faculty Email";
                $field_id = self::GetFieldId($form, $label);
                $_POST["input_$field_id"] = $current_user->user_email;

                $label = "Resident Email";
                $resident_email_field_id = self::GetFieldId($form, $label);
                $label = "Select a Resident";
                $field_id = self::GetFieldIdByStartOfLabel($form, $label);
                $_POST["input_$resident_email_field_id"] = rgpost("input_$field_id");
                break;
            default:
                // nothing
                break;
        }
    }

    public function init()
    {
        // TODO: This is a blanket-solution to the bug that let app-users return
        //       to the login screen while logged-in...
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    }

    public function gform_suppress_confirmation_redirect($confirmation)
    {
        return true;
    }

    public function gform_post_process($form, $page_number, $source_page_number)
    {
        $saving_for_later = rgpost('gform_save') ? true : false;

        if (!$saving_for_later) return;

        // TODO: hook not needed anymore...
    }

    public function debug_tags($tag)
    {
        global $debug_tags;
        if (in_array($tag, $debug_tags)) {
            return;
        }
        echo "<pre>" . $tag . "</pre>";
        $debug_tags[] = $tag;
    }

    public function add_chart()
    {
        include($this->plugin_path . 'public/php/add-chart.php');
    }

    public function after_setup_theme()
    {
        if (!current_user_can('administrator') && !is_admin()) {
            show_admin_bar(false);
        }
    }

    public function auto_redirect_after_logout()
    {
        wp_redirect(home_url("?loggedout=true"));
        exit();
    }

    public function template_redirect()
    {
        //        if (current_user_can('administrator')) return;

        $this->_pageRedirects();
    }

    function wp_login_failed($username)
    {
        $referrer = wp_get_referer();

        if ($referrer && !strstr($referrer, 'wp-login') && !strstr($referrer, 'wp-admin')) {
            wp_redirect(add_query_arg(['login' => 'failed', 'username' => $username], $referrer));
            exit;
        }
    }

    function authenticate($user, $username, $password)
    {
        if (empty($username) || empty($password)) {
            $error = new WP_Error();
            $user  = new WP_Error('authentication_failed', __('<strong>ERROR</strong>: Invalid username or incorrect password.'));

            return $error;
        }

        return $user;
    }

    public function gform_incomplete_submissions_expiration_days($expiration_days)
    {
        return 90;
    }

    /**
     * Redirect user after successful login.
     *
     * @param string $redirect_to URL to redirect to.
     * @param string $request URL the user is coming from.
     * @param object $user Logged user's data.
     * @return string
     */
    public function login_redirect($redirect_to, $request, $user)
    {
        if (!isset($user->roles) || !is_array($user->roles)) return $redirect_to;

        if (current_user_can('administrator') || is_network_admin()) return $redirect_to;

        if (in_array('program_director', (array) $user->roles)) return home_url("dashboard");

        if (in_array('observer', (array) $user->roles)) return home_url("dashboard");

        if (in_array('resident', (array) $user->roles)) return home_url("resident-dashboard");

        if (in_array('subscriber', (array) $user->roles)) return home_url("resident-dashboard");

        if (empty((array) $user->roles)) {
            wp_destroy_current_session();
            wp_clear_auth_cookie();
            return add_query_arg(['login' => 'wrongsite', 'username' => $user->user_login], home_url());
        }

        return $redirect_to;
    }

    private function _pageRedirects()
    {
        $requested_slug = $this->GetSlugOfRequestedPage();

        if ($requested_slug == "program-registration") {
            $programID = $_GET['programID'];
            if (is_null($programID)) wp_redirect(home_url("my-programs"));
            if (gettype($programID) !== 'string') wp_redirect(home_url("my-programs"));
            if ($programID === '') wp_redirect(home_url("my-programs"));
        }

        if ($requested_slug == "logout") wp_logout();

        if ($requested_slug == "latest-saved-observation") {
            $form = $this->GetGravityFormByTitle($this->OBSERVATION_FORM_TITLE);
            $entries = $this->GetUserEntries($form['id'], get_current_user_id(), true);

            wp_redirect($entries[0]['resume_url']);
            exit;
        }

        if (current_user_can('administrator') || is_network_admin()) return;

        else if (is_user_logged_in()) {
            $default_page = $this->getDefaultPageForUser();

            // TODO: This fails on mobile. It seems like mobile restarts app
            //       when it sees the person heading to the base path.
            if ($requested_slug === "")
                wp_redirect(home_url($default_page));
            else {
                // do nothing.
            }
        } else {
            $default_page = "";

            $somfrp_action = filter_input(INPUT_POST, 'somfrp_action');
            $somfrp_user_info = filter_input(INPUT_POST, 'somfrp_user_info');

            if ($somfrp_action === 'somfrp_lost_pass' && strlen($somfrp_user_info) > 0) {
                // TODO: This breaks the password reset...

                //                wp_redirect( add_query_arg( 'resetpass', $somfrp_user_info , home_url( $default_page ) ) );
                //                die;
            } else if (!in_array($requested_slug, $this->AllowedSlugs)) {
                wp_redirect(home_url($default_page));
                die;
            } else {
                // do nothing
            }
        }
    }

    public function query_message($atts, $content = null)
    {
        $height = isset($atts['height']) ? "" : "";

        return $this->Partial("notices/query_message.php", compact("height"));
    }

    public function getDefaultPageForUser(): string
    {
        if (self::IsObserver()) return "dashboard";

        if (self::IsProgramDirector()) return "dashboard";

        if (self::IsResident()) return "resident-dashboard";

        return "";
    }

    /**
     * Gravity Forms Custom Activation Template
     * http://gravitywiz.com/customizing-gravity-forms-user-registration-activation-page
     */
    public function custom_maybe_activate_user()
    {
        $template_path    = $this->plugin_path . 'vendor/' . 'gfur-activate-template';
        $is_activate_page = isset($_GET['page']) && $_GET['page'] === 'gf_activation';
        $is_activate_page = $is_activate_page || isset($_GET['gfur_activation']); // WP 5.5 Compatibility

        if (!file_exists($template_path . "/activate.php") || !$is_activate_page) {
            return;
        }

        if (!defined('STYLESHEETPATH')) define('STYLESHEETPATH', $this->plugin_path . 'vendor/');

        require_once($template_path . "/activate.php");

        exit();
    }

    /**
     * Filters the callback for killing WordPress execution for all non-Ajax, non-JSON, non-XML requests.
     *
     * @param [Callable] Callback function name.
     * @return void
     */
    public function wp_die_handler($function)
    {
        return [$this, 'wp_die_handler_callable'];
    }

    public function wp_die_handler_callable($message, $title = '', $args = [])
    {
        if (is_wp_error($message) && array_key_exists("somfrp_error",$message->errors) && count($message->errors["somfrp_error"])>0)
            $args['response'] = 401;
        
        _default_wp_die_handler( $message, $title, $args);
    }
}