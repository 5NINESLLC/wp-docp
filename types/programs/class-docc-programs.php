<?php

/**
 * Programs custom post type
 *
 * @link       https://design.garden
 * @since      1.0.0
 *
 * @package    Docc
 * @subpackage Docc/programs
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Docc
 * @subpackage Docc/programs
 * @author     Anthony Jacobs <tony@design.garden>
 */
class Docc_Programs extends Docc_Controller
{

    private $type_slug = 'program';

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name . "-programs", plugin_dir_url(__FILE__) . 'css/docc-programs.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name . "-programs", plugin_dir_url(__FILE__) . 'js/docc-programs.js', array('jquery'), $this->version, false);
    }

    /**
     * Admin setup for the Program post-type
     */

    /**
     * Register Program post-type with Wordpress
     */
    public function register_post_type()
    {
        $labels = [
            'name'                => 'Programs',
            'singular_name'       => 'Program',
            'menu_name'           => 'Programs',
            'parent_item_colon'   => 'Parent Programs:',
            'all_items'           => 'All Programs',
            'view_item'           => 'View Program',
            'add_new_item'        => 'Add New Program',
            'add_new'             => 'Add Program',
            'edit_item'           => 'Edit Program',
            'update_item'         => 'Update Program',
            'search_items'        => 'Search Programs',
            'not_found'           => 'No Programs found',
            'not_found_in_trash'  => 'No Programs found in Trash',
        ];
        $args = [
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            // 'rewrite' => array( 'slug' => 'programs' ),
        ];
        register_post_type('program', $args);
    }
    
    /**
     * Setup meta boxes for custom meta fields
     */
    public function add_meta_boxes()
    {
        add_meta_box('program_meta', 'Program Metadata', [$this, 'side_meta_box_html'], $this->type_slug, 'side', 'core');
        add_meta_box( 'program_directors', 'Program Directors', [$this, 'directors_meta_box_html'], $this->type_slug, 'advanced', 'core' );
        add_meta_box( 'program_faculty', 'Faculty', [$this, 'faculty_meta_box_html'], $this->type_slug, 'advanced', 'core' );
        add_meta_box( 'program_residents', 'Residents', [$this, 'residents_meta_box_html'], $this->type_slug, 'advanced', 'core' );
    }

    public function side_meta_box_html($post)
    {
        $support_contact = get_post_meta($post->ID, 'support_contact', true);
        echo 'Support Contact'; ?><br />
        <input type="email" name="support_contact" value="<?php echo esc_attr($support_contact); ?>" /><br /><br />
        <?php

        $invitation_message = get_post_meta($post->ID, 'invitation_message', true);
        echo 'Invitation Message'; ?><br />
        <textarea name="invitation_message"><?php echo esc_attr($invitation_message); ?></textarea><br /><br />
        <?php

        $broadcast_message = get_post_meta($post->ID, 'broadcast_message', true);
        echo 'Broadcast Message'; ?><br />
        <textarea name="broadcast_message"><?php echo esc_attr($broadcast_message); ?></textarea>

        <?php
                $director_ids = get_post_meta($post->ID, 'directors', true);
                $faculty_ids = get_post_meta($post->ID, 'faculty', true);
                $resident_ids = get_post_meta($post->ID, 'residents', true);
                $directors = [];
                $faculty = [];
                $residents = [];
                $users = [];
                foreach ($director_ids as $id)
                {
                    $user = get_user_by('id', $id);
                    $directors[$id] = [
                        'name' => $user->display_name,
                        'email' => $user->user_email,
                        'roles' => $user->roles
                    ];
                }
                foreach ($faculty_ids as $id)
                {
                    $user = get_user_by('id', $id);
                    $faculty[$id] = [
                        'name' => $user->display_name,
                        'email' => $user->user_email,
                        'roles' => $user->roles
                    ];
                }
                foreach ($resident_ids as $id)
                {
                    $user = get_user_by('id', $id);
                    $residents[$id] = [
                        'name' => $user->display_name,
                        'email' => $user->user_email,
                        'roles' => $user->roles
                    ];
                }
                $users["directors"] = $directors;
                $users["faculty"] = $faculty;
                $users["residents"] = $residents;
                $json = json_encode($users);

                $filename = get_the_title();
        ?>

        <?php
        echo "<script>var filename = '$filename';</script>";
        echo "<script>var users = $json;</script>";
        ?>
<?php
    }
    public function directors_meta_box_html($post) {
        $program_director_ids = get_post_meta($post->ID, 'director_ids')[0]; ?>
        <table>
            <tr>
                <th>User ID</th>
            </tr>
        <?php foreach($program_director_ids as $id) { ?>
            <tr>
                <td>
                    <input type="text" name="director_ids[]" value="<?php echo $id; ?>" />
                </td>
            </tr>
        <?php } ?>
            <tr>
                <td>
                    <input type="text" name="director_ids[]" value="" />
                </td>
            </tr>
        </table>
        <?php
    }
     
    
    public function residents_meta_box_html($post) {
        $program_resident_ids = get_post_meta($post->ID, 'resident_ids')[0]; ?>
        <table>
            <tr>
                <th>User ID</th>
            </tr>
        <?php foreach($program_resident_ids as $id) { ?>
            <tr>
                <td>
                    <input type="text" name="resident_ids[]" value="<?php echo $id; ?>" />
                </td>
            </tr>
        <?php } ?>
            <tr>
                <td>
                    <input type="text" name="resident_ids[]" value="" />
                </td>
            </tr>
        </table>
        <?php
    }
    
    
    public function faculty_meta_box_html($post) {
        $program_faculty_ids = get_post_meta($post->ID, 'faculty_ids')[0]; ?>
        <table>
            <tr>
                <th>User ID</th>
            </tr>
        <?php foreach($program_faculty_ids as $id) { ?>
            <tr>
                <td>
                    <input type="text" name="faculty_ids[]" value="<?php echo $id; ?>" />
                </td>
            </tr>
        <?php } ?>
            <tr>
                <td>
                   <input type="text" name="faculty_ids[]" value="" />
                </td>
            </tr>
        </table>
        <?php
    }
    

    /**
     * Handles saving custom meta fields
     */
    public function save_meta($post_id)
    {
        global $post;
        $user = wp_get_current_user();

        if (!is_object($post)) return;
        if (!isset($_POST)) return;
        if ($post->post_type !== "program") return;
        if (!in_array('administrator', $user->roles) && !$this->user_is_director_for_program($post_id, $user->ID, in_array('program_director', $user->roles))) return;

        update_post_meta($post_id, 'support_contact', strip_tags($_POST['support_contact']));
        update_post_meta($post_id, 'invitation_message', strip_tags($_POST['invitation_message']));
        update_post_meta($post_id, 'broadcast_message', strip_tags($_POST['broadcast_message']));
        update_post_meta( $post_id, 'director_ids', array_filter($_POST['director_ids']) );
        update_post_meta( $post_id, 'resident_ids', array_filter($_POST['resident_ids']) );
        update_post_meta( $post_id, 'faculty_ids', array_filter($_POST['faculty_ids']) );

        self::add_users_to_progam_after_save($post_id);
    }

    /**
     * Admin setup helper functions
     */
    public function user_is_director_for_program($program_id, $user_id, $is_director)
    {
        if (!$is_director) return false;
        $directors = get_post_meta($program_id, 'directors');
        if (in_array($user_id, $directors)) return true;
        return false;
    }

    public function manage_program_posts_columns($columns)
    {
        $columns['Support Contact']  = __('Support Contact', $this->type_slug);
        $columns['Invitation Message'] = __('Invitation Message', $this->type_slug);
        $columns['Broadcast Message'] = __('Broadcast Message', $this->type_slug);
        return $columns;
    }

    public function manage_program_posts_custom_column($column, $post_id)
    {
        switch ($column) {

            case 'Support Contact':
                echo get_post_meta($post_id, 'support_contact', true);
                break;
            case 'Invitation Message':
                echo get_post_meta($post_id, 'invitation_message', true);
                break;
            case 'Broadcast Message':
                echo get_post_meta($post_id, 'broadcast_message', true);
                break;
        }
    }

    public function wp_ajax_switch_demo_mode()
    {
        $user = wp_get_current_user();
        $meta = get_user_meta($user->ID);
        if (!isset($meta['demo_user'])) return;
        if (count($meta['demo_user']) < 1) return;
        if ($meta['demo_user'][0] !== "DEMO") return;

        $demo_mode = $_POST['switch'] === "off" ? "FULL" : "DEMO";
        update_user_meta($user->ID, 'demo_user', "FULL");

        self::remove_user_from_program($user->ID, "DEMO Program");
    }

    public function wp_ajax_get_program_residents()
    {
        $program = Docc_Programs::get_program_by_name($_GET['name']);
        $residents = get_post_meta($program->ID, 'residents', true);
        if (!is_array($residents)) $residents = [];
        $emails = [];
        foreach ($residents as $ID) {
            $user = get_user_by('ID', $ID);
            $emails[] = $user->user_email;
        }
        echo json_encode($emails);
        die();
    }

    public function user_demo()
    {
        // $user = wp_get_current_user();
        // $this->remove_user_from_program($user->ID, "DEMO Program");
        // $program = get_posts([
        //     'name'  => 'DEMO Program',
        //     'post_type'   => 'program',
        //     'post_status' => 'publish',
        //     'posts_per_page' => -1,
        // ])[0];
        // $meta = get_post_meta($program->ID, 'directors', true);
        // foreach ($meta as $user_id) {
        //     $user = get_user_by('ID', $user_id);
        //     var_dump($user->user_login);
        // }
        // exit;



        $user = get_user_by('login', 'demo-test');
        $meta = get_user_meta($user->ID, 'demo_user', true);
        if ($meta == "DEMO") return;
        // update_user_meta($user->ID, 'demo_user', "DEMO", "FULL");
    }

    public static function add_users_to_progam_after_save($post_id)
    {
        $director_ids = get_post_meta($post_id, 'director_ids', true);
        $faculty_ids = get_post_meta($post_id, 'faculty_ids', true);
        $resident_ids = get_post_meta($post_id, 'resident_ids', true);

        $director_ids_as_int = [];
        $faculty_ids_as_int = [];
        $resident_ids_as_int = [];

        foreach($director_ids as $id)
        {
            $director_ids_as_int[] = intval($id);
        }

        foreach($faculty_ids as $id)
        {
            $faculty_ids_as_int[] = intval($id);
        }

        foreach($resident_ids as $id)
        {
            $resident_ids_as_int[] = intval($id);
        }

        update_post_meta($post_id, 'directors', $director_ids_as_int);
        update_post_meta($post_id, 'faculty', $faculty_ids_as_int);
        update_post_meta($post_id, 'residents', $resident_ids_as_int);
    }

    public static function remove_user_from_program($user_id, $program_name)
    {
        $program = self::get_program_by_name($program_name);
        self::remove_user($program, 'directors', $user_id);
        self::remove_user($program, 'faculty', $user_id);
        self::remove_user($program, 'residents', $user_id);
    }
    
    public static function remove_user($program, $role, $user_id)
    {
        $users = get_post_meta($program->ID, $role, true);
        $ID = array_search($user_id, $users);
        if (false !== $ID) {
            unset($users[$ID]);
            update_post_meta($program->ID, $role, array_filter($users));
        }
    }

    public static function get_program_by_name($name)
    {
        $program = get_posts([
            'title'  => $name,
            'post_type'   => 'program',
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ]);

        if (empty($program)) return null;

        return $program[0];
    }

    public function ajaxurl()
    {
        echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
    }

    public function demo_mode()
    {
        $demo_mode = $this->demo_mode_active();
        echo '<script type="text/javascript">
            var demo_mode = "' . $demo_mode . '";
        </script>';
    }

    private function demo_mode_active()
    {
        $OFF = 'FULL';
        $ON = 'DEMO';
        $user = wp_get_current_user();
        $meta = get_user_meta($user->ID);
        if (!isset($meta['demo_user'])) return $OFF;
        if (count($meta['demo_user']) < 1) return $OFF;
        if (strcasecmp($meta['demo_user'][0], "DEMO") === 0) return $ON;
        return $OFF;
    }
}