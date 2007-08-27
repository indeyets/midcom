<?php
// Render a default page if we got a submission without an openid
// value.
if (empty($_POST['openid_url'])) 
{
    // TODO: Populate error message to form instead of UImessage
    $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('net.nemein.openid', 'net.nemein.openid'), $_MIDCOM->i18n->get_string('please provide an openid url', 'net.nemein.openid'), 'warning');
    $_MIDCOM->auth->show_login_page();
    // This will exit
}

// Load the OpenID consumer library
$consumer = new net_nemein_openid_consumer();

$openid = $_POST['openid_url'];

$process_url = $_MIDCOM->get_host_prefix() . 'midcom-exec-net.nemein.openid/process.php';
$trust_root = $_MIDCOM->get_host_prefix();

// Begin the OpenID authentication process
$auth_request = $consumer->begin($openid);

// Handle failure status return values.
if (!$auth_request) 
{
    // TODO: Populate error message to form instead of UImessage
    $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('net.nemein.openid', 'net.nemein.openid'), sprintf($_MIDCOM->i18n->get_string('could not discover openid provider for identity %s', 'net.nemein.openid'), $openid), 'error');
    $_MIDCOM->auth->show_login_page();
    // This will exit
}

/*
// TODO: Implement
$consumer->populate_sreg();

$auth_request->addExtensionArg('sreg', 'optional', 'email');
*/

// Redirect the user to the OpenID server for authentication.
$redirect_url = $auth_request->redirectURL($trust_root, $process_url);

$_MIDCOM->relocate($redirect_url);
?>