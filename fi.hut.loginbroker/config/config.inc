/**
 * Browsers may refuse cross-host cookies even in the same domain,
 * cross-domain cookies will almost certainly be refused. Set this
 * setting to true value to copy the cookie info as GET parameter to the
 * redirect URL.
 */
'allow_get_sessionid' => false, 
'allow_create_user' => false, /* If username is not found in DB allow to create him/her */
'create_user_callbacks' => array /* callback class to use when creating new user*/
(
    'fi_hut_loginbroker_callbacks_createperson',
    'fi_hut_loginbroker_callbacks_affiliations',
),
'fi_hut_loginbroker_callbacks_createperson_config' => array /* callback configurations should be namespaced like this */
(
    'add_to_groups' => array
    (
        // Guids here
    ),
),
'allow_reset_password' => false, /* If user is found but does not have plaintext password, allow reset to random one */
'reset_password_callbacks' => array /* you may have multiple but I don't quite see why you would want to */
(
    'fi_hut_loginbroker_callbacks_resetpasswd',
),
'allow_update_user' => false, /* enable update callbacks */
'update_user_callbacks' => array /* before redirecting call these classes to update user info from the remote login/identity provider */
(
    'fi_hut_loginbroker_callbacks_updateperson',
    'fi_hut_loginbroker_callbacks_affiliations',
),
'fi_hut_loginbroker_callbacks_affiliations_config' => array
(
    'enable' => false, /* quick way to enable/disable the plugin without messing with other configs */
    'header' => 'HTTP_SHIB_EPA', /* <AttributeRule Name="urn:mace:dir:attribute-def:eduPersonAffiliation" Header="SHIB_EPA" Alias="affiliation"> */
    'delimiter' => ';', /* how the header delimits the affiliation values */
    'map' => array /* keys are affiliations values local group guids */
    (
    ),
),
'username_header' => 'REMOTE_USER', /* which $_SERVER key has the username */
'property_map' => array /* Map $_SERVER keys to keys of the property array passed to the callbacks */
(
    'username'  => 'REMOTE_USER',
    'firstname' => 'HTTP_SHIB_DN',
    'lastname'  => 'HTTP_SHIB_SN',
    'email'     => 'HTTP_SHIB_MAIL',
),
'store_headers' => array /* Store $_SERVER keys as parameters to the session object */
(
    'HTTP_SHIB_LOGOUT_URL',
),
