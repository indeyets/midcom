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

    function org_routamc_gallery_photolink_dba($id = null)
    {
        return parent::__org_routamc_gallery_photolink_dba($id);
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