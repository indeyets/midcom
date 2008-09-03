<?php
/**
 * @package org.dgap.ipautologin 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for org.dgap.ipautologin
 * 
 * @package org.dgap.ipautologin
 */
class org_dgap_ipautologin_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function org_dgap_ipautologin_interface()
    {
        parent::__construct();
        $this->_component = 'org.dgap.ipautologin';
        $this->_purecode = true;
        $this->_autoload_files = array
        (
        );
        $this->_autoload_libraries = array
        (
        );
    }

    function _on_initialize()
    {
        return true;
    }

    function ip_login()
    {
        if ($_MIDCOM->auth->user)
        {
            // already logged in with some privileges, let's not override those
            return true;
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        $_MIDCOM->auth->request_sudo();
        $host = new midcom_db_host($_MIDGARD['host']);
        $host_credentials = $host->list_parameters('org.dgap.ipautologin:credentials');
        $_MIDCOM->auth->drop_sudo();
        foreach ($host_credentials as $range => $credentials_b64)
        {
            // Separate mask and address parts, typecast mask to int
            list ($address, $cidrmask) = explode('/', $range);
            settype($cidrmask, 'int');
            // Separate address octets
            $remote_octets = explode('.', $_SERVER['REMOTE_ADDR']);
            $address_octets = explode('.', $address);
            if (count($address_octets) != 4)
            {
                // Somehow an invalid address slipped through the cracks, log the issue and skip further processing
                debug_add("Range '{$range}' is invalid. The range manager should have caught this", MIDCOM_LOG_ERROR);
                continue;
            }
            // Format addresses to binary and separate the "masked" part for comparison
            $remote_binary = str_pad(decbin($remote_octets[0]), 8, '0', STR_PAD_LEFT) . 
                             str_pad(decbin($remote_octets[1]), 8, '0', STR_PAD_LEFT) .
                             str_pad(decbin($remote_octets[2]), 8, '0', STR_PAD_LEFT) .
                             str_pad(decbin($remote_octets[3]), 8, '0', STR_PAD_LEFT);
            $remote_unmasked_bits = substr($remote_binary, 0, $cidrmask);
            $address_binary = str_pad(decbin($address_octets[0]), 8, '0', STR_PAD_LEFT) . 
                              str_pad(decbin($address_octets[1]), 8, '0', STR_PAD_LEFT) .
                              str_pad(decbin($address_octets[2]), 8, '0', STR_PAD_LEFT) .
                              str_pad(decbin($address_octets[3]), 8, '0', STR_PAD_LEFT);
            $address_unmasked_bits = substr($address_binary, 0, $cidrmask);

            $msg = "{$_SERVER['REMOTE_ADDR']} vs {$range}
remote_binary:    {$remote_binary}
address_binary:   {$address_binary}
remote_unmasked:  {$remote_unmasked_bits}
address_unmasked: {$address_unmasked_bits}\n";
            debug_add($msg);

            if ($remote_unmasked_bits !== $address_unmasked_bits)
            {
                // Remote address does not match this range, check next range
                continue;
            }

            debug_add('match found, trying to authenticate');
            // Decode credentials and try to log in
            $credentials = base64_decode($credentials_b64);
            list ($username, $password) = explode(':', $credentials);
            if (!$_MIDCOM->auth->_auth_backend->create_login_session($username, $password))
            {
                // Valid address match but failed login
                debug_add("{$_SERVER['REMOTE_ADDR']} matches {$range}, but create_login_session() returned failure", MIDCOM_LOG_WARN);
                continue;
            }
            // Auth ok
            debug_add("Authenticated as {$_MIDCOM->auth->user->name}");
            debug_pop();
            return true;
        }
        debug_add('Could not log in based on IP address');
        debug_pop();
        return false;
    }
}

?>