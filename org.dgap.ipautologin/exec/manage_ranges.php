<?php
$_MIDCOM->auth->require_admin_user();
$host = new midcom_db_host($_MIDGARD['host']);

if (   isset($_POST['org_dgap_autologin_deleterange'])
    && is_array($_POST['org_dgap_autologin_deleterange']))
{
    foreach ($_POST['org_dgap_autologin_deleterange'] as $range => $dummy)
    {
        $host->delete_parameter('org.dgap.ipautologin:credentials', $range);
    }
}
elseif (   isset($_POST['org_dgap_autologin_save'])
        && isset($_POST['org_dgap_autologin'])
        && is_array($_POST['org_dgap_autologin']))
{
    foreach ($_POST['org_dgap_autologin'] as $data)
    {
        if (   empty($data['range'])
            || empty($data['username'])
            || empty($data['password']))
        {
            // incomplete data, skip
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.dgap.ipautologin', 'org.dgap.ipautologin'), $_MIDCOM->i18n->get_string('You must fill all the fields', 'org.dgap.ipautologin'), 'error');
            continue;
        }
        $credentials_b64 = base64_encode("{$data['username']}:{$data['password']}");
        if (strpos($data['range'], '/') === false)
        {
            // Single address, add correct CIDR mask
            $data['range'] .= '/32';
        }
        list ($address, $cidrmask) = explode('/', $data['range']);
        settype($cidrmask, 'int');
        // Separate address octets
        $address_octets = explode('.', $address);
        if (count($address_octets) != 4)
        {
            // Not valid IPv4 address, skip
            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.dgap.ipautologin', 'org.dgap.ipautologin'), sprintf($_MIDCOM->i18n->get_string('Range "%s" is invalid', 'org.dgap.ipautologin'), $data['range']), 'error');
            continue;
        }
        foreach ($address_octets as $octet)
        {
            if ((int)$octet > 255)
            {
                // Invalid octet, skip range
                $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('org.dgap.ipautologin', 'org.dgap.ipautologin'), sprintf($_MIDCOM->i18n->get_string('Range "%s" is invalid', 'org.dgap.ipautologin'), $data['range']), 'error');
                continue 2;
            }
        }
        $host->set_parameter('org.dgap.ipautologin:credentials', $data['range'], $credentials_b64);
    }
}
if (!empty($_POST))
{
    $_MIDCOM->relocate('/midcom-exec-org.dgap.ipautologin/manage_ranges.php?cache_avoidance=' . time());
}

$title = "IP autologins for {$host->name}";
if (   !empty($host->port)
    && $host->port != 80)
{
    $title .= ":{$host->port}";
}
if ($host->prefix)
{
    $title .= $host->prefix;
}

// Get UImessages working here
$_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . "/Pearified/JavaScript/Prototype/prototype.js");
$_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . "/Pearified/JavaScript/Scriptaculous/scriptaculous.js");
$_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.services.uimessages/protoGrowl.js');
$_MIDCOM->add_link_head(
    array
    (
        'rel'   => 'stylesheet',
        'type'  => 'text/css',
        'media' => 'screen',
        'href'  => MIDCOM_STATIC_URL . '/midcom.services.uimessages/protoGrowl.css',
    )
);
echo "<html>\n    <head></head>\n";
$_MIDCOM->print_head_elements();
echo "    <body>\n";
echo "        <h1>{$title}</h1>\n";
echo "        <p>Note: IP address ranges must be defined in <a href=\"http://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing\" target=\"_BLANK\"><abbr title=\"Classless Inter-Domain Routing\">CIDR</abbr></a> notation, only IPv4 supported for now.</p>\n";
echo "        <form method=\"POST\">\n";
?>
            <table>
                <tr>
                    <th>Address/range</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>&nbsp;</th>
                </tr>
<?php
$host_credentials = $host->list_parameters('org.dgap.ipautologin:credentials');
$i = 0;
foreach ($host_credentials as $range => $credentials_b64)
{
    $credentials = base64_decode($credentials_b64);
    list ($username, $password) = explode(':', $credentials);
?>
                <tr>
                    <td>
                        <input type="text" size="17" name="org_dgap_autologin[<?php echo $i ?>][range]" value="<?php echo $range; ?>" />
                    </td>
                    <td>
                        <input type="text" name="org_dgap_autologin[<?php echo $i; ?>][username]" value="<?php echo $username; ?>" />
                    </td>
                    <td>
                        <input type="text" name="org_dgap_autologin[<?php echo $i; ?>][password]" value="<?php echo $password; ?>" />
                    </td>
                    <td>
                        <button type="submit" class="delete" name="org_dgap_autologin_deleterange[<?php echo $range; ?>]"  title="Delete">
                            <img src="/midcom-static/stock-icons/16x16/trash.png" alt="Delete" />
                        </button>
                    </td>
                </tr>
<?php
    $i++;
}
?>
                <tr>
                    <td>
                        <input type="text" size="17" name="org_dgap_autologin[<?php echo $i; ?>][range]" value="" />
                    </td>
                    <td>
                        <input type="text" name="org_dgap_autologin[<?php echo $i; ?>][username]" value="" />
                    </td>
                    <td>
                        <input type="text" name="org_dgap_autologin[<?php echo $i; ?>][password]" value="" />
                    </td>
                    <td>
                        <button type="submit" class="save" name="org_dgap_autologin_save" title="Add">
                            <img src="/midcom-static/stock-icons/16x16/edit.png" alt="Add" />
                        </button>
                    </td>
                </tr>
            </table>
        </form>
        <p>
            Remember: To enable these automatic logins on-site you must add the following to <tt>code-init-after-midcom</tt>:
            <pre>
$interface = $_MIDCOM->componentloader->get_interface_class('org.dgap.ipautologin');
$interface->ip_login();
            </pre>
    </body>
</html>
<?php
$_MIDCOM->uimessages->show();
?>