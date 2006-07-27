<?php
/**
 * Created on Mar 12, 2006
 * @author tarjei huse
 * @package midcom.helper.imagepopup
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 * This handler shows the attachments attached to object $object.
 * 
 * It does not relate to any schemas or anything else. 
 * 
 * Use this if you do not have a schema to attach the images to.
 * 
 */
 
class midcom_helper_imagepopup_handler_advanced extends midcom_baseclasses_components_handler
{

    /**
     * The root or the listing, usually a topic.
     * @var object
     */
    var $_object = null;
    
    /**
     * The quickform object used to generate the form
     * @var object
     * @access private 
     */
    var $_form = null;
    /**
     * The list of attachments
     * @var array
     */
    var $_files = array();
    
    /**
     * Errordescription if it exists.
     */
    var $_error = "";
    
    /**
     * prefix
     * @var string
     * @access private
     */
    var $_prefix = '';
    /**
     * Imageinformation
     * @var string
     * @access private
     */
    var $_image_info = "var imagepopup_images = new Array();\n";
    
    function midcom_helper_imagepopup_handler_advanced()  
    {
        parent::midcom_baseclasses_components_handler();
    }
    /**
     * Used by midcom-exec. 
     */
    function exec_initialize() 
    {
        $_MIDCOM->cache->content->no_cache();
        $_MIDCOM->auth->require_valid_user();
        
        /* Check for aegir or normal style: */
        $style_attributes = array ( 'rel'   =>  "stylesheet" ,
                                    'type'  =>  "text/css" ,
                                    'media' =>  "screen"
                                    );
        $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.helper.imagepopup/styling.css";
        $_MIDCOM->add_link_head( $style_attributes);
        
        $this->_request_data['error'] = '';
        $this->_prefix = $GLOBALS['midcom_config']['midcom_site_url'];
        //require_once ("HTML/QuickForm.php");
        require_once ("HTML/QuickForm/Renderer/Object.php");
    }
    
    function _handler_list($id, $args)
    {
        
        $this->_object = $_MIDCOM->dbfactory->get_object_by_guid($args[0]);
        if (!$this->_object ) 
        {
            return false;
        }
        $attlist = new midcom_helper_imagepopup_attachmentlist_simple(&$this->_object);
        
        //$this->_request_data['images'] = $attlist->get_images();
        $this->_files = &$attlist->get_files(); 
        $this->_make_form();
        $this->_request_data['form'] =&  $this->_form;
        
        /* _process returns a boolean and sets _error.  */
        if ($this->_form->validate() ) 
        {
            $this->_process(); 
        }
        
        /*  add this here in case we have just added an extra image */
        $this->_form->addElement("header", null, "Submit");
        $this->_form->addElement(HTML_QuickForm::createElement('file', 'new_file', '', array()));
        $this->_form->addElement('submit', null, 'Upload new file', array('class' =>'upload'));
        
        /* use the object renderer */
        $renderer =& new HTML_QuickForm_Renderer_Object(true);        
        $this->_form->accept(&$renderer);
        $this->_request_data['renderer'] =& $renderer;
        $this->_request_data['error'] = $this->_error; 
        $this->_image_info .= "imagepopup_images['prefix'] = \"{$GLOBALS['midcom_config']['midcom_site_url']}midcom-serveattachmentguid-\";\n"; 
        
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . "/midcom.helper.imagepopup/functions.js");
        $_MIDCOM->add_jscript($this->_image_info);
        return true;
    }
    /**
     * If there has been a submitt, check what came inn and handle it.
     */
    function _process() 
    {
        $values = $this->_form->getSubmitValues(true);
        
        if (array_key_exists('new_file', $values) && file_exists($values['new_file']['tmp_name'])) 
        {
        
            return $this->_create_file($values['new_file']);
        }
        
        unset ($values['new_file']);
        
        foreach ($values as $attachment => $req) 
        {
            // the format is [guid][quid] = action.
            $action = "_attachment_" . $req[$attachment];
            if (method_exists($this, $action ))  
            {
                $this->{$action}($attachment);
            } 
            else 
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "You have tried to run an action that doesn't exist!");
            }
        }
        
    }
    
    /**
     * Delete an attachment
     * @param string attachment quid
     * @return boolean false if error
     */
    function _attachment_delete($guid) 
    {
        $att = new midcom_baseclasses_database_attachment($guid);
        if (!$att) 
        {
            // mostprobably a multiple submitt
            return true;
        }
        
        if (!$att->delete()) 
        {
            $this->_error ="Could not delete file {$att->name} because: " . mgd_errstr();
            return false;
        }
        
        $this->_form->removeElement($guid);
    }
    
    /**
     * A new attachment has been uploaded. This function creates the attachment.
     * @param array the information about the file (std php filename, tmp_name etc).
     */
    function _create_file($file)
    { 
        
        $attachment = $this->_object->create_attachment($file['name'], $file['name'], $file['type'] );
        if (! $attachment)
        {
            
            debug_push_class(__CLASS__, __FUNCTION__);
            $this->_error = "Failed to create attachment record, see above for details."; 
            debug_add($this->_error, MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
        
        $handle = @fopen($file['tmp_name'], 'r');
        
        if (! $handle)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            $this->_error = "Cannot add attachment, could not open {$file['tmp_name']} for reading."; 
            debug_add($this->_error, MIDCOM_LOG_INFO);
            if (isset($php_errormsg))
            {
                debug_add("Last PHP error was: {$php_errormsg}", MIDCOM_LOG_INFO);
            }
            debug_pop();
            return false;
        }
        $attachment->copy_from_handle($handle);
        $this->_set_image_size($attachment, $file['tmp_name']);
        
        return true;        
        
    }
    

    function _make_form() 
    {
        
        $attributes = array();
        
        $this->_form = new HTML_Quickform("midcom_helper_imagepopup", 'post', '','', array('class' => 'midcom_helper_imagepopup'));
        
        
        $this->_form->addElement("header", null, $this->_l10n->get("Attachments"));
        
        foreach ($this->_files as $key => $image)
        {
            $this->_add_file_to_form($key);
            
        }
        
        
    }
    /**
     * Add an extra file to the form.
     */
    function _add_file_to_form ($key)
    {
        $file = &$this->_files[$key];
        $elements[] =& HTML_QuickForm::createElement('static',$file->get_name() , '', $file->get_preview(), array('style' => 'float:left;'));
        
        $elements[] =& HTML_QuickForm::createElement('submit', 
                                                     $file->object->guid, 
                                                     $this->_l10n->get('delete'), 
                                                     array('class' => 'image_action')
                                                     );
                                                     
        $elements[] =& HTML_QuickForm::createElement('button', 
                                                     $file->object->guid , 
                                                     $this->_l10n->get('edit'), 
                                                     array('class' => 'image_action', 
                                                           'onclick' => "window.location ='{$this->_prefix}midcom-exec-midcom.helper.imagepopup/edit.php/{$file->object->guid}'"
                                                           )
                                                     );
        $this->_form->addGroup($elements, $file->object->guid , null, "&nbsp;");
        $this->_image_info .= $file->get_image_js_info();
    }
    
    /**
     * Remove a file from the l
     */

    /**
     * This is a simple helper which evaluates the imagesize information of a given
     * file and adds that information as parameters to the attachmend identified by
     * its identifier.
     *
     * @param object the attachment to update
     * @param resource the filename to read.
     */
    function _set_image_size($attachment, $filename)
    {
        $data = @getimagesize($filename);
        if ($data)
        {
            $attachment->parameter("midcom.helper.datamanager2.type.blobs", "size_x", $data[0]);
            $attachment->parameter("midcom.helper.datamanager2.type.blobs", "size_y", $data[1]);
            $attachment->parameter("midcom.helper.datamanager2.type.blobs", "size_line", $data[3]);
            if (! $attachment->mimetype)
            {
                switch ($data[2])
                {
                    case 1:
                        $attachment->mimetype = "image/gif";
                        $attachment->update();
                        break;

                    case 2:
                        $attachment->mimetype = "image/jpeg";
                        $attachment->update();
                        break;

                    case 3:
                        $attachment->mimetype = "image/png";
                        $attachment->update();
                        break;

                    case 6:
                        $attachment->mimetype = "image/bmp";
                        $attachment->update();
                        break;

                    case 7:
                    case 8:
                        $attachment->mimetype = "image/tiff";
                        $attachment->update();
                        break;
                }
            }
        }
    }
    


    function _show_list() {
        
        midcom_show_style("advanced");
    }
    
    
}
