<?php

/**
 * Created by PhpStorm.
 * User: Julian Scheuchenzuber <js@lvl51.de>
 * Date: 22.10.15
 * Time: 13:52
 */
class MoreAdminsRequestFilter implements RequestFilter
{

    public function preRequest(SS_HTTPRequest $request, Session $session, DataModel $model)
    {
        Cookie::set('more_admins_entire_site_protected', Config::inst()->get('BasicAuth', 'entire_site_protected'));
        Config::inst()->update('BasicAuth', 'entire_site_protected', false);

        return true;
    }

    public function postRequest(SS_HTTPRequest $request, SS_HTTPResponse $response, DataModel $model)
    {
        return true;
    }
}
