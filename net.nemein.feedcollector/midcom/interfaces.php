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
    function net_nemein_feedcollector_interface()
    {
        parent::midcom_baseclasses_components_interface();
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
            'midcom.helper.dm2config',
        );
    }
    
    /**
     * Simple lookup method which tries to map the guid to an article of out topic.
     */
    function _on_resolve_permalink(&$topic, &$config, $guid)
    {
        /*
        $topic_guid = $config->get('symlink_topic');
        if ($topic_guid !== null)
        {
            $topic = new midcom_db_topic($topic_guid);
            // Validate topic.

            if (! $topic)
            {
                debug_add('Failed to open symlink content topic, (might also be an invalid object) last Midgard Error: '
                    . mgd_errstr(), MIDCOM_LOG_ERROR);
                $_MIDCOM->generate_error('Failed to open symlink content topic.');
                // This will exit.
            }

            if ($topic->component != 'net.nehmer.static')
            {
                debug_print_r('Retrieved topic was:', $topic);
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Symlink content topic is invalid, see the debug level log for details.');
                // This will exit.
            }
        }
        */

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