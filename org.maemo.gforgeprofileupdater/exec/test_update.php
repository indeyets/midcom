<?php
$_MIDCOM->componentloader->load('org.openpsa.contacts');
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
    $person = false;
    $qb = org_openpsa_contacts_person::new_query_builder();
    $qb->add_constraint('username', '=', $fetch_user);
    $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
    $persons = $qb->execute();
    if (!empty($persons))
    {
        $person = $persons[0];
    }
    if (!$person)
    {
        echo "ERROR: user '{$fetch_user}' not found in local db<br/>\n";
    }
    $handler = new org_maemo_gforgeprofileupdater();
    $user = $handler->get_gforge_user($fetch_user);
    /*
    echo "\$handler->get_gforge_user({$fetch_user}) returned:<pre>\n";
    print_r($user);
    echo "</pre>\n";
    echo "Last soap error: " . $handler->get_soap_error() . "<br/>\n";
    */
    if (!$user)
    {
        echo "ERROR: user '{$fetch_user}' not found in GForge<br/>\n";
        echo "Last soap error: " . $handler->get_soap_error() . "<br/>\n";
    }

    if (   $person
        && $user)
    {
        if (   strpos($user->firstname, '__o.m.gfpu_test__') === false
            && strlen($person->firstname . '__o.m.gfpu_test__') <= 32)
        {
            // append test string to firstname but take into account the varchar(32) field size
            $person->firstname .= '__o.m.gfpu_test__';
        }
        /*
        echo "Calling \$handler->updated(\$person)<br/>\n";
        $props = array('firstname', 'lastname', 'email');
        foreach ($props as $prop)
        {
            echo "&nbsp;&nbsp;&nbsp;\$person->{$prop} = {$person->$prop}<br/>\n";
        }
        */
        $ret = $handler->updated($person);
        echo "\$handler->updated(\$person) returned " . (int)$ret . "<br/>\n";
        echo "See debug log for SOAP error (updated method destroys the SOAP client at the end)<br/>\n";
    }

    $handler->destroy_soap();
}

?>
<form method="post">
    Username: <input type="text" name="fetch_user" value="<?php echo $fetch_user; ?>" />
    <input type="submit" value="Update" />
</form>
