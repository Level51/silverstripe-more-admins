<?php

/**
 * Created by PhpStorm.
 * User: Julian Scheuchenzuber <js@lvl51.de>
 * Date: 22.10.15
 * Time: 13:28
 */
class MoreAdminsBasicAuth extends BasicAuth
{
    private static $default_realm = 'Test space';

    /**
     * Require basic authentication.  Will request a username and password if none is given.
     *
     * Used by {@link Controller::init()}.
     *
     * @throws SS_HTTPResponse_Exception
     *
     * @param string $realm
     * @param string|array $permissionCode Optional
     * @param boolean $tryUsingSessionLogin If true, then the method with authenticate against the
     *  session log-in if those credentials are disabled.
     * @return Member $member
     */
    public static function requireLogin($realm, $permissionCode = null, $tryUsingSessionLogin = true)
    {
        $isRunningTests = (class_exists('SapphireTest', false) && SapphireTest::is_running_test());
        if (!Security::database_is_ready() || (Director::is_cli() && !$isRunningTests)) {
            return true;
        }

        /*
         * Enable HTTP Basic authentication workaround for PHP running in CGI mode with Apache
         * Depending on server configuration the auth header may be in HTTP_AUTHORIZATION or
         * REDIRECT_HTTP_AUTHORIZATION
         *
         * The follow rewrite rule must be in the sites .htaccess file to enable this workaround
         * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
         */
        $authHeader = (isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] :
            (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : null));
        $matches = array();
        if ($authHeader &&
            preg_match('/Basic\s+(.*)$/i', $authHeader, $matches)) {
            list($name, $password) = explode(':', base64_decode($matches[1]));
            $_SERVER['PHP_AUTH_USER'] = strip_tags($name);
            $_SERVER['PHP_AUTH_PW'] = strip_tags($password);
        }

        $member = null;
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            $member = MoreAdminsAuthenticator::authenticate(array(
                'Email' => $_SERVER['PHP_AUTH_USER'],
                'Password' => $_SERVER['PHP_AUTH_PW'],
            ), null);
        }

        if (!$member && $tryUsingSessionLogin) {
            $member = Member::currentUser();
        }

        // If we've failed the authentication mechanism, then show the login form
        if (!$member) {
            $response = new SS_HTTPResponse(null, 401);
            $response->addHeader('WWW-Authenticate', "Basic realm=\"$realm\"");

            if (isset($_SERVER['PHP_AUTH_USER'])) {
                $response->setBody(_t('BasicAuth.ERRORNOTREC', "That username / password isn't recognised"));
            } else {
                $response->setBody(_t('BasicAuth.ENTERINFO', "Please enter a username and password."));
            }

            // Exception is caught by RequestHandler->handleRequest() and will halt further execution
            $e = new SS_HTTPResponse_Exception(null, 401);
            $e->setResponse($response);
            throw $e;
        }

        if ($permissionCode && !Permission::checkMember($member->ID, $permissionCode)) {
            $response = new SS_HTTPResponse(null, 401);
            $response->addHeader('WWW-Authenticate', "Basic realm=\"$realm\"");

            if (isset($_SERVER['PHP_AUTH_USER'])) {
                $response->setBody(_t('BasicAuth.ERRORNOTADMIN', "That user is not an administrator."));
            }

            // Exception is caught by RequestHandler->handleRequest() and will halt further execution
            $e = new SS_HTTPResponse_Exception(null, 401);
            $e->setResponse($response);
            throw $e;
        }

        return $member;
    }
}
