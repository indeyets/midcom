<?php

/**
 * @package org.openpsa.contacts
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: navigation.php,v 1.6 2006/02/20 13:53:34 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.org NAP interface class.
 * 
 * NAP is mainly used for toolbar rendering in this component
 * 
 * ...
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_navigation extends midcom_baseclasses_components_navigation
{
    function _is_initialized()
    {
        $config = false;
        if (org_openpsa_contacts_interface::find_root_group($config))
        {
            return true;
        }
        return false;
    }

    function get_leaves()
    {
        $leaves = array();
        return $leaves;
    }
    
    function get_node($toolbar = null)
    {
        $toolbar = Array();
        $prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        return parent::get_node($toolbar);
    }
}

?>