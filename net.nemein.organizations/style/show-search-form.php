<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<form method="get" class="datamanager">
    <input type="hidden" name="net_nemein_organizations_search[1][property]" value="city" />
    <input type="hidden" name="net_nemein_organizations_search[1][constraint]" value="LIKE" />
    <label>
        <span class="field_text"><?php echo sprintf($data['l10n']->get('%s includes'), $data['l10n']->get('city')); ?></span>
        <input class="shorttext" type="text" name="net_nemein_organizations_search[1][value]" />
    </label>

    <div class="form_toolbar">
        <input type="submit" accesskey="s" class="search" value="<?php echo $data['l10n']->get('search'); ?>" />
    </div>
</form>