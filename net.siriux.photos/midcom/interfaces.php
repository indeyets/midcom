<?php

/**
 * @package net.siriux.photos
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Photo Gallery MidCOM interface class.	
 * 	
 * @package net.siriux.photos	
 */
class net_siriux_photos_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_siriux_photos_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'net.siriux.photos';
        $this->_autoload_files = Array('viewer.php', 'admin.php', 'navigation.php', 'Photo.php', 'filter.php');
        $this->_autoload_libraries = Array('midcom.helper.datamanager');
    }
	    
    /**
     * Iterate over all articles and create index record using the datamanger indexer
     * method.
     */
    function _on_reindex($topic, $config, &$indexer)
    {
    	debug_push_class(__CLASS__, __FUNCTION__);
        $articles = mgd_list_topic_articles($topic->id);
        if ($articles)
        {
            $this->_data['config']->store_from_object($topic, 'net.siriux.photos'); 
            while ($articles->fetch())
            {
                $photo = new siriux_photos_Photo($articles->id, $this->_data['config']);
                if ($photo)
                {
                    $photo->index();
                    $photo->datamanager->destroy();
                }
                else
                {
                    debug_add("Note: Could not create a photo object for article ID {$articles->id}, skipping.", MIDCOM_LOG_INFO);
                }
                // Update script execution time
                set_time_limit(30);                
            }
            $this->_data['config']->reset_local();
        }

        debug_pop();
        return true;
    }
}


?>