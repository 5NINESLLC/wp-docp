<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://design.garden
 * @since      1.0.0
 *
 * @package    Docc
 * @subpackage Docc/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Docc
 * @subpackage Docc/includes
 * @author     Anthony Jacobs <tony@design.garden>
 */
class Docc
{

    protected $updater;

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Docc_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    protected $plugin_path;
    protected $plugin_url;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        $this->version = defined('DOCC_VERSION') ? DOCC_VERSION : '1.0.0';
        $this->plugin_name = 'DOCC';
        $this->plugin_path = plugin_dir_path(dirname(__FILE__));
        $this->plugin_url = plugin_dir_url(dirname(__FILE__));

        $this->check_for_updates();

        $this->load_dependencies();
        $this->set_locale();

        $this->define_programs_hooks();
        $this->define_data_access_hooks();

        if (is_admin() || is_customize_preview())
        {
            $this->define_admin_hooks();
        }
        else
        {
            $this->define_public_hooks();
            $this->define_shortcode_hooks();
        }

        $this->schedule_cron_jobs();
    }

    /**
     * 
     */
    private function check_for_updates()
    {
        require_once $this->plugin_path . 'includes/class-docc-updater.php';

        $this->updater = new Docc_Updater();
    }

    private function schedule_cron_jobs()
    {
        if (!wp_next_scheduled('docc_delete_old_data')) wp_schedule_event(time(), 'daily', 'docc_delete_old_data');

        if (!wp_next_scheduled('docc_phi_scrubber')) wp_schedule_event(time(), 'daily', 'docc_phi_scrubber');
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Docc_Loader. Orchestrates the hooks of the plugin.
     * - Docc_i18n. Defines internationalization functionality.
     * - Docc_Admin. Defines all hooks for the admin area.
     * - Docc_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once $this->plugin_path . 'includes/class-docc-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once $this->plugin_path . 'includes/class-docc-i18n.php';

        /**
         * The abstract controller classes.
         */
        require_once $this->plugin_path . 'core/controllers/abstract-controller.php';
        require_once $this->plugin_path . 'core/controllers/abstract-wordpress-controller.php';
        require_once $this->plugin_path . 'core/controllers/abstract-docc-controller.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once $this->plugin_path . 'admin/class-docc-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once $this->plugin_path . 'public/class-docc-public.php';

        /**
         * The class responsible for defining all shortcodes that occur in the public-facing
         * side of the site.
         */
        require_once $this->plugin_path . 'shortcodes/class-docc-shortcodes.php';

        /**
         * Classes for the Programs post-type
         */
        require_once $this->plugin_path . 'types/programs/class-docc-programs.php';
        require_once $this->plugin_path . 'types/programs/shortcodes/class-docc-programs-shortcodes.php';
        require_once $this->plugin_path . 'types/programs/gravity-forms/class-docc-programs-gravity-forms.php';

        require_once $this->plugin_path . 'types/data_access/class-docc-data-access.php';

        $this->loader = new Docc_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Docc_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Docc_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Docc_Admin($this->get_plugin_name(), $this->get_version(), $this->plugin_url, $this->get_plugin_path());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        $this->loader->add_action('admin_init', $plugin_admin, 'admin_init');

        $this->loader->add_action('admin_menu', $plugin_admin, 'docc_admin_page');

        $this->loader->add_action('activated_plugin', $plugin_admin, 'activated_plugin');

        $this->loader->add_action('wp_ajax_install_theme', $plugin_admin, 'wp_ajax_install_theme');
        $this->loader->add_action('wp_ajax_activate_theme', $plugin_admin, 'wp_ajax_activate_theme');
        
        $this->loader->add_action('wp_ajax_install_plugin', $plugin_admin, 'wp_ajax_install_plugin');
        $this->loader->add_action('wp_ajax_activate_plugin', $plugin_admin, 'wp_ajax_activate_plugin');

        $this->loader->add_action('wp_ajax_setup_status', $plugin_admin, 'wp_ajax_setup_status');

        $this->loader->add_action('wp_ajax_add_user_roles', $plugin_admin, 'wp_ajax_add_user_roles');
        $this->loader->add_action('wp_ajax_import_pages', $plugin_admin, 'wp_ajax_import_pages');
        $this->loader->add_action('wp_ajax_import_forms', $plugin_admin, 'wp_ajax_import_forms');
        $this->loader->add_action('wp_ajax_import_feeds', $plugin_admin, 'wp_ajax_import_feeds');
        $this->loader->add_action('wp_ajax_misc_settings', $plugin_admin, 'wp_ajax_misc_settings');

        $this->loader->add_action('wp_ajax_setup_support_email', $plugin_admin, 'wp_ajax_setup_support_email');
        $this->loader->add_action('wp_ajax_rerun_automatic_setup', $plugin_admin, 'wp_ajax_rerun_automatic_setup');

        $this->loader->add_action('wp_ajax_test_email', $plugin_admin, 'wp_ajax_test_email');
        $this->loader->add_action('wp_ajax_setup_complete', $plugin_admin, 'wp_ajax_setup_complete');

        $this->loader->add_action('show_user_profile', $plugin_admin, 'extra_user_profile_fields');
        $this->loader->add_action('edit_user_profile', $plugin_admin, 'extra_user_profile_fields');

        $this->loader->add_action('wp_ajax_gf_id', $plugin_admin, 'gf_id');

        $this->loader->add_action('wp_ajax_as_error_log', $plugin_admin, 'as_error_log');

        $this->loader->add_action('wp_ajax_get_password_reset_link', $plugin_admin, 'get_password_reset_link');

        // TESTING
        // $this->loader->add_action( 'wp_ajax_nopriv_add_user_roles', $plugin_admin, 'wp_ajax_add_user_roles' );
        // $this->loader->add_action('wp_ajax_nopriv_import_pages', $plugin_admin, 'wp_ajax_import_pages');
        // $this->loader->add_action( 'wp_ajax_nopriv_import_nav_menu_roles', $plugin_admin, 'wp_ajax_import_nav_menu_roles' );
        // $this->loader->add_action('wp_ajax_nopriv_get_setting', $plugin_admin, 'get_setting');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
        $plugin_public = new Docc_Public($this->get_plugin_name(), $this->get_version(), $this->get_plugin_path(), $this->get_plugin_url(), "public/partials/");

        $this->loader->add_action('wp_head', $plugin_public, 'enqueue_manifest');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('init', $plugin_public, 'init', 10);
        $this->loader->add_action('after_setup_theme', $plugin_public, 'after_setup_theme', 10);
        $this->loader->add_action('wp_login_failed', $plugin_public, 'wp_login_failed', 10, 1);
        $this->loader->add_action('gform_post_process', $plugin_public, 'gform_post_process', 10, 3);
        $this->loader->add_action('gform_pre_submission', $plugin_public, 'gform_pre_submission', 10, 1);
        $this->loader->add_action('template_redirect', $plugin_public, 'template_redirect', 10);
        $this->loader->add_action('wp_logout', $plugin_public, 'auto_redirect_after_logout', 100);
        $this->loader->add_action('wp', $plugin_public, 'custom_maybe_activate_user', 9);

        $this->loader->add_filter('authenticate', $plugin_public, 'authenticate', 30, 3);
        $this->loader->add_filter('login_redirect', $plugin_public, 'login_redirect', 10, 3);
        $this->loader->add_filter('gform_incomplete_submissions_expiration_days', $plugin_public, 'gform_incomplete_submissions_expiration_days', 10, 1);
        $this->loader->add_filter('wp_die_handler', $plugin_public, 'wp_die_handler');

        $this->loader->add_shortcode('query_message', $plugin_public, 'query_message');
        $this->loader->add_shortcode('add_chart', $plugin_public, 'add_chart');
        $this->loader->add_shortcode('past_observations', $plugin_public, 'past_observations');
        $this->loader->add_shortcode('past_user_observations', $plugin_public, 'past_user_observations');
        $this->loader->add_shortcode('past_res_observations', $plugin_public, 'past_res_observations');
        $this->loader->add_shortcode('observations_table', $plugin_public, 'observations_table', 10, 1);
    }

    /**
     * Register all of the hooks related to the shortcodes.
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_shortcode_hooks()
    {

        $plugin_shortcodes = new Docc_Shortcodes($this->get_plugin_name(), $this->get_version(), $this->get_plugin_path(), $this->get_plugin_url(), "shortcodes/partials/");

        $this->loader->add_action('wp_enqueue_scripts', $plugin_shortcodes, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_shortcodes, 'enqueue_scripts');

        $this->loader->add_shortcode('saved_observations', $plugin_shortcodes, 'saved_observations');

        $this->loader->add_shortcode('gravityform_by_title', $plugin_shortcodes, 'gravityform_by_title');

        $this->loader->add_shortcode('logout_link', $plugin_shortcodes, 'logout_link');
    }

    private function define_programs_hooks()
    {

        $programs = new Docc_Programs($this->get_plugin_name(), $this->get_version(), $this->get_plugin_path(), $this->get_plugin_url(), "types/programs/partials/");

        $this->loader->add_action('wp_enqueue_scripts', $programs, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $programs, 'enqueue_scripts');

        /**
         * Initial setup for custom post-type
         */
        $this->loader->add_action('init', $programs, 'register_post_type');

        /**
         * Admin scripts
         */
        $this->loader->add_action('wp_head', $programs, 'ajaxurl');
        $this->loader->add_action('wp_head', $programs, 'demo_mode');

        // $this->loader->add_action( 'wp_head', $plugin_programs, 'user_demo' );

        /**
         * Backend setup
         */
        $this->loader->add_action('add_meta_boxes', $programs, 'add_meta_boxes');
        $this->loader->add_action('save_post', $programs, 'save_meta');
        $this->loader->add_filter('manage_program_posts_columns', $programs, 'manage_program_posts_columns');
        $this->loader->add_filter('manage_program_posts_custom_column', $programs, 'manage_program_posts_custom_column', 10, 3);

        /**
         * Gravity Forms custom behavior
         */
        $gravity_forms = new Docc_Programs_GF($this->get_plugin_name(), $this->get_version(), $this->get_plugin_path(), $this->get_plugin_url(), "types/programs/partials/");

        $this->loader->add_action('gform_after_submission', $gravity_forms, 'gform_after_submission', 10, 2);
        $this->loader->add_filter('gform_pre_render', $gravity_forms, 'gform_pre_render_filter');
        $this->loader->add_filter('gform_pre_validation', $gravity_forms, 'gform_pre_render_filter');
        $this->loader->add_filter('gform_pre_submission_filter', $gravity_forms, 'gform_pre_render_filter');
        $this->loader->add_filter('gform_admin_pre_render', $gravity_forms, 'gform_pre_render_filter');
        $this->loader->add_action('gform_pre_submission', $gravity_forms, 'gform_pre_submission');
        $this->loader->add_filter('gform_validation', $gravity_forms, 'user_role_validation');
        $this->loader->add_action('user_register', $gravity_forms, 'user_register');
        $this->loader->add_action('wp_login', $gravity_forms, 'wp_login', 10, 2); // TODO: Move to public

        /**
         * AJAX calls
         */
        $this->loader->add_action('wp_ajax_switch_demo_mode', $programs, 'wp_ajax_switch_demo_mode');
        $this->loader->add_action('wp_ajax_get_program_residents', $programs, 'wp_ajax_get_program_residents');

        /**
         * Shortcodes
         */
        $programs_shortcodes = new Docc_Programs_Shortcodes($this->get_plugin_name(), $this->get_version(), $this->get_plugin_path(), $this->get_plugin_url(), "types/programs/partials/");

        $this->loader->add_shortcode('new_program', $programs_shortcodes, 'display_new_program_form');
        $this->loader->add_shortcode('program_registration', $programs_shortcodes, 'display_program_registration_form');

        $this->loader->add_shortcode('gf', $programs_shortcodes, 'display_gf', 10, 1);
        $this->loader->add_shortcode('programs', $programs_shortcodes, 'display_director_programs');
        $this->loader->add_shortcode('broadcast', $programs_shortcodes, 'display_broadcast_message');
        $this->loader->add_shortcode('user_programs', $programs_shortcodes, 'display_programs_on_dashboard');
        $this->loader->add_shortcode('program_members', $programs_shortcodes, 'display_program_members');
    }

    private function define_data_access_hooks()
    {
        $data_access = new Data_Access($this->get_plugin_name(), $this->get_version(), $this->get_plugin_path(), $this->get_plugin_url(), "types/data_access/partials/");

        $this->loader->add_action('wp_enqueue_scripts', $data_access, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $data_access, 'enqueue_scripts');

        $this->loader->add_action('editable_roles', $data_access, 'disable_new_admins', 10, 1);

        $this->loader->add_action('docc_delete_old_data', $data_access, 'docc_delete_old_data');
        $this->loader->add_action('docc_phi_scrubber', $data_access, 'docc_phi_scrubber');

        $this->loader->add_action('gform_after_submission', $data_access, 'gform_after_submission', 10, 2);

        $this->loader->add_action('wp_ajax_scrub_phi_data', $data_access, 'wp_ajax_scrub_phi_data');
        $this->loader->add_action('wp_ajax_toggle_delete_old_data', $data_access, 'wp_ajax_toggle_delete_old_data');
        $this->loader->add_action('wp_ajax_get_delete_old_data_setting', $data_access, 'wp_ajax_get_delete_old_data_setting');

        $this->loader->add_shortcode('observations_with_phi', $data_access, 'observations_with_phi', 10, 1);
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The plugin filepath.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_path()
    {
        return $this->plugin_path;
    }

    /**
     * The plugin url.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_url()
    {
        return $this->plugin_url;
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Docc_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
