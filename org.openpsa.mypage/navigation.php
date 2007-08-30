<?php

/**
 * @package org.openpsa.mypage
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: navigation.php,v 1.1 2005/07/04 12:53:47 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.mypage NAP interface class.
 *
 * NAP is mainly used for toolbar rendering in this component
 *
 * @package org.openpsa.mypage
 */
class org_openpsa_mypage_navigation extends midcom_baseclasses_components_navigation
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