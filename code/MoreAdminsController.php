<?php

/**
 * Created by PhpStorm.
 * User: Julian Scheuchenzuber <js@lvl51.de>
 * Date: 22.10.15
 * Time: 13:40
 */
class MoreAdminsController extends Extension
{
    public function onAfterInit()
    {
        // Check if basic auth is needed and handle cookie
        $useBasicAuth = Cookie::get('more_admins_entire_site_protected');
        if (isset($_COOKIE['more_admins_entire_site_protected'])) {
            unset($_COOKIE['more_admins_entire_site_protected']);
        }

        if ($useBasicAuth) {
            MoreAdminsBasicAuth::requireLogin(Config::inst()->get('MoreAdminsBasicAuth', 'default_realm'));
        }
    }
}
