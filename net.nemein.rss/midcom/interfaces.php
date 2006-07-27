<?php
/**
 * @package net.nemein.rss
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * RSS Aggregator MidCOM interface class.
 * 
 * @package net.nemein.rss
 */
class net_nemein_rss_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_rss_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'net.nemein.rss';
        $this->_autoload_files = Array
        (
            'viewer.php', 
            'admin.php', 
            'navigation.php',
            'magpierss/Snoopy.class.inc',
			'magpierss/rss_parse.inc',
			'magpierss/rss_cache.inc',
			'magpierss/rss_fetch.inc',
			'magpierss/rss_utils.inc',
        );
    }
    
    /**
     * Initialize MagpieRSS
     */
    function _on_initialize()
    {
        // RSS bandwidth usage settings
        define('MAGPIE_CACHE_ON', true);
        define('MAGPIE_CACHE_DIR', '/tmp/');

        // $midcom->cache->expires must match this
        define('MAGPIE_CACHE_AGE', 1800);

        // Get correct encoding for magpie
        $i18n =& $GLOBALS['midcom']->get_service('i18n');
        $encoding = $i18n->get_current_charset();
        // PHP's XML parser supports UTF-8 and ISO-LATIN-1
        if ($encoding == "ISO-8859-15") 
        {
            $encoding = "ISO-8859-1";
        }
        define('MAGPIE_OUTPUT_ENCODING', $encoding);

        return true;
    }
    
    /* No indexing at this time. */
}
?>
