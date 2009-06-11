<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Very simple attachment serving by guid.
 *
 * @package midcom_core
 */
class midcom_core_controllers_attachment
{

    public function __construct($instance)
    {
      $this->configuration = $instance->configuration;
    }
    
    /**
     * Function serves the attachment by provided guid and exits.
     * @todo: Permission handling
     * @todo: Direct filesystem serving
     * @todo: Configuration options
     */
    public function get_serve($args)
    {
        $att = new midgard_attachment($args['guid']);
                
        if ($_MIDCOM->configuration->enable_attachment_cache)
        {
            header('Location: ' . midcom_core_helpers_attachment::get_url($att));
            exit();
        }

        $blob = new midgard_blob($att);
        
        header('Content-type: '.$att->mimetype);
        /**
          * If X-Sendfile support is enabled just sending correct headers
          */
        if ($_MIDCOM->configuration->enable_xsendfile)
        {
            header('X-Sendfile: ' . $blob->get_path());
        }
        else
        {
            echo $blob->read_content();
        }
        exit();
    }
}
?>
