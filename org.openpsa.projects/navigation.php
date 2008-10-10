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
 * @package org.openpsa.projects
 */
class org_openpsa_projects_navigation extends midcom_baseclasses_components_navigation
{

    /**
     * Returns a static leaf list with access to the archive.
     */
    function get_leaves()
    {
        $leaves = array();

        $leaves["{$this->_topic->id}:tasks_open"] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => "task/list/all/open/",
                MIDCOM_NAV_NAME => $this->_l10n->get('open tasks'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
            MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
            MIDCOM_META_CREATED => $this->_topic->metadata->created,
            MIDCOM_META_EDITED => $this->_topic->metadata->revised
        );

        $leaves["{$this->_topic->id}:tasks_closed"] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => "task/list/all/closed/",
                MIDCOM_NAV_NAME => $this->_l10n->get('closed tasks'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
            MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
            MIDCOM_META_CREATED => $this->_topic->metadata->created,
            MIDCOM_META_EDITED => $this->_topic->metadata->revised
        );

        $leaves["{$this->_topic->id}:tasks_invoiceable"] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => "task/list/all/invoiceable/",
                MIDCOM_NAV_NAME => $this->_l10n->get('invoiceable tasks'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
            MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
            MIDCOM_META_CREATED => $this->_topic->metadata->created,
            MIDCOM_META_EDITED => $this->_topic->metadata->revised
        );

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