<?php
/**
 * @package net.nemein.downloads
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Download manager MidCOM interface class.
 *
 * @package net.nemein.downloads
 */
class net_nemein_downloads_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_downloads_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'net.nemein.downloads';
        $this->_autoload_files = array
        (
            'viewer.php',
            'navigation.php',
        );
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
        );
    }

    function _on_resolve_permalink($topic, $config, $guid)
    {
        $article = new midcom_db_article($guid);
        if (   ! $article
            || $article->topic != $topic->id)
        {
            return null;
        }
        return "{$article->name}.html";
    }
}

?>