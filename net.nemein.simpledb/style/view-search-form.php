<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<form method="post" action="&(prefix);">
    <label for="net_nemein_simpledb_search">
        <?php echo $data['l10n']->get('search'); ?>
        <input id="net_nemein_simpledb_search" type="text" name="net_nemein_simpledb_viewer_query" value="&(data['query']);" />
    </label>
    <input type="submit" name="net_nemein_simpledb_viewer_query_submit" value="<?php echo $data['l10n']->get('go'); ?>" />
</form>