<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$host = mgd_get_host($_MIDGARD['host']);
$user = mgd_get_person($_MIDGARD['user']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="" lang="">
<html>
    <head>
        <title><?php echo $view_data['l10n']->get("instant messaging"); ?></title>
    </head>
    <body>
        <applet archive="<?php echo MIDCOM_STATIC_URL; ?>/org.openpsa.jabber/JabberApplet.jar" code="org/jabber/applet/JabberApplet.class" height="200" width="200" viewastext="viewastext">            <param name="xmlhostname" value="<?php echo $host->name; ?>" />
            <param name="user" value="<?php echo $user->username; ?>" />
        </applet>
    </body>
</html>