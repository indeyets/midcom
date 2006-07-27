<?php
global $view_l10n;
global $view_l10n_midcom;
global $view_auth;
global $view_datamanager;
global $view; // Incident event object

$incident_type = $GLOBALS["view_incident_type"];

$data = $view_datamanager->get_array();
if ($GLOBALS["view_mode"] == "create")
    $str = $view_l10n->get("create incident");
else
    $str = $view_l10n->get("edit incident");
$title = sprintf($str, $incident_type["name"]);

$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2>&(title);</h2>

<?php
 
// Last Ditch self-defense to avoid Privilige escalation through url-guessing
// Note, that you can dodge this check if you know how to write http post requests
// recognized by the midcom datamanager ;-), but I guess this is a risk we can take.

if ($view_auth->can_write())
    $view_datamanager->display_form(); 
else
    $view_datamanager->display_view();

?>