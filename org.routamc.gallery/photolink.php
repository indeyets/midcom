<?php
/**
 * @package org.routamc.gallery
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: aerodrome.php 3630 2006-06-19 10:03:59Z bergius $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for photolink objects
 *
 * @package org.routamc.gallery
 */
class org_routamc_gallery_photolink_dba extends __org_routamc_gallery_photolink_dba
{

    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    function get_parent_guid_uncached()
    {
        if ($this->node != 0)
        {
            $parent = new midcom_db_topic($this->node);
            return $parent->guid;
        }
        else
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("No node defined for this photolink", MIDCOM_LOG_DEBUG);
            debug_pop();
            return null;
        }
    }

    function _on_creating()
    {
        if (   !$this->photo
            || !$this->node)
        {
            return false;
        }
        return true;
    }

    function _on_updating()
    {
        if (   !$this->photo
            || !$this->node)
        {
            return false;
        }
        return true;
    }
}
?>