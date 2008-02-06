<?php
echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$title = $data['l10n']->get('popup');
if (array_key_exists('popup_title', $data))
{
    $title = $data['popup_title'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $_MIDCOM->i18n->get_content_language(); ?>" lang="<?php echo $_MIDCOM->i18n->get_content_language(); ?>">
    <head>
    <title><?php echo htmlspecialchars($title); ?></title>
    <?php
    $_MIDCOM->print_head_elements();
    ?>    
    <link rel="stylesheet" type="text/css" href="<?php echo MIDCOM_STATIC_URL; ?>/midcom.helper.datamanager/columned.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo MIDCOM_STATIC_URL; ?>/org.openpsa.core/popup.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo MIDCOM_STATIC_URL; ?>/org.openpsa.core/print.css" media="print" />
    </head>
    <body id="org_openpsa_popup"<?php $_MIDCOM->print_jsonload(); ?>>
        <div id="container">
            <?php
            echo "<h1>{$title}</h1>\n";
            ?>
            <div id="org_openpsa_toolbar">
                    <?php
                    $_MIDCOM->toolbars->show_view_toolbar();
                    ?>
            </div>
            <div id="org_openpsa_messagearea">
            </div>
            <div id="content">
