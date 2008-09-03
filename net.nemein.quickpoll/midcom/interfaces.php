<?php
/**
 * @package net.nemein.quickpoll 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for net.nemein.quickpoll
 * 
 * @package net.nemein.quickpoll
 */
class net_nemein_quickpoll_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function __construct()
    {
        parent::__construct();

        define('NET_NEMEIN_QUICKPOLL_LEAFID_ARCHIVE', 1);

        $this->_component = 'net.nemein.quickpoll';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'viewer.php', 
            'navigation.php',
            'option.php',
            'vote.php',
        );
        
        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
            'org.openpsa.qbpager',
        );
    }

    function _on_resolve_permalink($topic, $config, $guid)
    {
        $article = new midcom_baseclasses_database_article($guid);
        if (   ! $article
            || $article->topic != $topic->id)
        {
            return null;
        }

        return "{$article->guid}.html";
    }
}
?>