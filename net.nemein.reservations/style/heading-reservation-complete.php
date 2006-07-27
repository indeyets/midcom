<?php

/*
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$topic = $config_dm->data;
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
$errstr =& $GLOBALS["midcom"]->get_custom_context_data("errstr");
$auth =& $GLOBALS["midcom"]->get_custom_context_data("auth");
$reservation =& $GLOBALS["midcom"]->get_custom_context_data("reservation");
*/
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$resource =& $GLOBALS["midcom"]->get_custom_context_data("resource");
$resource_guid = $resource->dm->data["_storage_guid"];
$data = $resource->dm->data;
$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$reservation =& $GLOBALS["midcom"]->get_custom_context_data("reservation");
$reservation_guid = $reservation->dm->data["_storage_guid"];
?>
<h1>&(topic.extra);: &(data['name']);</h1>
<h2><?echo $l10n->get("reservation complete"); ?></h2>
<p><a href="&(prefix);&(resource_guid);/reservation_print/&(reservation_guid);.html"><?echo $l10n->get("print view"); ?></a></p>
