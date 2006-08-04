<?php
$view =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$title = $view['l10n']->get('popup');
if (array_key_exists('popup_title', $view))
{
    $title = $view['popup_title'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="" lang="">
    <head>
        <?php
        echo "<title>{$title}</title>\n";
        $_MIDCOM->print_head_elements();
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".MIDCOM_STATIC_URL."/midcom.helper.datamanager/columned.css\" />\n";
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".MIDCOM_STATIC_URL."/org.openpsa.core/popup.css\" />\n";
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".MIDCOM_STATIC_URL."/org.openpsa.core/print.css\" media=\"print\" />\n";        
        ?>
    </head>
    <body id="org_openpsa_popup"<?php $_MIDCOM->print_jsonload(); ?>>
        <div id="container">
            <?php
            echo "<h1>{$title}</h1>\n";
            ?>        
            <div id="org_openpsa_toolbar">
                    <?php
                    $toolbars =& midcom_helper_toolbars::get_instance();
                    if (count($toolbars->bottom->items) > 0)
                    {
                        echo $toolbars->bottom->render();
                    }
                    ?>
            </div>
            <div id="org_openpsa_messagearea">
            </div>                
            <div id="content">
