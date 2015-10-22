<?php

/**
 * User: Julian Scheuchenzuber <js@lvl51.de>
 * Date: 22.10.15
 * Time: 11:25
 */
class MoreAdminsAuthenticator extends MemberAuthenticator {

    /**
     * Attempt to find and authenticate member if possible from the given data.
     *
     * @param array $data
     * @param Form $form
     * @param bool &$success Success flag
     * @return Member Found member, regardless of successful login
     * @see MemberAuthenticator::authenticate_member()
     */
    protected static function authenticate_member($data, $form, &$success) {
        // Default success to false
        $success = false;

        // Attempt to identify by temporary ID
        $member = null;
        $email = null;
        if(!empty($data['tempid'])) {
            // Find user by tempid, in case they are re-validating an existing session
            $member = Member::member_from_tempid($data['tempid']);
            if($member) $email = $member->Email;
        }

        // Otherwise, get email from posted value instead
        if(!$member && !empty($data['Email'])) {
            $email = $data['Email'];
        }

        // Check default login (see Security::setDefaultAdmin()) the standard way and the "extension"-way :-)
        $asDefaultAdmin = $email === Security::default_admin_username();
        if($asDefaultAdmin || (isset($GLOBALS['_DEFAULT_ADMINS']) && array_key_exists($email, $GLOBALS['_DEFAULT_ADMINS']))) {
            // If logging is as default admin, ensure record is setup correctly
            $member = Member::default_admin();
            $success = Security::check_default_admin($email, $data['Password']);

            // If not already true check if one of the extra admins match
            if(!$success)
                $success = $GLOBALS['_DEFAULT_ADMINS'][$email] == $data['Password'];

            if($success) return $member;
        }

        // Attempt to identify user by email
        if(!$member && $email) {
            // Find user by email
            $member = Member::get()
                ->filter(Member::config()->unique_identifier_field, $email)
                ->first();
        }

        // Validate against member if possible
        if($member && !$asDefaultAdmin) {
            $result = $member->checkPassword($data['Password']);
            $success = $result->valid();
        } else {
            $result = new ValidationResult(false, _t('Member.ERRORWRONGCRED'));
        }

        // Emit failure to member and form (if available)
        if(!$success) {
            if($member) $member->registerFailedLogin();
            if($form) $form->sessionMessage($result->message(), 'bad');
        } else {
            if($member) $member->registerSuccessfulLogin();
        }

        return $member;
    }
}