<?php

/*
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$topic = $config_dm->data;
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
$errstr =& $GLOBALS["midcom"]->get_custom_context_data("errstr");
$auth =& $GLOBALS["midcom"]->get_custom_context_data("auth");
$resource =& $GLOBALS["midcom"]->get_custom_context_data("resource");
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
*/
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$reservation =& $GLOBALS["midcom"]->get_custom_context_data("reservation");
$resource =& $GLOBALS["midcom"]->get_custom_context_data("resource");
$resource_guid = $resource->dm->data["_storage_guid"];
$reservation_guid = $reservation->dm->data["_storage_guid"];
$data = $reservation->dm->data;
?>
<li class="vevent" id="<?php echo $reservation->event->guid(); ?>-midgardGuid">
    <abbr class="dtstart" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $data["start"]["timestamp"]); ?>"><?php echo midcom_helper_generate_daylabel('start', $data["start"]["timestamp"], $data["end"]["timestamp"]); ?></abbr> -
    <abbr class="dtend" title="<?php echo gmdate('Y-m-d\TH:i:s\Z', $data["end"]["timestamp"]); ?>"><?php echo midcom_helper_generate_daylabel('end', $data["start"]["timestamp"], $data["end"]["timestamp"]); ?></abbr>
    <span class="summary"><a href="&(prefix);&(resource_guid);/reservation/&(reservation_guid);.html">&(data["name"]);</a></span>
</li>
