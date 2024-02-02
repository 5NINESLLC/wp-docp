<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://design.garden
 * @since      1.0.0
 *
 * @package    Docc
 * @subpackage Docc/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Docc
 * @subpackage Docc/admin
 * @author     Anthony Jacobs <tony@design.garden>
 */
class Docc_Admin extends Docc_Controller
{
    protected $plugin_url;

    protected $plugin_path;

    private $theme_names = ['Divi'];

    private $plugin_names = [
        'advanced-custom-fields-font-awesome/acf-font-awesome.php' => 'Advanced Custom Fields: Font Awesome',
        'advanced-custom-fields-pro/acf.php' => 'Advanced Custom Fields PRO',
        'frontend-reset-password/som-frontend-reset-password.php' => 'Frontend Reset Password',
        'gravityforms/gravityforms.php' => 'Gravity Forms',
        'gravityformspartialentries/partialentries.php' => 'Gravity Forms Partial Entries Add-On',
        'gravityformsuserregistration/userregistration.php' => 'Gravity Forms User Registration Add-On',
        'gravityformswebhooks/webhooks.php' => 'Gravity Forms Webhooks Add-On',
        'importexport-add-on-feeds-for-gravity-forms/import-export-feeds-for-gravity-forms.php' => 'Import/Export Add-On Feeds for Gravity Forms',
        'GFChart/gfchart.php' => 'GFChart',
        'members/members.php' => 'Members',
        'nav-menu-roles/nav-menu-roles.php' => 'Nav Menu Roles',
        'shortcode-in-menus/shortcode-in-menus.php' => 'Shortcode in Menus',
        'styles-and-layouts-for-gravity-forms/styles-layouts-gravity-forms.php' => 'Styles & Layouts Gravity Forms',
        'wordpress-importer/wordpress-importer.php' => 'WordPress Importer',
        'wp-post-modal/wp-post-modal.php' => 'WP Post Popup'
    ];

    private $menu_slug = "docc-settings";

    private static $NavMenus = ["admin-menu" => 9, "default-menu" => 10, "main-menu" => 11, "resident-menu" => 12];

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version, $plugin_url, $plugin_path)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->plugin_url = $plugin_url;
        $this->plugin_path = $plugin_path;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/docc-admin.css', [], $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        $STATUS = $this->get_setup_status();

        // TODO: Use admin_enqueue_scripts

        wp_enqueue_script($this->plugin_name . "-setup", plugin_dir_url(__FILE__) . 'js/docc-setup.js', ['jquery'], $this->version, false);
        wp_localize_script($this->plugin_name . "-setup", 'localized_vars', [
            "pluginUrlPath" => $this->plugin_url,
            "setupStatus" => $STATUS,
            "autoSetup" => add_query_arg(['page' => 'auto-setup'], admin_url('admin.php')),
            "setupComplete" => add_query_arg(['page' => 'setup-complete'], admin_url('admin.php')),
            "adminAjax" => admin_url('admin-ajax.php'),
            "etThemeBuilder" => add_query_arg(['page' => 'et_theme_builder'], admin_url('admin.php')),
            "etThemeOptions" => add_query_arg(['page' => 'et_divi_options'], admin_url('admin.php')),
            "etCustomizerOptionSetTheme" => add_query_arg(['et_customizer_option_set' => 'theme'], admin_url('customize.php')),
        ]);

        wp_enqueue_script($this->plugin_name . "-admin", plugin_dir_url(__FILE__) . 'js/docc-admin.js', ['jquery'], $this->version, false);
    }

    private function get_setup_status(): int
    {
        switch (get_option('docc_setup_status'))
        {
            case '(0/3) Setup In Progress':
                return 1;
            case '(1/3) Guided Setup':
                return 2;
            case '(2/3) Automatic Setup':
                return 3;
            case '(2/3) Test Support Email (Optional)':
                return 4;
            case '(3/3) Test Email':
                return 5;
            case '(3/3) Setup Complete':
                return 6;
        }

        return 0;
    }

    public function wp_die_ajax_handler($function)
    {
        $error = error_get_last();

        // No error, just skip the error handling code.
        if (null === $error)
        {
            return $function;
        }

        // Bail if this error should not be handled.
        if (!$this->should_handle_error($error))
        {
            return $function;
        }

        return array($this, 'ajax_shutdown_function');
    }

    /**
     * Determines whether we are dealing with an error that WordPress should handle
     * in order to protect the admin backend against WSODs.
     *
     * @since 5.2.0
     *
     * @param array $error Error information retrieved from error_get_last().
     * @return bool Whether WordPress should handle this error.
     */
    protected function should_handle_error($error)
    {
        $error_types_to_handle = array(
            E_ERROR,
            E_PARSE,
            E_USER_ERROR,
            E_COMPILE_ERROR,
            E_RECOVERABLE_ERROR,
        );

        if (isset($error['type']) && in_array($error['type'], $error_types_to_handle, true)) return true;

        /**
         * Filters whether a given thrown error should be handled by the fatal error handler.
         *
         * This filter is only fired if the error is not already configured to be handled by WordPress core. As such,
         * it exclusively allows adding further rules for which errors should be handled, but not removing existing
         * ones.
         *
         * @since 5.2.0
         *
         * @param bool  $should_handle_error Whether the error should be handled by the fatal error handler.
         * @param array $error               Error information retrieved from error_get_last().
         */
        return (bool) apply_filters('wp_should_handle_php_error', false, $error);
    }

    public function ajax_shutdown_function()
    {
        @header('Content-Type: application/json; charset=' . get_option('blog_charset'));

        if (!headers_sent())
        {
            status_header(400);
            nocache_headers();
        }

        $response = [
            'success' => false,
            'notices' => error_get_last()
        ];

        echo wp_json_encode($response);
    }

    public function docc_admin_page()
    {
        $status = get_option('docc_setup_status');
        $isSetupInProgress = $status === '(0/3) Setup In Progress';
        $isGuidedSetup = $status === '(1/3) Guided Setup';
        $isSetupComplete = $status === '(3/3) Setup Complete';

        add_menu_page(
            'DOCC',
            'DOCC',
            'manage_options',
            $this->menu_slug,
            [$this, 'menu_page_guided_setup'],
            'dashicons-welcome-widgets-menus',
            1
        );
        
        remove_submenu_page($this->menu_slug, $this->menu_slug);

        add_submenu_page(
            $this->menu_slug,
            'DOCC Setup',
            'DOCC Setup',
            'manage_options',
            $this->menu_slug,
            [$this, 'menu_page_guided_setup'],
            2
        );
        add_submenu_page(
            $this->menu_slug, 
            'HIPAA', 
            'HIPAA', 
            'manage_options', 
            'hipaa', 
            [$this, 'menu_page_hipaa'],
            2
        );
        if (!$isSetupInProgress && !$isSetupComplete)
        {
            add_submenu_page(
                $this->menu_slug,
                'Import Settings',
                'Import Settings',
                'manage_options',
                'auto-setup',
                [$this, 'menu_page_automatic_setup'],
                3
            );
        }
        if (!$isSetupInProgress && !$isGuidedSetup && !$isSetupComplete)
        {
            add_submenu_page(
                $this->menu_slug,
                'Support',
                'Support',
                'manage_options',
                'docc-support',
                [$this, 'menu_page_docc_support'],
                3
            );
        }
        if (!$isSetupInProgress && !$isGuidedSetup)
        {
            add_submenu_page(
                $this->menu_slug,
                'Test Email',
                'Test Email',
                'manage_options',
                'test-email',
                [$this, 'menu_page_test_email'],
                3
            );
        }
        if ($isSetupComplete)
        {
            add_submenu_page(
                $this->menu_slug,
                'Setup Complete',
                'Setup Complete',
                'manage_options',
                'setup-complete',
                [$this, 'menu_page_setup_complete'],
                3
            );
        }
    }

    public function menu_page_docc_setup()
    {
        echo $this->Partial("admin/partials/setup/docc_setup.html");
    }

    public function menu_page_hipaa()
    {
        echo $this->Partial("admin/partials/setup/hipaa.php");
    }

    public function menu_page_guided_setup()
    {
        $plugins = $this->GetPlugins();
        $themes = $this->GetThemes();
        $subdirectory = $this->wordpress_is_installed_in_subdirectory();

        echo $this->Partial("admin/partials/setup/guided_setup.php", compact("themes", "plugins", "subdirectory"));
    }

    private function wordpress_is_installed_in_subdirectory(): bool
    {
        if (get_option('siteurl') !== get_option('home')) return true;

        if (strlen(rtrim(home_url('/', 'relative'), '/')) > 0) return true;

        return false;
    }

    public function menu_page_automatic_setup()
    {
        echo $this->Partial("admin/partials/setup/automatic_setup.php");
    }

    public function menu_page_docc_support()
    {
        echo $this->Partial("admin/partials/setup/docc_support.php", ["error_log" => get_option('docc_as_error_log')]);
    }

    public function menu_page_test_email()
    {
        echo $this->Partial("admin/partials/setup/test_email.html");
    }

    public function menu_page_setup_complete()
    {
        echo $this->Partial("admin/partials/setup/setup_complete.html");
    }

    /** AJAX */
    public function wp_ajax_install_theme()
    {
        $DEBUG = get_option('docc_debug', []);

        $theme_loc = $this->plugin_path . 'admin/dependencies/themes/Divi.zip';

        if (!file_exists($theme_loc))
        {
            $DEBUG['guided-setup']['theme_installed'][0] = false;
            $DEBUG['guided-setup']['theme_installed'][1][] = "ERROR: Unable to install theme. The theme file at $theme_loc does not exist.";

            update_option('docc_debug', $DEBUG);

            wp_die();
        }

        if (!class_exists('ZipArchive'))
        {
            $DEBUG['guided-setup']['theme_installed'][0] = false;
            $DEBUG['guided-setup']['theme_installed'][1][] = "ERROR: ZipArchive class is not available.";

            update_option('docc_debug', $DEBUG);

            wp_die();
        }

        $zip = new ZipArchive;

        if (true === $zip->open($theme_loc))
        {
            if ($zip->extractTo(get_theme_root())) {
                $zip->close();
                
                $DEBUG['guided-setup']['theme_installed'][0] = true;
            } else {
                $zip->close();

                $DEBUG['guided-setup']['theme_installed'][0] = false;
                $DEBUG['guided-setup']['theme_installed'][1][] = "ERROR: Unable to extract theme.";
            }
        } else {
            $DEBUG['guided-setup']['theme_installed'][0] = false;
            $DEBUG['guided-setup']['theme_installed'][1][] = "ERROR: Unable to open theme file at $theme_loc.";
        }

        update_option('docc_debug', $DEBUG);

        wp_die();
    }

    public function wp_ajax_activate_theme()
    {
        $DEBUG = get_option('docc_debug', []);

        if (wp_get_theme()->get_stylesheet() === 'Divi') wp_die();

        $theme = wp_get_theme('Divi');

        if ($theme->exists())
        {
            switch_theme('Divi');

            $DEBUG['guided-setup']['theme_active'][0] = true;
        }
        else
        {
            $DEBUG['guided-setup']['theme_active'][0] = false;
            $DEBUG['guided-setup']['theme_active'][1][] = "ERROR: Unable to activate theme, it is not installed.";
        }

        wp_die();
    }

    public function wp_ajax_install_plugin()
    {
        $DEBUG = get_option('docc_debug', []);

        if (!isset($_POST['plugin']) || trim($_POST['plugin']) === '')
        {
            $DEBUG['guided-setup']['plugins_installed'][0] = false;
            $DEBUG['guided-setup']['plugins_installed'][1][] = "WARNING: Function called without plugin defined.";
            update_option('docc_debug', $DEBUG);

            wp_die();
        }

        $slug = $this->GetSlugFromBase($_POST['plugin']);

        if (!is_string($slug))
        {
            $DEBUG['guided-setup']['plugins_installed'][0] = false;
            $DEBUG['guided-setup']['plugins_installed'][1][] = "WARNING: Tried to install plugin with invalid format i.e. not a string.";

            wp_die();
        }

        $slug = filter_var($slug, FILTER_SANITIZE_STRING);

        $plugin_loc = $this->plugin_path . 'admin/dependencies/plugins/' . $slug . '.zip';

        if (!file_exists($plugin_loc))
        {
            $DEBUG['guided-setup']['plugins_installed'][0] = false;
            $DEBUG['guided-setup']['plugins_installed'][1][] = "ERROR: Plugin file '$slug' not found at '$plugin_loc'.";
            update_option('docc_debug', $DEBUG);

            wp_die();
        }

        if (!class_exists('ZipArchive'))
        {
            $DEBUG['guided-setup']['plugins_installed'][0] = false;
            $DEBUG['guided-setup']['plugins_installed'][1][] = "ERROR: ZipArchive class is not available.";
            update_option('docc_debug', $DEBUG);

            wp_die();
        }

        $zip = new ZipArchive;

        if (true === $zip->open($plugin_loc))
        {
            $zip->extractTo(WP_PLUGIN_DIR);
            $zip->close();

            $DEBUG['guided-setup']['plugins_installed'][0] = $this->all_plugins_installed() ? true : false;
        }
        else
        {
            $DEBUG['guided-setup']['plugins_installed'][0] = false;
            $DEBUG['guided-setup']['plugins_installed'][1][] = "ERROR: Unable to install plugin. There was a problem opening the plugin '$slug' at '$plugin_loc'.";
        }

        update_option('docc_debug', $DEBUG);

        wp_die();
    }

    private function all_plugins_installed()
    {
        foreach ($this->plugin_names as $plugin_slug => $plugin_name)
        {
            if (!$this->get_plugin_install_status($plugin_slug)) return false;
        }
        return true;
    }

    public function wp_ajax_activate_plugin()
    {
        $DEBUG = get_option('docc_debug', []);

        $plugin = isset($_POST['plugin']) ? trim($_POST['plugin']) : '';

        if (!$plugin)
        {
            $DEBUG['guided-setup']['plugins_active'][0] = false;
            $DEBUG['guided-setup']['plugins_active'][1][] = "WARNING: Function called without plugin defined.";
            update_option('docc_debug', $DEBUG);

            wp_die();
        }

        if (!is_string($plugin))
        {
            $DEBUG['guided-setup']['plugins_active'][0] = false;
            $DEBUG['guided-setup']['plugins_active'][1][] = "WARNING: Tried to install plugin with invalid format i.e. not a string.";
            update_option('docc_debug', $DEBUG);

            wp_die();
        }

        $plugin = filter_var($plugin, FILTER_SANITIZE_STRING);

        $activation_result = activate_plugin($plugin);

        if (is_wp_error($activation_result))
        {
            $DEBUG['guided-setup']['plugins_active'][0] = false;
            $DEBUG['guided-setup']['plugins_active'][1][] = "ERROR: Unable to activate plugin. " . $activation_result->get_error_message();
        }
        else
        {
            $DEBUG['guided-setup']['plugins_active'][0] = $this->all_plugins_active();
        }
    
        update_option('docc_debug', $DEBUG);

        wp_die();
    }

    private function all_plugins_active()
    {
        foreach ($this->plugin_names as $plugin_slug => $plugin_name)
        {
            if (!$this->get_plugin_active_status($plugin_slug)) return false;
        }
        return true;
    }

    public function wp_ajax_setup_status()
    {

        if (!isset($_POST['status']) || trim($_POST['status']) === '')  wp_die();

        update_option('docc_setup_status', $_POST['status']);

        wp_die();
    }

    public function wp_ajax_add_user_roles()
    {
        $roles = [
            'resident' => 'Resident',
            'observer' => 'Observer',
            'program_director' => 'Program Director'
        ];

        foreach ($roles as $role => $display_name) {
            if (get_role($role)) continue;
    
            $added_role = add_role($role, $display_name, ['read' => true]);
            if (is_null($added_role)) {
                $DEBUG = get_option('docc_debug', []);
                $DEBUG['guided-setup']['add_user_roles'][0] = false;
                $DEBUG['guided-setup']['add_user_roles'][1][] = "ERROR: Unable to add role '$role'.";
                update_option('docc_debug', $DEBUG);
            }
        }
        
        wp_die();
    }

    public function wp_ajax_import_pages()
    {
        add_filter('wp_die_ajax_handler', [$this, 'wp_die_ajax_handler'], 100);

        $wordpress_import = $this->plugin_path . 'admin/dependencies/files/docc.ImportWordpress.xml';

        if (!file_exists($wordpress_import))
        {
            $DEBUG = get_option('docc_debug', []);
            $DEBUG['guided-setup']['import_pages'][0] = false;
            $DEBUG['guided-setup']['import_pages'][1][] = "ERROR: File not found at $wordpress_import.";
            update_option('docc_debug', $DEBUG);
            wp_die();
        }

        $this->_requireImportClasses();

        $wp_import = new WP_Import();
        $wp_import->fetch_attachments = true;
        
        ob_start(); // TODO: This isn't stopping the debug output from the importer...
        $wp_import->import($wordpress_import);
        ob_get_clean();

        $count_menus_fixed = 0;
        $count_menus_error = 0;
        
        foreach (self::$NavMenus as $slug => $id)
            if (!$this->_menuIdIsCorrect($slug, $id))
                if ($this->_tryToFixMenuId($slug, $id))
                    $count_menus_fixed++;
                else
                    $count_menus_error++;

        // TODO: Improve error messaging.

        if ($count_menus_error > 0) throw new Exception('Menus configuration failed.');

        wp_die();
    }

    private function _menuIdIsCorrect(string $slug, int $id): bool
    {
        $menu = get_term_by('slug', $slug, 'nav_menu');

        if ($menu === false) return false;

        return $menu->term_id === $id;
    }

    private function _tryToFixMenuId(string $slug, int $id, array $recursiveIds = []): bool
    {
        global $wpdb;

        $existing = get_term_by('id', $id);

        if ($existing === false)
        {
            // do nothing
        }
        else if ($existing->slug === $slug)
        {
            return true;
        }
        else if (array_key_exists($existing->slug, self::$NavMenus))
        {
            // Try to fix our other menu that is currently using this menu's intended ID.
            // TODO: Handle case when menus block eachother.
            if (in_array($id, $recursiveIds)) return false;
            if (count($recursiveIds) > count(self::$NavMenus)) return false;
            $recursiveIds[] = $id;
            if (!$this->_tryToFixMenuId($existing->slug, self::$NavMenus[$existing->slug], $recursiveIds)) return false;
        }
        else
        {
            // TODO: Be more careful about deleting the term.
            wp_delete_term($existing->term_id, $existing->taxonomy);
        }

        $menu = get_term_by('slug', $slug, 'nav_menu');

        if ($menu === false) return false;

        $old_term_id = $menu->term_id;

        $wpdb->update($wpdb->terms, ['term_id' => $id], ['term_id' => $old_term_id]);
        $wpdb->update($wpdb->term_taxonomy, ['term_id' => $id], ['term_id' => $old_term_id]);

        return true;
    }

    private function _requireImportClasses()
    {
        if (!class_exists('WP_Importer'))
        {
            $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
            require_once $class_wp_importer;
        }
        if (!class_exists('WP_Import'))
        {
            $class_wp_import = ABSPATH . 'wp-content/plugins/wordpress-importer/class-wp-import.php';
            require_once $class_wp_import;
        }
        if (!class_exists('WXR_Parser'))
        {
            $class_wxr_parser = ABSPATH . 'wp-content/plugins/wordpress-importer/parsers/class-wxr-parser.php';
            require_once $class_wxr_parser;
        }
        if (!class_exists('WXR_Parser_XML'))
        {
            $class_wxr_parser_xml = ABSPATH . 'wp-content/plugins/wordpress-importer/parsers/class-wxr-parser-xml.php';
            require_once $class_wxr_parser_xml;
        }
        if (!class_exists('WXR_Parser_SimpleXML'))
        {
            $class_wxr_parser_simplexml = ABSPATH . 'wp-content/plugins/wordpress-importer/parsers/class-wxr-parser-simplexml.php';
            require_once $class_wxr_parser_simplexml;
        }
        if (!class_exists('WXR_Parser_Regex'))
        {
            $class_wxr_parser_regex = ABSPATH . 'wp-content/plugins/wordpress-importer/parsers/class-wxr-parser-regex.php';
            require_once $class_wxr_parser_regex;
        }
    }

    public function wp_ajax_import_forms()
    {
        $DEBUG = get_option('docc_debug', []);

        $forms_import = $this->plugin_path . 'admin/dependencies/files/docc.GravityForms.json';

        if (!file_exists($forms_import))
        {
            $DEBUG['guided-setup']['import_forms'][0] = false;
            $DEBUG['guided-setup']['import_forms'][1][] = "ERROR: File not found at $forms_import.";
            update_option('docc_debug', $DEBUG);
            wp_die();
        }

        $forms_json_file = file_get_contents($forms_import);
        $forms_json = json_decode($forms_json_file, true);

        if (!class_exists('GFAPI'))
        {
            $DEBUG['guided-setup']['import_forms'][0] = false;
            $DEBUG['guided-setup']['import_forms'][1][] = "ERROR: GFAPI class does not exist.";
            update_option('docc_debug', $DEBUG);
            wp_die();
        }

        foreach ($forms_json as $form)
        {
            if (is_wp_error(GFAPI::add_form($form)))
            {
                $DEBUG['guided-setup']['import_forms'][0] = false;
                $DEBUG['guided-setup']['import_forms'][1][] = "ERROR: Unable to import form with title '{$form['title']}'.";
                update_option('docc_debug', $DEBUG);
                wp_die();
            }
        }

        $DEBUG['guided-setup']['import_forms'][0] = true;
        update_option('docc_debug', $DEBUG);

        wp_die();
    }

    public function wp_ajax_import_feeds()
    {
        $DEBUG = get_option('docc_debug', []);

        if (!class_exists('GFAPI'))
        {
            $DEBUG['guided-setup']['import_feeds'][0] = false;
            $DEBUG['guided-setup']['import_feeds'][1][] = "ERROR: GFAPI class does not exist.";
            update_option('docc_debug', $DEBUG);

            wp_die();
        }

        $this->importFeedsFromFile('docc.GravityFormsExportFeeds.UserRegistrationForm.json', "User Registration");
        $this->importFeedsFromFile('docc.GravityFormsExportFeeds.InviteUserToProgramForm.json', "Invite User to Program");

        wp_die();
    }
    
    private function importFeedsFromFile($fileName, $formTitle)
    {
        $DEBUG = get_option('docc_debug', []);
        
        $filePath = $this->plugin_path . 'admin/dependencies/files/' . $fileName;
    
        if (!file_exists($filePath)) {
            $DEBUG['guided-setup']['import_feeds'][0] = false;
            $DEBUG['guided-setup']['import_feeds'][1][] = "ERROR: File not found at $filePath.";
            update_option('docc_debug', $DEBUG);

            wp_die();
        }
    
        $feeds_json_file = file_get_contents($filePath);
        $feeds_json = json_decode($feeds_json_file, true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            $DEBUG['guided-setup']['import_feeds'][0] = false;
            $DEBUG['guided-setup']['import_feeds'][1][] = "ERROR: Failed to decode JSON file '$fileName'.";
            update_option('docc_debug', $DEBUG);

            wp_die();
        }
    
        $form = $this->GetGravityFormByTitle($formTitle);
        if (is_null($form))
        {
            $DEBUG['guided-setup']['import_feeds'][0] = false;
            $DEBUG['guided-setup']['import_feeds'][1][] = "ERROR: Form with title '$formTitle' not found.";
            update_option('docc_debug', $DEBUG);

            wp_die();
        }
    
        foreach ($feeds_json as $feed)
        {
            if ($this->feedAlreadyExists($feed, $form['id'])) continue;
    
            if (is_array($feed))
            {
                if (is_wp_error(GFAPI::add_feed($form['id'], $feed['meta'], $feed['addon_slug'])))
                {
                    $DEBUG['guided-setup']['import_feeds'][0] = false;
                    $DEBUG['guided-setup']['import_feeds'][1][] = "ERROR: Unable to import feed with title '{$feed['meta']['feedName']}'.";
                    update_option('docc_debug', $DEBUG);

                    wp_die();
                }
            }
        }
    }

    private function feedAlreadyExists($feed, $form_id)
    {
        $existing_feeds = GFAPI::get_feeds(null, $form_id, $feed['addon_slug']);
        foreach ($existing_feeds as $existing_feed) {
            if ($existing_feed['meta'] == $feed['meta']) {
                return true;
            }
        }
        return false;
    }

    public function gf_id()
    {
        if (!isset($_POST['title']) || trim($_POST['title']) === '') wp_die();

        $form = $this->GetGravityFormByTitle($_POST['title']);

        echo $form['id'];

        wp_die();
    }

    public function wp_ajax_misc_settings()
    {
        // TODO: add debug checking

        // Set static homepage
        $home = get_page_by_title('Home'); // TODO: add error check
        update_option('page_on_front', $home->ID);
        update_option('show_on_front', 'page');

        // Set “Search Engine Visibility” to “Discourage search engines from indexing this site”
        update_option('blog_public', '0');

        // Set the Permalinks setting to “Post name”
        update_option('permalink_structure', '/%postname%/');

        // Set the primary navigation menu to “Main Menu”
        $menu = get_term_by('name', 'Main Menu', 'nav_menu');
        $locations = get_theme_mod('nav_menu_locations');
        $locations['primary-menu'] = $menu->term_id;
        set_theme_mod('nav_menu_locations', $locations);

        // TODO: Apply role-based menu restrictions
        // $nav_menu_roles_import_file = $this->plugin_path . 'admin/dependencies/files/docc.importWordpress.xml';
        // if (file_exists($nav_menu_roles_import_file)) $this->import_nav_menu_roles($nav_menu_roles_import_file);

        // TODO: Apply role-based header restrictions
        update_option('docc_setup_status', '(2/3) Automatic Setup');

        wp_die();
    }

    public function as_error_log()
    {

        if (!isset($_POST['error_log']) || (is_string($_POST['error_log']) && trim($_POST['error_log'])) === '') wp_die();

        update_option('docc_as_error_log', $_POST['error_log']);

        wp_die();
    }

    /*private function import_nav_menu_roles($file)
    {

        define('WP_LOAD_IMPORTERS', true);

        if (!file_exists($file)) wp_die();

        $this->_requireImportClasses();

        if (!class_exists('Nav_Menu_Roles_Import'))
        {
            $class_nav_menu_roles_importer = ABSPATH . 'wp-content/plugins/nav-menu-roles/inc/class-nav-menu-roles-import.php';
            require_once $class_nav_menu_roles_importer;
        }

        $nav_menu_roles_import = new Nav_Menu_Roles_Import();

        $nav_menu_roles_import->import($file);
    }*/

    public function wp_ajax_rerun_automatic_setup()
    {
        update_option('page_on_front', '0');
        update_option('show_on_front', 'posts');

        update_option('blog_public', '1');

        update_option('permalink_structure', '/%year%/%monthnum%/%day%/%postname%/');

        $locations = get_theme_mod('nav_menu_locations');
        if (array_key_exists('primary-menu', $locations))
        {
            $locations['primary-menu'] = 0;
            set_theme_mod('nav_menu_locations', $locations);
        }

        // TODO: Only delete Pages, Posts and Forms that were created by this plugin.

        $pages = get_pages();
        foreach ($pages as $page)
        {
            wp_delete_post($page->ID, true);
        }

        $posts = get_posts();
        foreach ($posts as $post)
        {
            wp_delete_post($post->ID, true);
        }

        $menu_names = [
            'Admin Menu',
            'Main Menu',
            'Default Menu',
            'Resident Menu'
        ];
        foreach ($menu_names as $name)
        {
            $menu = get_term_by('name', $name, 'nav_menu');
            wp_delete_term($menu->term_id, $menu->taxonomy);
        }

        $roles = [
            'resident',
            'observer',
            'program_director'
        ];
        foreach ($roles as $role)
        {
            remove_role($role);
        }

        foreach (GFAPI::get_forms() as $form)
        {
            GFAPI::delete_form($form['id']);
        }

        delete_option('docc_as_error_log');
        update_option('docc_setup_status', '(1/3) Guided Setup');
    }

    public function wp_ajax_setup_support_email()
    {
        $DEBUG = get_option('docc_debug', []);

        $sender = isset($_POST['sender']) ? filter_var(trim($_POST['sender']), FILTER_SANITIZE_EMAIL) : '';
        $msg = isset($_POST['msg']) ? filter_var($_POST['msg'], FILTER_SANITIZE_STRING) : '';

        if (!$sender || !filter_var($sender, FILTER_VALIDATE_EMAIL) || !$msg) {
            $error_message = !$sender ? "Function called without sender defined." : "Invalid sender email.";
            $error_message = !$msg ? "Function called without message defined." : $error_message;
            $DEBUG['test-email']['setup-errors'][0] = false;
            $DEBUG['test-email']['setup-errors'][1][] = "WARNING: " . $error_message;
            update_option('docc_debug', $DEBUG);
            
            wp_die();
        }

        $to = 'support@design.garden';
        $subject = 'DOCC user setup error';

        $message = $this->Partial("admin/partials/mail/setup_support_email.php", compact("DEBUG"));

        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        if (wp_mail($to, $subject, $message, $headers)) {
            $DEBUG['test-email']['setup-errors'][0] = true;
            update_option('docc_setup_status', '(2/3) Test Support Email (Optional)');
        } else {
            $DEBUG['test-email']['setup-errors'][0] = false;
            $DEBUG['test-email']['setup-errors'][1][] = "ERROR: Server could not send email with debug info.";
            update_option('docc_setup_status', '(2/3) Automatic Setup');
        }

        update_option('docc_debug', $DEBUG);

        wp_die();
    }

    public function wp_ajax_test_email()
    {
        $DEBUG = get_option('docc_debug', []);

        $to = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $DEBUG['test-email']['email-validated'][0] = false;
            $DEBUG['test-email']['email-validated'][1][] = "WARNING: Invalid or undefined email.";
            update_option('docc_debug', $DEBUG);
            wp_die();
        }

        $current_email = wp_get_current_user()->user_email;
        $email_is_same_as_admin = ($current_email === $to);
        $subject = $email_is_same_as_admin ? "Setting up email for your new DOCC installation" : "Register a Program Director for your DOCC installation";
    
        $registration_link = get_site_url(null, 'register/?email=' . urlencode($to) . '&role=Program%20Director');
        $logout_link = wp_logout_url($registration_link);
        $message = $this->Partial("admin/partials/mail/test_email.php", compact("registration_link", "logout_link"));
    
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        if (wp_mail($to, $subject, $message, $headers)) {
            $DEBUG['test-email']['email-validated'][0] = true;
            update_option('docc_setup_status', '(3/3) Test Email');
        } else {
            $DEBUG['test-email']['email-validated'][0] = false;
            $DEBUG['test-email']['email-validated'][1][] = "ERROR: Server could not send test email to $to.";
            update_option('docc_setup_status', '(2/3) Automatic Setup');
        }

        update_option('docc_debug', $DEBUG);

        wp_die();
    }

    public function wp_ajax_setup_complete()
    {
        update_option('docc_setup_status', "(3/3) Setup Complete");

        wp_die();
    }

    /*public function get_setting()
    {
        var_dump(get_option('docc_setup_status'));
        wp_die();
    }*/

    public function activated_plugin()
    {
        $default_debug = [
            'guided-setup' => [
                'theme_installed' => [false, []],
                'theme_active' => [false, []],
                'plugins_installed' => [false, []],
                'plugins_active' => [false, []],
                'status' => 'Not Started'
            ],
            'auto-setup' => [],
            'test-email' => [
                'setup-errors' => [false, []],
                'email-validated' => [false, []]
            ]
        ];

        update_option('docc_debug', get_option('docc_debug', $default_debug));

        $setup_status = get_option('docc_setup_status');
        if ($setup_status !== "(3/3) Setup Complete") {
            $setup_status = '(0/3) Setup In Progress';
            update_option('docc_setup_status', $setup_status);
        }

        $redirect_page = $setup_status === "(3/3) Setup Complete" ? 'setup-complete' : $this->menu_slug;
        exit(wp_redirect(admin_url('admin.php?page=' . $redirect_page)));
    }

    /*public function wp_ajax_add_residents_test()
    {
        // $users = get_users( [ 'role' => 'resident' ] );
        // foreach ($users as $user) {
        //     $meta = get_user_meta($user->id);
        //     $first = $meta['first_name'][0];,
        //     $email = $meta['email'];
        //     var_dump($user);
        //     echo $last;
        //     echo strlen($last);
        //     if ($this->NameIsSet($first) && $this->NameIsSet($last)) {
        //         echo $first . " " . $last;
        //     } else {
        //         echo "no name";
        //     }
        //     echo '<br>';
        // }

        // wp_die();
    }*/

    public function admin_init()
    {
        if (is_admin() && !current_user_can('administrator') && !(defined('DOING_AJAX') && DOING_AJAX))
        {
            wp_redirect(home_url());
            exit;
        }

        $this->handle_program_edit();
    }

    private function handle_program_edit()
    {
        if (!isset($_GET['post'], $_GET['action']) || $_GET['action'] !== 'edit') return;

        $post_id = $_GET['post'];
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'program') return;

        $this->update_program_metadata($post_id, 'directors');
        $this->update_program_metadata($post_id, 'faculty');
        $this->update_program_metadata($post_id, 'residents');
    }

    private function update_program_metadata($post_id, $meta_key)
    {
        $meta_values = get_post_meta($post_id, $meta_key, true);

        if (!is_array($meta_values)) {
            return;
        }

        $updated_ids = array_map('strval', $meta_values);
        update_post_meta($post_id, "{$meta_key}_ids", $updated_ids);
    }

    public function extra_user_profile_fields($user)
    {
        $programs = Docc_Programs_GF::get_user_programs($user);
        $program_titles = [];
        foreach ($programs as $program)
        {
            $program_titles[] = $program->post_title;
        }
        echo $this->Partial("admin/partials/profile/extra_user_profile_fields.php", compact("user", "program_titles"));
    }

    public function get_password_reset_link()
    {
        if (!(isset($_GET) && isset($_GET['user_id']))) return;

        global $wpdb, $wp_hasher;

        $user = get_user_by('id', intval($_GET['user_id']));

        do_action('lostpassword_post');

        $user_login = $user->user_login;
        $user_email = $user->user_email;

        do_action('retrieve_password', $user_login);

        $allow = apply_filters('allow_password_reset', true, $user->ID);

        if ($allow && !is_wp_error($allow))
        {
            $key = wp_generate_password(20, false);
            do_action('retrieve_password_key', $user_login, $key);
            if (empty($wp_hasher))
            {
                require_once ABSPATH . 'wp-includes/class-phpass.php';
                $wp_hasher = new PasswordHash(8, true);
            }
            $hashed = time() . ':' . $wp_hasher->HashPassword($key);
            $wpdb->update($wpdb->users, array('user_activation_key' => $hashed), array('user_login' => $user_login));
            $message = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
        }

        echo $message;

        wp_die();
    }

    protected function GetPlugins(): array
    {
        $ret = [];
        foreach ($this->plugin_names as $slug => $name)
        {
            $ret[] = [
                'plugin' => $name,
                'slug' => $slug,
                'version' => $this->get_plugin_version($slug),
                'latest_version' => $this->get_plugin_latest_version($this->GetSlugFromBase($slug)),
                'required' => $this->is_plugin_required($slug),
                'installed' => $this->get_plugin_install_status($slug),
                'active' => $this->get_plugin_active_status($slug),
            ];
        }
        return $ret;
    }

    private function get_plugin_version(string $plugin_slug)
    {
        $fullpath = WP_PLUGIN_DIR . '/' . $plugin_slug;
        if (!file_exists($fullpath)) return '';
        $data = get_plugin_data($fullpath, false, false);
        return $data['Version'] ?: '';
    }

    private function get_plugin_latest_version(string $plugin_slug)
    {
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        $api = plugins_api('plugin_information', ['slug' => $plugin_slug, 'fields' => ['sections' => false]]);
        if (is_wp_error($api) || !property_exists($api, "version"))
        {
            return false;
        }
        return $api->version ?: '';
    }

    private function is_plugin_required($slug)
    {
        $plugins = [
            'gravityformswebhooks/webhooks.php' => false,
            'GFChart/gfchart.php' => false,
            'styles-and-layouts-for-gravity-forms/styles-layouts-gravity-forms.php' => false
        ];

        return !array_key_exists($slug, $plugins);
    }

    private function get_plugin_install_status(string $plugin_slug)
    {
        $installed_plugins = get_plugins();
        return (array_key_exists($plugin_slug, $installed_plugins) || in_array($plugin_slug, $installed_plugins, true)) ? true : false;
    }

    private function get_plugin_active_status(string $plugin_slug)
    {
        return is_plugin_active($plugin_slug) ?: false;
    }

    protected function GetThemes(): array
    {
        $ret = [];
        foreach ($this->theme_names as $name)
        {
            $ret[] = [
                'theme' => $name,
                'version' => '', // get_theme_version();
                'latest_version' => '', // get_latest_theme_version();
                'required' => true,
                'installed' => $this->get_theme_install_status($name),
                'active' => $this->get_theme_active_status($name),
            ];
        }
        return $ret;
    }
    
    private function get_theme_install_status(string $name)
    {
        $theme = wp_get_theme($name);
        return $theme->exists() ?: false;
    }

    private function get_theme_active_status(string $name)
    {
        return (wp_get_theme()->name === $name) ? true : false;
    }
}
