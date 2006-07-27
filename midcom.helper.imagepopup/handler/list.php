<?php
/**
 * Created on Mar 12, 2006
 * @author tarjei huse
 * @package midcom.helper.imagepopup
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 * This handler shows the attachments attached to object $object.
 */
 
class midcom_helper_imagepopup_handler_list extends midcom_baseclasses_components_handler
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
    
    /**
     * Name of the schema to use
     * @var string
     */
    var $_schemaname;
    
    /**
     * The loaded schemaobject
     * @var object midcom_helper_datamanager2_schema
     */
    var $_schemadb = null;
    /**
     * The datamanager controller
     * @var midcom_helper_datamanager2_controller_simple
     */
    var $_controller = null;
    
    function midcom_helper_imagepopup_handler_list()  
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
        $style_attributes = array ( 
        	'rel'   =>  "stylesheet" ,
			'type'  =>  "text/css" ,
			'media' =>  "screen"
        );
        $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.helper.imagepopup/styling.css";
        $_MIDCOM->add_link_head( $style_attributes);
        
        $this->_request_data['error'] = '';

        //$this->_prefix = $GLOBALS['midcom_config']['midcom_site_url'];
        $this->_prefix = $_MIDGARD['self'];
    }
    
    function _handler_list($id, $args)
    {
		if (count($args) < 3) 
		{
			return false;
		}    
		
    	$this->_request_data['list_type'] = $id;
	    $this->_request_data['topic_guid'] = $args[0];
	    $this->_request_data['object_guid'] = $args[1];
	    $this->_request_data['schema_name'] = $args[2];
	    
    	if ($id == 'list_object')
    	{
			$this->_object = $_MIDCOM->dbfactory->get_object_by_guid($this->_request_data['object_guid']);
	        $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get('page attachments'));
        }
        elseif ($id == 'list_topic')
        {
        	$this->_object = new midcom_db_topic($this->_request_data['topic_guid']);        	
	        $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get('folder attachments'));  
        }
        
        if (!$this->_object ) 
        {
            return false;
        }
        
        $this->_schemaname = $this->_request_data['schema_name'];
        
        $this->_get_schema();
        
        switch ($this->_run_datamanager())
        {
            case 'cancel':
                $_MIDCOM->add_jsonload("window.close();");
                break;
        }
        
        $this->_image_info .= "imagepopup_images['prefix'] = \"{$this->_prefix}midcom-serveattachmentguid-\";\n"; 

        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/Pearified/JavaScript/Prototype/prototype.js");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . "/midcom.helper.imagepopup/functions.js");
        $_MIDCOM->add_jscript($this->_image_info);
        $_MIDCOM->add_jsonload("tinyMCEPopup.executeOnLoad('tinyMCEPopup.resizeToContent()');");
        $_MIDCOM->add_jsonload("imagePopupConvertImagesForAddition();imagePopupConvertFilesForAddition();");
        
        return true;
    }
    /**
     * starts and runs the datamanger
     */
    function _run_datamanager() 
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        //$schemas = array ( $this->_schemaname = & $this->_schema );
        $this->_controller->schemadb =& $this->_schemadb ;
        
        $this->_controller->schemaname = $this->_schemaname; 
        $this->_controller->set_storage($this->_object);
        //$this->_controller->defaults = $defaults;
        $this->_controller->initialize();
        $this->_request_data['form'] = & $this->_controller;
        return $this->_controller->process_form();
    }
    
    /**
     * Loads and filters the schema from the session.
     */
    function _get_schema() 
    {
        $key = $this->_schemaname . $this->_request_data['object_guid'];
        $session =& $_MIDCOM->get_service('session');
        
        if (!$session->exists('midcom.helper.datamanager2',$key))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,"Could not find session with key: {$key}. Quitting.");
            // this exits.
        }
        
        $schema = $session->get('midcom.helper.datamanager2',$key);
        //var_dump($schema);
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
                'title' => $this->_request_data['l10n']->get('images'),
                'storage' => null,
                'type' => 'images',
                'widget' => 'images',
            );

            $schema['fields']['midcom_helper_imagepopup_files'] = Array
            (
                'title' => $this->_request_data['l10n']->get('files'),
                'storage' => null,
                'type' => 'blobs',
                'widget' => 'downloads',
            );        
        }
        
        $schema_o = new midcom_helper_datamanager2_schema(array ($this->_schemaname => $schema));
        $this->_schemadb = Array ( $this->_schemaname => $schema_o);
        
        return;        
    }
    
    /**
     * Add an extra file to the form.
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
    }*/
       
    function _show_list() {
        
        midcom_show_style("list");
    }
    
    
}
