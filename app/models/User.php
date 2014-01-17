<?php

use Cartalyst\Sentry\Users\Eloquent\User as SentryUser;

class User extends SentryUser {

    /**
     * See if a user has access to the passed permission(s).
     * Permissions are merged from all groups the user belongs to
     * and then are checked against the passed permission(s).
     *
     * If multiple permissions are passed, the user must
     * have access to all permissions passed through, unless the
     * "all" flag is set to false.
     *
     * Super users have access no matter what.
     *
     * @param  string|array  $permissions   String of a single permission or array of multiple permissions
     * @param  bool          $level         Desired level
     * @param  bool          $all           Do all permission need to hit the level?
     * @return bool
     */
    public function hasAccess($permissions, $level = 1, $all = true)
    {
        if ($this->isSuperUser())
        {
            return true;
        }

        return $this->hasPermission($permissions, $level, $all);
    }

    /**
     * See if a user has access to the passed permission(s).
     * This overwrites Sentry's lowlevel permission system
     * and adds the level attribute.
     *
     * @param  string|array  $permissions   String of a single permission or array of multiple permissions
     * @param  bool          $level         Desired level
     * @param  bool          $all           Do all permission need to hit the level?
     * @return bool
     */
    public function hasPermission($permissions, $level = 1, $all = true)
    {
        $mergedPermissions = $this->getMergedPermissions(); // Permission the user has

        if ( ! is_array($permissions)) {
            $permissions = (array) $permissions; // Ensure $permissions is an array
        }

        foreach ($permissions as $permission) {
            $matched = false;

            foreach ($mergedPermissions as $mergedPermission => $value) {
                if ($permission == $mergedPermission) {
                    if ($mergedPermissions[$permission] >= $level) { // Compare with desired level
                        if (! $all) {
                            return true;
                        } else {
                            $matched = true;
                        }
                    }
                    break;
                }
            }

            if ($all and ! $matched) {
                return false; // Return false if $all = true and a permission is not given
            }
        }

        if ($all) {
            return true;
        } else {
            return false;
        }
    }

}