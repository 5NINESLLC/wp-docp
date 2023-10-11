<?php

/**
 * The abstract class for all Controllers.
 * 
 * @package 
 */
abstract class Docc_Controller extends Wordpress_Controller
{
    public static function IsSubscriber(): bool
    {
        return self::CurrentUserHasRole("subscriber");
    }
    public static function IsObserver(): bool
    {
        return self::CurrentUserHasRole("observer");
    }
    public static function IsResident(): bool
    {
        return self::CurrentUserHasRole("resident");
    }
    public static function IsProgramDirector(): bool
    {
        return self::CurrentUserHasRole("program_director");
    }
    public static function IsAdministrator(): bool
    {
        return self::CurrentUserHasRole("administrator");
    }

    protected function UserIsInProgram(int $program_id, int $user_id): bool
    {
        if ($this->UserIsInProgramAsRole($program_id, $user_id, 'directors')) return true;

        if ($this->UserIsInProgramAsRole($program_id, $user_id, 'faculty')) return true;

        if ($this->UserIsInProgramAsRole($program_id, $user_id, 'residents')) return true;

        return false;
    }

    protected function UserIsInProgramAsRole(int $program_id, int $user_id, string $role): bool
    {
        return in_array($user_id, $this->GetProgramMembers($program_id, $role));
    }

    protected function GetProgramMembers(int $program_id, string $role): array
    {
        $members = get_post_meta($program_id, $role, true);

        if (!is_array($members)) return [];

        return array_filter($members, 'get_userdata');
    }
}
