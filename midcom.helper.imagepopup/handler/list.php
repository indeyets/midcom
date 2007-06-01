<?php
/**
 * Created on Mar 12, 2006
 * @author tarjei huse
 * @package midcom.helper.imagepopup
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/**
 * This handler shows the attachments attached to object $object.
 * @package midcom.helper.imagepopup
 */
class midcom_helper_imagepopup_handler_list extends midcom_baseclasses_components_handler
{
    /**
     * The datamanager controller
     * @var midcom_helper_datamanager2_controller_simple
     */
    var $_controller = null;
    
    function midcom_helper_imagepopup_handler_list()  
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    function _handler_list($handler_id, $args, &$data)
    {
        $_MIDCOM->cache->content->no_cache();
        $_MIDCOM->auth->require_valid_user();
        $_MIDCOM->skip_page_style = true;
        
        $_MIDCOM->add_link_head
        (
            array 
            ( 
                'rel'   => 'stylesheet',
                'type'  => 'text/css',
                'media' => 'screen',
                'href'  => MIDCOM_STATIC_URL ."/midcom.helper.imagepopup/styling.css",
            )
        );

        $data['schema_name'] = $args[0];
        $data['object'] = null;
        $data['folder'] = $this->_topic;
        
        if ($handler_id != '____ais-imagepopup-list_folder_noobject')
        {
            $data['object'] = $_MIDCOM->dbfactory->get_object_by_guid($args[1]);
            
            if (!$data['object']) 
            {
                return false;
            }
        }
        
        switch ($handler_id)
        {
            case '____ais-imagepopup-list_folder_noobject':
            case '____ais-imagepopup-list_folder':
                $data['list_type'] = 'folder';
                $data['list_title'] = $_MIDCOM->i18n->get_string('folder attachments', 'midcom.helper.imagepopup');
                break;
                
            case '____ais-imagepopup-list_object':
                $data['list_type'] = 'page';
                $data['list_title'] = $_MIDCOM->i18n->get_string('page attachments', 'midcom.helper.imagepopup');
                break;
        }
        
        // Run datamanager for handling the images
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb = $this->_load_schema($data['schema_name']);
        $this->_controller->schemaname = $data['schema_name']; 
        
        $_MIDCOM->set_pagetitle($data['list_title']);
        
        if ($data['list_type'] == 'page')
        {
            $this->_controller->set_storage($data['object']);
        }
        else
        {
            $this->_controller->set_storage($data['folder']);
        }
        
        $this->_controller->initialize();
        $this->_request_data['form'] = & $this->_controller;
        switch ($this->_controller->process_form())
        {
            case 'cancel':
                $_MIDCOM->add_jsonload("window.close();");
                break;
        }

        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/Pearified/JavaScript/Prototype/prototype.js");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . "/midcom.helper.imagepopup/functions.js");
        
        $image_info = "var imagepopup_images = new Array();\n";
        $image_info .= "imagepopup_images['prefix'] = \"{$_MIDGARD['self']}midcom-serveattachmentguid-\";\n"; 
        $_MIDCOM->add_jscript($image_info);
        
        //$_MIDCOM->add_jsonload("tinyMCEPopup.executeOnLoad('tinyMCEPopup.resizeToContent()');");
        $_MIDCOM->add_jsonload("imagePopupConvertImagesForAddition();imagePopupConvertFilesForAddition();");
        
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midcom.helper.imagepopup');
        
        return true;
    }
    
    function _show_list() 
    {    
        midcom_show_style('midcom_helper_imagepopup_init');
        midcom_show_style('midcom_helper_imagepopup_list');
        midcom_show_style('midcom_helper_imagepopup_finish');
    }
    
    /**
     * Loads and filters the schema from the session.
     */
    function _load_schema() 
    {
        if ($this->_request_data['object'])
        {
            $key = "{$this->_request_data['schema_name']}{$this->_request_data['object']->guid}";
        }
        else
        {
            $key = "{$this->_request_data['schema_name']}{$this->_request_data['folder']->guid}";
        }
        
        $session =& $_MIDCOM->get_service('session');
        
        if (!$session->exists('midcom.helper.datamanager2', $key))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not find datamanager2 schema session with key: {$key}.");
            // this exits.
        }
        
        $schema = $session->get('midcom.helper.datamanager2', $key);

        $imagetypes = array
        (
            'images'=> true,
            'image' => false,
        );
        
        foreach ($schema['fields'] as  $key => $field)
        {
            
            if (   array_key_exists($field['type'], $imagetypes)
                && $imagetypes[$field['type']] == true) 
            {
                // TODO: What should we do with the image fields in schema
            }
            else
            {
                // This schema field isn't an image field, remove from schema
                unset ($schema['fields'][$key]);
            }
            
        }
        if (count($schema['fields']) == 0 ) 
        {
            // No image fields natively in the schema, add one
            $schema['fields']['midcom_helper_imagepopup_images'] = Array
            (
                'title' => $_MIDCOM->i18n->get_string('images', 'midcom.helper.imagepopup'),
                'storage' => null,
                'type' => 'images',
                'widget' => 'images',
                'widget_config' => array (
                	'set_name_and_title_on_upload' => false
                	) ,
            );

            $schema['fields']['midcom_helper_imagepopup_files'] = Array
            (
                'title' => $_MIDCOM->i18n->get_string('files', 'midcom.helper.imagepopup'),
                'storage' => null,
                'type' => 'blobs',
                'widget' => 'downloads',
            );        
        }
        
        $schema_object = new midcom_helper_datamanager2_schema
        (
            array 
            (
                $this->_request_data['schema_name'] => $schema
            )
        );
        
        $schemadb = Array
        ( 
            $this->_request_data['schema_name'] => $schema_object
        );
        
        return $schemadb;        
    }    
}
