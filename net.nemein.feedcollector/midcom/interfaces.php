<?php
/**
 * @package net.nemein.feedcollector 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for net.nemein.feedcollector
 * 
 * @package net.nemein.feedcollector
 */
class net_nemein_feedcollector_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function __construct()
    {
        parent::__construct();
        $this->_component = 'net.nemein.feedcollector';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'viewer.php', 
            'navigation.php',
            'topic.php',
        );
        
        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
        );
    }
    
    /**
     * Simple lookup method which tries to map the guid to an article of out topic.
     */
    function _on_resolve_permalink(&$topic, &$config, $guid)
    {
        $fc_topic = new net_nemein_feedcollector_topic_dba($guid);

        if (   ! $fc_topic
            || $fc_topic->node != $topic->id)
        {
            return null;
        }

        return '';
    }

}
?>