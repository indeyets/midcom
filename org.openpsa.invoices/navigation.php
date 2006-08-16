<?php
/**
 * @package org.openpsa.invoices
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: navigation.php,v 1.2 2006/06/01 15:28:20 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.invoices NAP interface class.
 * 
 * @package org.openpsa.invoices
 */
class org_openpsa_invoices_navigation extends midcom_baseclasses_components_navigation
{

    /**
     * Simple constructor, calls base class.
     */
    function org_openpsa_invoices_navigation()
    {
        parent::midcom_baseclasses_components_navigation();
    }
    
    /**
     * Returns a static leaf list with access to the archive.
     */
    function get_leaves()
    {
        $leaves = array();
        
        /*
        $leaves[$this->_topic->id.':from_projects'] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => "projects.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('project invoicing'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $this->_topic->revised
        );
        */

        return $leaves;
    }
}

?>