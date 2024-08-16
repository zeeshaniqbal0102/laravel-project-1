<?php namespace App\Helpers;

use Auth;
use App\User;
use App\DB\Resource;
use App\DB\Role;

class AclHelper
{
    private static $roles;

    /*
     * Return Roles associated to user
     *
     * @param obj User Model Instance
     * @return obj
     */
    private static function getRolesByUser(User $user)
    {
        return $user->roles;
    }

    /*
     * Return Roles associated to resource
     *
     * @param obj Resource Model Instance
     * @return obj
     */
    private static function getRolesByResource(Resource $resource)
    {
        return $resource->roles;
    }

    /**
     * Match the url pattern to resources associated
     * with current logged in user
     *
     * @param string Url pattern
     * @return bullion true|false
     */
    public static function isUrlPatternMatching($pattern)
    {
        if (!self::$roles)
            self::$roles = Self::getRolesByUser(User::find(Auth::user()->id));

        if (count(self::$roles) == 0)
            return false;

        foreach (self::$roles as $role) {
            foreach ($role->resources as $resource) {
                if (self::isPatternMatching($pattern, $resource->pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function isPatternMatching($menu_pattern, $resource_pattern)
    {
        $tmp_m = explode('/', $menu_pattern);
        $tmp_r = explode('/', $resource_pattern);
        $r_pattern = [];

        $r_p = '';
        for ($i = 0; $i < count($tmp_m); $i++) {
            if (isset($tmp_r[$i]))
                $r_pattern[] = $tmp_r[$i];
        }

        $r_p = implode('/', $r_pattern);

        if ($menu_pattern == $r_p)
            return true;
        return false;
    }


    /**
     * Match the route string passed with associated
     * routes for current logged in user
     *
     * @param $route
     * @return bool
     */
    public static function isRouteAccessable($route)
    {
        if (!self::$roles)
            self::$roles = Self::getRolesByUser(User::find(Auth::user()->id));

        if (count(self::$roles) == 0)
            return false;

        foreach (self::$roles as $role) {
            foreach ($role->resources as $resource) {
                if ($route == $resource->route) {
                    return true;
                }
            }
        }

        return false;
    }



}