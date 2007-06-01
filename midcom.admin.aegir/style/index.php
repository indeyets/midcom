<?
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');
//$dn_data= $data['datamanager']->get_array();

$user = $_MIDCOM->auth->user->get_storage();

echo "<h1>". $data['l10n']->get("Welcome") ." " . $user->name . "</h1>";

?>