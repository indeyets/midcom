<?php
session_start();
$consumer = new net_nemein_openid_consumer();

// Complete the authentication process using the server's response.
$response = $consumer->complete($_GET);

// Deal with status
switch ($response->status)
{
    case Auth_OpenID_FAILURE:
        // TODO: Populate error message to form instead of UImessage
        $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('net.nemein.openid', 'net.nemein.openid'), sprintf($_MIDCOM->i18n->get_string('openid authentication failed: %s', 'net.nemein.openid'), $response->message), 'error');
        // Fall-through
    case Auth_OpenID_CANCEL:
        $_MIDCOM->auth->show_login_page();
        // This will exit        
        break;
    case Auth_OpenID_SUCCESS:
        // This means the authentication succeeded.
        $openid = $response->identity_url;
        $esc_identity = htmlspecialchars($openid, ENT_QUOTES);
        
        $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('net.nemein.openid', 'net.nemein.openid'), sprintf($_MIDCOM->i18n->get_string('successfully authenticated as %s via openid', 'net.nemein.openid'), $esc_identity), 'ok');

        // Handle MidCOM login session creation and/or user registration
        if (!$consumer->authenticate())
        {
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('net.nemein.openid', 'net.nemein.openid'), sprintf($_MIDCOM->i18n->get_string('failed to authenticate to midgard as %s via openid: %s', 'net.nemein.openid'), $esc_identity, mgd_errstr()), 'error');
            $_MIDCOM->auth->show_login_page();
        }
        /*
        // TODO: Deal with registration information
        $sreg = $response->extensionResponse('sreg');

        if (@$sreg['email']) {
            $success .= "  You also returned '".$sreg['email']."' as your email.";
        }
        if (@$sreg['postcode']) {
            $success .= "  Your postal code is '".$sreg['postcode']."'";
        }
        */
        
        // TODO: Deal with specific return URLs
        $_MIDCOM->relocate('');
        // This will exit
}

// Fallback for weird situations
$_MIDCOM->auth->show_login_page();
// This will exit
?>