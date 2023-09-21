<?php

/**
 * The shortcode functionality of the plugin.
 *
 * @link       https://design.garden
 * @since      1.0.0
 *
 * @package    Docc
 * @subpackage Docc/shortcodes
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Docc
 * @subpackage Docc/shortcodes
 * @author     Anthony Jacobs <tony@design.garden>
 */
class Docc_Shortcodes extends Docc_Controller
{

    const MESSAGE_PERMISSION_ERROR = "You lack permission to view this content";

    private $OBSERVATION_FORM_TITLE = "New Observation";

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

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/docc-shortcodes.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        /*
         * Bootstrap: Latest compiled and minified JavaScript
         */
        wp_enqueue_script($this->plugin_name . "-bootstrap", 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js', array('jquery'), $this->version, false);

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/docc-shortcodes.js', array('jquery'), $this->version, false);
    }

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
                /*[
                    'key' => 'partial_entry_percent',
                    'operator' => $saved ? '!=' : '=',
                    'value' => false
                ],*/
            ]
        ];

        return GFAPI::get_entries($form_id, $search_criteria); //, $sorting = null, $paging = null, $total_count = null );
    }

    protected function GetField($entry)
    { // TODO: Used in "saved_observations.php" partial...
        $ret = '<ul>';
        for ($i = 1; $i < 5; $i++) {
            if ($entry["5.$i"]) {
                $ret .= '<li>' . $entry["5.$i"] . '</li>';
            }
        }
        $ret .= '</ul>';

        return $ret;
    }


    public function HtmlTableRow(...$args) //TODO: Used in partial file...
    {
        $args = func_get_args();

        $ret = '<tr>';

        foreach ($args as $column)
            $ret .= '<td>' . $column . '</td>';

        $ret .= '</tr>';

        return $ret;
    }

    public function HtmlAnchor(string $text, string $link): string //TODO: Used in partial file...
    {
        return "<a href='$link'>$text</a>";
    }

    public function gravityform_by_title($atts, $content = null)
    {
        $query = '';
        $title = "false";
        $ajax = "false";
        $description = 'false';
        $tabindex = 0;
        extract(shortcode_atts(array('query' => $query, 'title' => $title, 'ajax' => $ajax, 'description' => $description, 'tabindex' => $tabindex), $atts));

        $form = $this->GetGravityFormByTitle($query);

        if ($form === null) return "We're having trouble loading this content, please check again later.";

        return do_shortcode("[gravityform id='" . $form['id'] . "' title='$title' description='$description' ajax='$ajax' tabindex='$tabindex']");
    }

    public function saved_observations($atts, $content = null)
    {
        if (!(self::IsObserver() || self::IsProgramDirector() || self::IsAdministrator())) return self::MESSAGE_PERMISSION_ERROR;

        $form = $this->GetGravityFormByTitle($this->OBSERVATION_FORM_TITLE);

        if ($form == null) return "None found.";

        $entries = $this->GetUserEntries($form['id'], get_current_user_id(), true);

        return $this->Partial("tables/saved_observations.php", compact("entries"));
    }

    public function logout_link()
    {
        return wp_logout_url(home_url());
    }
}