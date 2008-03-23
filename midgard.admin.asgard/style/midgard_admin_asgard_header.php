<?php
// Check the user preference and configuration
$config =& $GLOBALS['midcom_component_data']['midgard.admin.asgard']['config'];
if (   midgard_admin_asgard_plugin::get_preference('escape_frameset')
    || (   midgard_admin_asgard_plugin::get_preference('escape_frameset') !== '0'
        && $config->get('escape_frameset')))
{
    $_MIDCOM->add_jsonload('if(top.frames.length != 0 && top.location.href != this.location.href){top.location.href = this.location.href}');
}

//don't send an XML prolog for IE, it knocks IE6 into quirks mode
$client = $_MIDCOM->get_client();
if (!$client[MIDCOM_CLIENT_IE])
{
    echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
}

$pref_found = false;

if (($width = midgard_admin_asgard_plugin::get_preference('offset')))
{
    $navigation_width = $width - 40;
    $content_offset = $width + 2;
    $pref_found = true;
}

if (midgard_admin_asgard_plugin::get_preference('enable_quicklinks') !== 'no')
{
    $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midgard.admin.asgard/object_browser.js');
    $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/jQuery/thickbox/jquery-thickbox-3.1.pack.js');
    $_MIDCOM->add_link_head
    (
        array
        (
            'rel' => 'stylesheet',
            'type' => 'text/css',
            'href' => MIDCOM_STATIC_URL . '/jQuery/thickbox/thickbox.css',
            'media' => 'screen',
        )
    );
    $_MIDCOM->add_jscript('var tb_pathToImage = "' . MIDCOM_STATIC_URL . '/jQuery/thickbox/loadingAnimation.gif"');
}


// JavasScript libraries required by Asgard
$_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/jQuery/ui/ui.mouse.js');
$_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/jQuery/ui/ui.draggable.js');
$_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midgard.admin.asgard/resize.js');
$_MIDCOM->add_jscript("var MIDGARD_ROOT = '{$_MIDGARD['self']}';");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $_MIDCOM->i18n->get_current_language(); ?>" lang="<?php echo $_MIDCOM->i18n->get_current_language(); ?>">
    <head>
        <title><?php echo $_MIDCOM->get_context_data(MIDCOM_CONTEXT_PAGETITLE); ?> (Asgard for <(title)>)</title>
        <link rel="stylesheet" type="text/css" href="<?php echo MIDCOM_STATIC_URL; ?>/midgard.admin.asgard/screen.css" media="screen,projector" />
        <link rel="stylesheet" type="text/css" href="<?php echo MIDCOM_STATIC_URL; ?>/midcom.helper.datamanager2/legacy.css" media="all" />
        <?php
        $_MIDCOM->print_head_elements();
        if ($pref_found)
        {?>
              <style type="text/css">
                #container #navigation
                {
                 width: &(navigation_width);px;
                }
                
                #container #content
                {
                  margin-left: &(content_offset);px;
                }
            </style>
        <?php } ?>
        <!--[if IE 6]>
            <script type="text/javascript">
                var ie6 = true;
            </script>
        <![endif]-->
    </head>
    <body class="asgard"<?php $_MIDCOM->print_jsonload(); ?>>
        <div id="container-wrapper">
            <div id="container">
                <div id="navigation">
