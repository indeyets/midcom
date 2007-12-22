<?php
/**
 * @package midgard.webdav.styles
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Base handler to share utility functions
 *
 * @package midgard.webdav.styles
 *
 */
class midgard_webdav_styles_handler extends midcom_baseclasses_components_handler {


    /**
     * _on_initialize is called by midcom on creation of the handler.
     */
    function _on_initialize()
    {

        require 'dav.php';
        require 'dav/styles.php';
        require 'dav/element.php';
        require 'dav/midcoms.php';
       	$_MIDCOM->cache->content->no_cache();
        $_MIDCOM->skip_page_style = true;

    }
    /**
     * Get the root styleobject
     * @return midcom_db_style the root style
     */
    function get_root_style(  ) {
        $style = new midcom_db_style($_MIDGARD['style']);
        $this->log(__CLASS__ . ": root style: " . $style->name);
        if (!$style->id)
        {
        	return false;
        }
        return $style;
    }

    function log($obj) {
        ob_start();
        echo "\n" . date("H:i:s" ) ." - ";
        print_r($obj);
        $end = ob_get_contents();
        ob_end_clean();
        error_log($end, 3, '/tmp/davlog.txt');
    }

}


?>
