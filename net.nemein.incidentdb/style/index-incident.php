<?php
global $view;
global $view_datamanager;
global $view_incident_type;
global $view_l10n;
global $view_l10n_midcom;
global $view_auth;

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$data = $view_datamanager->get_array();
$schemadb = $view_datamanager->get_layout_database();
$schema = $schemadb[$view_datamanager->get_layout_name()];
?>

<tr>
<td>
<?php if ($view_auth->can_write()) { ?>
  <a href="&(prefix);edit/&(view.id);.html"><?echo $view_l10n_midcom->get("edit"); ?></a>
<?php } ?>
  <a href="&(prefix);view/&(view.id);.html"><?echo $view_l10n_midcom->get("view"); ?></a>
</td>

<?php
foreach ($schema["fields"] as $name => $field) {
    echo "<td>";
    $view_datamanager->display_view_field($name);
    echo "</td>\n";
}
?>

</tr>