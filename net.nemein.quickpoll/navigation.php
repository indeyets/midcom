<?php
/**
 * @package net.nemein.quickpoll 
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: navigation.php 4392 2006-10-22 08:39:17Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * net.nemein.quickpoll NAP interface class
 *
 * See the individual member documentations about special NAP options in use.
 *
 * @package net.nemein.quickpoll
 */
class net_nemein_quickpoll_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function net_nemein_quickpoll_navigation()
    {
        parent::midcom_baseclasses_components_navigation();
    }
    /**
     * Returns a static leaf list with access to the archive.
     */
    function get_leaves()
    {
        $leaves = array();
        
        if (   $this->_config->get('archive_enable')
            && $this->_config->get('show_navigation_pseudo_leaves'))
        {
            $leaves[NET_NEMEIN_QUICKPOLL_LEAFID_ARCHIVE] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "archive/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('archive'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
                MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
                MIDCOM_META_CREATED => $this->_topic->metadata->created,
                MIDCOM_META_EDITED => $this->_topic->metadata->revised
            );
        }
        
        return $leaves;
    }
}

?>
