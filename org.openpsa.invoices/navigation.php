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
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns a static leaf list with access to the archive.
     */
    function get_leaves()
    {
        $leaves = array();

        $leaves[$this->_topic->id . ':from_projects'] = array
        (
            MIDCOM_NAV_SITE => array
            (
                MIDCOM_NAV_URL => "projects/",
                MIDCOM_NAV_NAME => $this->_l10n->get('project invoicing'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
            MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
            MIDCOM_META_CREATED => $this->_topic->metadata->created,
            MIDCOM_META_EDITED => $this->_topic->metadata->revised
        );
        return $leaves;
    }

}
?>