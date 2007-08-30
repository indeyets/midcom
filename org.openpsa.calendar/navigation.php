<?php

/**
 * @package org.openpsa.calendar
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: navigation.php,v 1.3 2006/02/16 12:59:20 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.calendar NAP interface class.
 *
 * NAP is mainly used for toolbar rendering in this component
 *
 * ...
 * @package org.openpsa.calendar
 */
class org_openpsa_calendar_navigation extends midcom_baseclasses_components_navigation
{

    function get_leaves()
    {
        $leaves = array();
        return $leaves;
    }

    function get_node($toolbar = null)
    {
        $toolbar = Array();
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        return parent::get_node($toolbar);
    }
}

?>