<?php
/**
 * @package net.nemein.organizations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 4840 2006-12-29 06:25:07Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Selection handler. Catches the POST request to select user's "default organization"
 * and redirects back to that.
 *
 * @package net.nemein.organizations
 */

class net_nemein_organizations_handler_select extends midcom_baseclasses_components_handler
{
    /**
     * Simple default constructor.
     */
    function net_nemein_organizations_handler_select()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Select the given group
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_select($handler_id, $args, &$data)
    {
        if (   !$_MIDCOM->auth->user
            || !isset($_POST['group']))
        {
            $_MIDCOM->relocate('');
            // This will exit
        }
        
        $person = $_MIDCOM->auth->user->get_storage();
        if ($_POST['group'] == 'remove')
        {
            $person->set_parameter('net.nemein.organizations', 'default_organization', '');
        }
        else
        {
            $group = new midcom_db_group($_POST['group']);
            if (!$group->guid)
            {
                return false;
            }
            
            $person->set_parameter('net.nemein.organizations', 'default_organization', $group->guid);
        }
        
        if (isset($_POST['return_url']))
        {
            $_MIDCOM->relocate($_POST['return_url']);
        }
        
        $_MIDCOM->relocate('');
    }
}