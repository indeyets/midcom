<?php
/**
 * @package fi.hut.loginbroker
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Simple, insecure token login handler. Include this file in your code-init-before-midcom to utilize.
 *
 * require(MIDCOM_ROOT . '/fi/hut/loginbroker/tokenlogin.php');
 *
 * All unauthenticated (no MidCOM login session) POST requests will be intercepted by this tool, and
 * the following done:
 *
 * - Check if user has a simple token cookie
 * - If not, create new token and corresponding user to database
 * - Log the user in and send new token version if needed
 *
 * This is useful in sites where no real user identity is needed but components like net.nemein.favourites
 * and net.nemein.quickpoll are used.
 *
 * @package fi.hut.loginbroker
 */
if (!$_MIDGARD['user'])
{
    $session_exists = false;
    $session_token = '';
    if (!empty($_COOKIE))
    {
        foreach ($_COOKIE as $id => $data)
        {
            if ($id == 'PHPSESSID')
            {
                // We have *some* PHP session, don't token login
                //$session_exists = true;
                //continue;
            }
            elseif (substr($id, 0, 35) == 'midcom_services_auth_backend_simple')
            {
                // We have a MidCOM login session, don't token login
                $session_exists = true;
                continue;
            }
            elseif ($id == 'fi_hut_loginbroker_token')
            {
                // We have a token
                $session_token = $data;

                continue;
            }
        }
    }
    
    // Proceed only if no better login means (like MidCOM logins) exist
    if (!$session_exists)
    {
        $username_prefix = 'token_';
        $username = '';
        $password = '';
        if ($session_token)
        {
            $qb = new midgard_query_builder('midgard_person');
            $qb->add_constraint('username', '=', "{$username_prefix}{$session_token}");
            $qb->add_constraint('password', 'LIKE', '**%');
            $persons = $qb->execute();
            foreach ($persons as $person)
            {
                if (!$person->parameter('fi.hut.loginbroker', 'tokenlogin_enable'))
                {
                    continue;
                }
                $username = $person->username;
                $password = substr($person->password, 2);
            }
            
            if (   !$username
                || !$password)
            {
                // Clear the faulty token
                setcookie('fi_hut_loginbroker_token', '', time() - 3600);
            }
        }
        elseif (   $_POST
                && !isset($_POST['midcom_services_auth_frontend_form_submit']))
        {
            // We have POST data, create user and token
            $person = new midgard_person();
            $token = md5('Midgard ' . time() . $_SERVER['REMOTE_ADDR'] . $_MIDGARD['uri']);
            $person->username = "{$username_prefix}{$token}";

            // Generate random password
            $length = 8;
            if (function_exists('mt_rand'))
            {
                $rand = 'mt_rand';
            }
            else
            {
                $rand = 'rand';
            }
            $password = '';
            while($length--)
            {
                $password .= chr($rand(33,125));
            }
            $person->password = "**{$password}";
            
            $qb = new midgard_query_builder('midgard_person');
            $qb->add_constraint('username', '=', $person->username);
            if ($qb->count() == 0
                && $person->create())
            {
                $username = $person->username;
                $password = substr($person->password, 2);
                $person->parameter('fi.hut.loginbroker', 'tokenlogin_enable', 1);
                $person->parameter('fi.hut.loginbroker', 'tokenlogin_ip', $_SERVER['REMOTE_ADDR']);
                $person->parameter('midcom', 'first_login', time());
                
                // Send the token to the user
                setcookie('fi_hut_loginbroker_token', $token, time()+60*60*24*30, $_MIDGARD['self']);
            }
        }
        
        if (   $username
            && $password)
        {
            if (mgd_auth_midgard($username, $password))
            {
                $user = new midgard_person($_MIDGARD['user']);
                $user->parameter('midcom', 'last_login', time());
            }
            else
            {
                // Clear the faulty token
                setcookie('fi_hut_loginbroker_token', '', time() - 3600);
            }
        }
    }
}
?>