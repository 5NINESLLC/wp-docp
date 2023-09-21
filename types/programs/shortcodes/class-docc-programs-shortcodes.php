<?

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
 * Shortcode implementation for Programs
 *
 *
 * @package    Docc
 * @subpackage Docc/programs
 * @author     Anthony Jacobs <tony@design.garden>
 */
class Docc_Programs_Shortcodes extends Docc_Controller
{

    /**
     * Display Gravity Form by title
     */
    public function display_gf($atts)
    {
        if (!is_array($atts) || !array_key_exists("title", $atts) || !is_string($atts['title'])) return "This form is currently unavailable. Please forward this issue to your technical support contact.";

        if (!self::user_can_view_form($atts['title'])) return "You must be an administrator or program director to manage this content";

        return gravity_form(
            $atts['title'],
            $display_title = true,
            $display_description = true,
            $display_inactive = false,
            $field_values = null,
            $ajax = false,
            $tabindex = 0,
            $echo = false
        );
    }

    public static function user_can_view_form(string $title)
    {
        if ($title === 'User Registration') return true;

        if (self::IsAdministrator() || self::IsProgramDirector()) return true;

        if ($title === "New Observation" && self::IsObserver()) return true;

        return false;
    }

    /**
     * Displays programs that the current user is a Program Director for
     */
    public function display_director_programs()
    {

        $programs = $this->GetProgramsOfDirector(wp_get_current_user());

        return $this->Partial("display_director_programs.php", compact("programs"));
    }

    /**
     * Displays broadcast messages that the current user is a Resident of
     */
    public function display_broadcast_message()
    {
        $user = wp_get_current_user();
        $programs = get_posts([
            'post_type'   => 'program',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ]);

        return $this->Partial("display_broadcast_message.php", compact("user", "programs"));
    }

    /**
     * Displays all of current user's programs on the dashboard
     */
    public function display_programs_on_dashboard()
    {
        $user_id = get_current_user_id();

        if ($user_id === 0) return "";

        $program_ids = get_posts([
            'post_type'   => 'program',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        $tableData = [];
        foreach ($program_ids as $program_id) {
            if ($this->UserIsInProgram($program_id, $user_id)){
                $tableData[] = [
                    $program_id,
                    get_post_meta($program_id, 'broadcast_message', true),
                    get_post_meta($program_id, 'support_contact', true),
                ];
            }
        }

        return $this->Partial("display_programs_on_dashboard.php", compact("tableData"));
    }

    private function GetProgramsOfDirector($user): array
    {
        $programs = get_posts([
            'post_type'   => 'program',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ]);

        if (!is_array($programs)) return [];

        return array_filter($programs, function($v) use (&$user) { 
            return $this->UserIsInProgramAsRole($v->ID, $user->ID, 'directors');
        });
    }

    public function display_program_members()
    {
        if (!(self::IsAdministrator() || self::IsProgramDirector())) return "";

        $program = get_post();

        if ($program === null) return "";

        if ($program->post_title === "DEMO Program") return "";

        $user_IDs =  array_merge(
            $this->GetProgramMembers($program->ID, 'directors'),
            $this->GetProgramMembers($program->ID, 'faculty'),
            $this->GetProgramMembers($program->ID, 'residents')
        );

        return $this->Partial("display_program_members.php", compact("user_IDs"));
    }
}