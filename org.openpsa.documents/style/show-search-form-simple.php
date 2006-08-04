<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<div class="area">
    <h2><?php echo $view_data['l10n']->get('document search'); ?></h2>
    <form method="get" action="&(node[MIDCOM_NAV_FULLURL]);search/">
        <input type="text" name="search"<?php
        if (array_key_exists('search', $_GET))
        {
            echo " value=\"{$_GET['search']}\"";
        }
        ?> />
        <input type="submit" value="<?php echo $view_data['l10n']->get("search"); ?>" />
    </form>        
</div>