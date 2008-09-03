<?php
/**
 * @package org.routamc.photostream
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: navigation.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * o.r.photostream NAP interface class
 *
 * This class has been rewritten for MidCOM 2.6 utilizing all of the currently
 * available state-of-the-art technology.
 *
 * See the individual member documentations about special NAP options in use.
 *
 * @package org.routamc.photostream
 */

class org_routamc_photostream_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function __construct()
    {
        parent::__construct();
    }
    
    /**
     *
     *
     */
    function get_leaves()
    {
        if (   !$this->_config->get('moderate_uploaded_photos')
            || !$this->_topic->can_do('org.routamc.photostream:moderate'))
        {
            return array();
        }
        
        // Check the quantity of unapproved photos
        $qb = org_routamc_photostream_photo_dba::new_query_builder();
        $qb->add_constraint('status', '<>', ORG_ROUTAMC_PHOTOSTREAM_STATUS_UNMODERATED);
        
        if ($qb->count() === 0)
        {
            return array();
        }
        
        $leaves = array();
        
        $leaves["{$this->_topic->id}_ARCHIVE"] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => "moderate/",
                MIDCOM_NAV_NAME => $this->_l10n->get('moderate'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
            MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
            MIDCOM_META_CREATED => $this->_topic->metadata->created,
            MIDCOM_META_EDITED => $this->_topic->metadata->revised,
        );
        
        return $leaves;
    }
     
}

?>