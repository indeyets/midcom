<?php

/*
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$topic = $config_dm->data;
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$errstr =& $GLOBALS["midcom"]->get_custom_context_data("errstr");
$auth =& $GLOBALS["midcom"]->get_custom_context_data("auth");
$reservation =& $GLOBALS["midcom"]->get_custom_context_data("reservation");
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$resource =& $GLOBALS["midcom"]->get_custom_context_data("resource");
$resource->dm->display_view();
*/

$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");

?>
<form action="" class="datamanager" enctype="multipart/form-data" method="POST">
    <fieldset class="date">
        <label for="net_nemein_reservations_form_start"><?echo $l10n->get("reservation start time:"); ?><br />
            <?echo $l10n->get("start time format is YYYY-MM-DD HH:MM"); ?><br />
            <input id="net_nemein_reservations_form_start" name="form_start"<?php
            if (array_key_exists("form_start", $_REQUEST)) 
            {
                echo " value=\"{$_REQUEST['form_start']}\" ";
            } 
            else
            {
                echo " value=\"".date('Y-m-d H:i', time())."\" ";
            } 
            ?>size="20" class="date" maxlength="20" /> 
            <button type="button" class="date" id="net_nemein_reservations_form_start_trigger" onclick="showCalendar<?php echo md5('net_nemein_reservations_form_start'); ?>();"></button>
        </label>
    </fieldset>
    <div class="form_toolbar">
        <input type="submit" name="form_submit" accesskey="s" value="<?echo $l10n_midcom->get("next");?>" />
        <input type="submit" name="form_cancel" value="<?echo $l10n_midcom->get("cancel");?>" />
    </div>
</form>