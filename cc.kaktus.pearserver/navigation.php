<?php
/**
 * @package cc.kaktus_pearserver
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: navigation.php 4198 2006-09-25 14:20:07Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * PEAR server NAP interface class
 *
 * @package cc.kaktus_pearserver
 */
class cc_kaktus_pearserver_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function cc_kaktus_pearserver_navigation()
    {
        parent::midcom_baseclasses_components_navigation();
    }

    /**
     * Returns all leaves for the current content topic.
     * 
     * @TODO: This needs to be written to show the pseudo leaves
     */
    function get_leaves()
    {
        // Prepare everything
        $leaves = array ();
        
        $leaves["{$this->_topic->id}_upload"] = array
        (
            MIDCOM_NAV_SITE => array
            (
                MIDCOM_NAV_URL => 'upload/',
                MIDCOM_NAV_NAME => $this->_l10n->get('upload a release'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_NAV_GUID => null,
            MIDCOM_NAV_OBJECT => null,
            MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
            MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
            MIDCOM_META_CREATED => $this->_topic->metadata->created,
            MIDCOM_META_EDITED => $this->_topic->metadata->revised,
        );
        
        return $leaves;
    }
}
?>
