<?php

/**
 * The abstract class for all Controllers.
 * 
 * @package 
 */
abstract class Wordpress_Controller extends Controller
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    protected $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    protected $version;

    protected $plugin_url;

    /**
     * Initialize the class and set its properties.
     * 
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     * @param string $plugin_path 
     * @return void 
     */
    public function __construct(string $plugin_name, string $version, string $plugin_path, string $plugin_url, string $partials_path)
    {
        parent::__construct($partials_path);

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->plugin_path = $plugin_path;
        $this->plugin_url = $plugin_url;
    }

    protected function GetSlugOfReferringPage()
    {
        $referrer_url = parse_url(wp_get_referer(), PHP_URL_PATH);
        $referrer_slug_explode = explode('/', rtrim($referrer_url, '/'));
        return end($referrer_slug_explode);
    }

    protected function GetSlugOfUrl(string $url): string
    {
        $requested_url = rtrim($url, '/');

        $requested_url_explode = explode('/', $requested_url);

        return end($requested_url_explode);
    }
    
    protected function GetSlugFromBase(string $plugin_slug): string
    {
        return explode("/", $plugin_slug)[0];
    }

    protected function GetSlugOfRequestedPage(): string
    {
        global $wp;

        $slug = $this->GetSlugOfUrl(home_url($wp->request));
        if ($slug === $this->GetSlugOfUrl(home_url())) return "";

        return $slug;
    }

    protected static function GetUsersWithRole(string $role): array
    {
        $roles = array($role);

        return get_users(['role__in' => $roles]);
    }

    protected static function CurrentUserHasRole(string $role): bool
    {
        if (!is_user_logged_in()) return false;

        $current_user = wp_get_current_user();

        if (in_array($role, (array) $current_user->roles)) return true;

        return false;
    }

    protected function NameIsSet($name)
    {
        if ($name == "") return false;
        if (is_null($name)) return false;
        return true;
    }

    /**
     * The plugin filepath.
     *
     * @since     1.0.0
     * @return    string    The filepath of the plugin.
     */
    public function get_plugin_path()
    {
        return $this->plugin_path;
    }

    /**
     * The plugin url.
     *
     * @since     1.0.0
     * @return    string    The url of the plugin.
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

    public function GetGravityFormByTitle(string $title)
    {
        $existing = GFAPI::get_forms(true);

        foreach ($existing as $form)
            if ($form["title"] === $title) return $form;

        return null;
    }

    public static function GetFieldId($form, string $label)
    {
        foreach ($form['fields'] as $field) if ($label === $field['label']) return $field['id'];

        return null;
    }

    public static function GetFieldIdByStartOfLabel($form, string $label)
    {
        foreach ($form['fields'] as $field) if (strpos($field['label'], $label) === 0) return $field['id'];

        return null;
    }
}
