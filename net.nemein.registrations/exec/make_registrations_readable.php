<?php
$_MIDCOM->auth->require_admin_user();

echo "<h1>Making all registrations readable</h1>\n";

echo "<p>\n";
$qb = net_nemein_registrations_registration_dba::new_query_builder();
$registrations = $qb->execute();
foreach ($registrations as $registration)
{
    echo "<br />Registration {$registration->guid}...\n";
    $privs = midcom_core_privilege::get_all_privileges($registration->guid);
    $found = false;
    foreach ($privs as $privilege)
    {
        if (   $privilege->name == 'midgard:read'
            && $privilege->value == MIDCOM_PRIVILEGE_DENY)
        {
            $found = true;
            // Nuke it
            if ($privilege->drop())
            {
                echo "CLEARED\n";
            }
            else
            {
                echo "FAIL\n";
            }
        }
    }

    if (!$found)
    {
        echo "OK\n";
    }
}
echo "</p>\n";

echo "<p>All done.</p>\n";
?>