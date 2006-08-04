<?php

/**
 * @package org.openpsa.projects
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: navigation.php,v 1.1 2005/06/20 17:49:05 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.projects NAP interface class.
 * 
 * NAP is mainly used for toolbar rendering in this component
 * 
 * ...
 * @package org.openpsa.projects
 */
class org_openpsa_projects_navigation extends midcom_baseclasses_components_navigation
{

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