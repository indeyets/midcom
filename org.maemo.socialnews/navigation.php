<?php
/**
 * @package org.maemo.socialnews 
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: navigation.php 6094 2007-06-01 15:50:49Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * org.maemo.socialnews NAP interface class
 *
 * See the individual member documentations about special NAP options in use.
 *
 * @package org.maemo.socialnews
 */
class org_maemo_socialnews_navigation extends midcom_baseclasses_components_navigation
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
        
        if ($this->_config->get('show_navigation_pseudo_leaves'))
        {
        
            $title = $this->_config->get('socialnews_title');
            if (empty($title))
            {
                $title = $this->_topic->extra;
            }
            
            $leaves["{$this->_topic->id}_BEST"] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "best/",
                    MIDCOM_NAV_NAME => sprintf($this->_l10n->get('best of %s'), $title),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
                MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
                MIDCOM_META_CREATED => $this->_topic->metadata->created,
                MIDCOM_META_EDITED => $this->_topic->metadata->revised,
            );
        
            $leaves["{$this->_topic->id}_ARCHIVE"] = array
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
                MIDCOM_META_EDITED => $this->_topic->metadata->revised,
            );
        }
        
        return $leaves;
    }
}

?>