<?php
$_MIDCOM->auth->require_admin_user();
if (   isset($_POST['fetch_user'])
    && !empty($_POST['fetch_user']))
{
    $fetch_user =& $_POST['fetch_user'];
}
else
{
    $fetch_user = false;
}
if ($fetch_user)
{
    $handler = new org_maemo_gforgeprofileupdater();
    $user = $handler->get_gforge_user($fetch_user);
    echo "\$handler->get_gforge_user({$fetch_user}) returned:<pre>\n";
    print_r($user);
    echo "</pre>\n";
    echo "Last soap error: " . $handler->get_soap_error() . "<br/>\n";

    $ret = $handler->call_gforge('userGetGroups', array('user_id' => (int)$user->user_id));
    echo "\$handler->call_gforge('userGetGroups', array('user_id' => (int){$user->user_id})) returned:<pre>\n";
    print_r($ret);
    echo "</pre>\n";
    echo "Last soap error: " . $handler->get_soap_error() . "<br/>\n";

    $handler->destroy_soap();
}

?>
<form method="post">
    Username: <input type="text" name="fetch_user" value="<?php echo $fetch_user; ?>" />
    <input type="submit" value="Fetch" />
</form>
