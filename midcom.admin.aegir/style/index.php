<?
// Bind the view data, remember the reference assignment:
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
//$data = $request_data['datamanager']->get_array();

$user = $_MIDCOM->auth->user->get_storage();

echo "<h1>". $request_data['l10n']->get("Welcome") ." " . $user->name . "</h1>";

?>