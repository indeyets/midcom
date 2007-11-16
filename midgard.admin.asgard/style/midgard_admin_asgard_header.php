<?php
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";

if (($width = midgard_admin_asgard_plugin::get_preference('offset')))
{
    $width -= 40;
    $navigation_width = " style=\"width: {$width}px\"";
}
else
{
    $navigation_width = '';
}
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
        ?>
        <!--[if IE 6]>
            <script type="text/javascript">
                var ie6 = true;
            </script>
        <![endif]-->
    </head>
    <body class="asgard"<?php $_MIDCOM->print_jsonload(); ?>>
        <div id="container-wrapper">
            <div id="container">
                <div id="navigation"&(navigation_width:h);>
