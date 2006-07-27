<?php
$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");

$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$resource =& $GLOBALS["midcom"]->get_custom_context_data("resource");
$reservation =& $GLOBALS["midcom"]->get_custom_context_data("reservation");

$data = $reservation->dm->data;
$start = $data["start"];
$end = $data["end"];

?>
<h2>&(data['name']);</h2>

<p>
<strong><?echo $l10n->get("reservation start time:"); ?></strong><br />
&(start['local_strfulldate']);
</p>
<p>
<strong><?echo $l10n->get("reservation end time including the buffer:"); ?></strong><br />
&(end['local_strfulldate']);
</p>
<?php
$reservation->dm->display_view();
?>
