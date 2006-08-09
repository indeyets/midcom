<?php
/**
 * @package org.openpsa.products 
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: navigation.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * TAViewer NAP interface class
 *
 * This class has been rewritten for MidCOM 2.6 utilizing all of the currently
 * available state-of-the-art technology.
 *
 * See the individual member documentations about special NAP options in use.
 *
 * @package org.openpsa.products
 */

class org_openpsa_products_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function org_openpsa_products_navigation()
    {
        parent::midcom_baseclasses_components_navigation();
    }

    /**
     * Returns all leaves for the current content topic.
     *
     * It will hide the index leaf from the NAP information unless we are in Autoindex
     * mode. The leaves' title are used as a description within NAP, and the toolbar will
     * contain edit and delete links.
     */
    function get_leaves()
    {
        return array();
    }
}

?>
