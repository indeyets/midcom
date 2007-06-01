<?php
/**
 * @package org.routamc.photostream
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: fetchflickr.php 4795 2006-12-19 13:42:39Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Cron handler for fetching person photos from Flickr
 * @package org.routamc.photostream
 */
class org_routamc_photostream_cron_fetchflickr extends midcom_baseclasses_components_cron_handler
{
    var $photostream_id = null;
    
    function _on_initialize()
    {
        $node_qb = midcom_db_topic::new_query_builder();
        $node_qb->add_constraint('component', '=', 'org.routamc.photostream');
        $nodes = $node_qb->execute();
        foreach ($nodes as $node)
        {
            $this->photostream_id = $node->id;
        }    
        return true;
    }

    /**
     * Fetches Flickr photos for users
     */
    function _on_execute()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('_on_execute called');
        
        if (!$this->photostream_id)
        {
            debug_pop();
            return false;
        }
        
        $flickr = org_routamc_photostream_importer::create('flickr', $this->photostream_id);
        $flickr->seek_flickr_users();

        debug_add('Done');
        debug_pop();
        return;
    }
}
?>