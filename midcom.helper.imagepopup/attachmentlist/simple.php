<?php
/**
 * Created on Jan 28, 2006
 * @author tarjei huse
 * @package midcom.helper.imagepopup
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
/**
 * A class for generating a list of images.
 */
class midcom_helper_imagepopup_attachmentlist_simple {

    /**
     * List of attachments
     * @access public
     * @var array
     */
    var $attachments= array();
    
    /**
     * The object parent object
     */
    var $_object = null;
    
    /**
     * The fieldnames for the attachments
     */
    var $_fieldnames = array();
    
    var $files  = array( );
    var $images = array( );
    
    /**
     * Takes an object and generates a listing
     */
    function midcom_helper_imagepopup_attachmentlist_simple (&$object) 
    {
        $this->_object = $object;
        $this->_generate_listing();        
    }
    /**
     * Generates the listing.
     */
    function _generate_listing() 
    {
        $files = $this->_object->listattachments();
        if ( !$files) 
        {
            return;
        }
        while ($files->fetch()) 
        {
            $this->files[] = $this->make_wrapper(&$files);
        }
    }
    /**
     * Factory method to make sure we use the correct wrappper.
     * @param object midgard_attachment
     * @access public
     */
    function make_wrapper (&$file) 
    {
        
        if ( substr($file->mimetype, 0, 5) == 'image' )
        {
            return  new midcom_helper_imagepopup_image ($file->id);
        } else {
            return  new midcom_helper_imagepopup_attachment ($file->id);
        }
        
    }
    /**
     * Returns the whole images array.
     * @access public
     */
    function get_images() 
    {
        return $this->images;
    }
    
    function &get_files() 
    {
        return $this->files;
    }
}
 
?>