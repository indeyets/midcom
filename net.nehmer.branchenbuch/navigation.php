<?php
/**
 * @package net.nehmer.branchenbuch
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Branchenbuch (Yellow Pages) NAP interface class
 *
 * @package net.nehmer.branchenbuch
 */

class net_nehmer_branchenbuch_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function net_nehmer_branchenbuch_navigation()
    {
        parent::midcom_baseclasses_components_navigation();
    }

    function get_leaves()
    {
        $leaves = Array();
        $schemamgr = new net_nehmer_branchenbuch_schemamgr($this->_topic);

        $result = net_nehmer_branchenbuch_branche::list_root_categories();

        foreach ($result as $branche)
        {
            $leaves[$branche->guid] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => $schemamgr->get_root_category_url($branche->type, $branche->guid),
                    MIDCOM_NAV_NAME => $branche->name
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_NAV_GUID => $branche->guid,
                MIDCOM_NAV_TOOLBAR => null,
                MIDCOM_META_CREATOR => 1,
                MIDCOM_META_EDITOR => 1,
                MIDCOM_META_CREATED => time(),
                MIDCOM_META_EDITED => time()
            );

        }

        $leaves[NET_NEHMER_BRANCHENBUCH_LEAFID_ADDENTRY] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => "entry/add.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('add entry'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $this->_topic->revised
        );
        $leaves[NET_NEHMER_BRANCHENBUCH_LEAFID_LISTSELF] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => "entry/list/self.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('your entries'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $this->_topic->revised
        );

        return $leaves;
    }

    /*
    function get_node()
    {
        return array
        (
            MIDCOM_NAV_URL => '',
            MIDCOM_NAV_NAME => $this->_topic->extra,
            MIDCOM_NAV_TOOLBAR => $toolbar,
            MIDCOM_NAV_NOENTRY => $hidden,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $this->_topic->revised
        );
    }
     */

}

?>