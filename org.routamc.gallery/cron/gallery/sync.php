<?php
/**
 * @package org.routamc.gallery
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: clearold.php,v 1.1 2006/04/19 14:08:46 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Cron handler for periodical gallery sync
 * @package org.routamc.gallery
 */
class org_routamc_gallery_cron_gallery_sync extends midcom_baseclasses_components_cron_handler
{
    function _on_initialize()
    {
        return true;
    }

    /**
     * Find all old temporary reports and clear them.
     */
    function _on_execute()
    {
        //Disable limits, TODO: think if this could be done in smaller chunks to save memory.
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', 0);
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('_on_execute called');

        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('component', '=', 'org.routamc.gallery');
        $galleries = $qb->execute();
        
        foreach ($galleries as $gallery)
        {
            $helper = new org_routamc_gallery_helper($gallery);
            $helper->sync();
        }

        debug_add('done');
        debug_pop();
        return;
    }
}
?>