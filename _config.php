<?php
/**
 * User: Julian Scheuchenzuber <js@lvl51.de>
 * Date: 22.10.15
 * Time: 12:12
 */
if(isset($GLOBALS['_DEFAULT_ADMINS'])) {
    // Reset pointer and fetch data of first record
    reset($GLOBALS['_DEFAULT_ADMINS']);
    $email = key($GLOBALS['_DEFAULT_ADMINS']);
    $pw = $GLOBALS['_DEFAULT_ADMINS'][$email];

    // Set default admin if not exists
    if(!Security::has_default_admin())
        Security::setDefaultAdmin($email, $pw);
}