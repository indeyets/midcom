<?php
global $view;
global $view_datamanager;
global $view_incident_type;
global $view_l10n;
global $view_l10n_midcom;
global $view_auth;

$data = $view_datamanager->get_array();
$schemadb = $view_datamanager->get_layout_database();
$schema = $schemadb[$view_datamanager->get_layout_name()];
?>

<tr>
<th>&nbsp;</th>

<?php
foreach ($schema["fields"] as $name => $field) { 
    $string = $field["description"];
    if (strlen($string) > 20) 
        $string = substr($string,0,20) . "...";
    echo "<th>" . htmlspecialchars($string) . "</th>\n";
}
?>

</tr>