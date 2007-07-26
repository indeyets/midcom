<?php

/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:midcom.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a base class which is targeted at MidCOM content object indexing. It should 
 * be used whenever MidCOM documents are indexed, either directly or as a base class.
 * 
 * It will take an arbitrary Midgard Object, for which Metadata must be available.
 * The document class will then load the metadata information out of the database
 * and populate all metadata fields of the document from there. 
 * 
 * If you want to index datamanager driven objects, you should instead look at
 * the class midcom_services_indexer_document_datamanager.
 * 
 * The GUID of the object being referred is used as a RI.
 * 
 * The documents type is "midcom".
 * 
 * @package midcom.services
 * @see midcom_services_indexer
 * @see midcom_helper_metadata
 */

class midcom_services_indexer_document_midcom extends midcom_services_indexer_document
{
    /**
     * The metadata instance attached to the object to be indexed.
     * 
     * @access protected
     * @var midcom_helper_metadata
     */
    var $_metadata = null;
    
    
    /**
     * The constructor initializes the content object, loads the metadata object
     * and populates the metadata fields accordingly.
     * 
     * The source memeber is automatically populated with the GUID of the document,
     * the RI is set to it as well. The URL is set to a on-site permalink. 
     * 
     * @param mixed $object The content object to load, passed to the metadata constructor.
     * @see midcom_helper_metadata
     */
    function midcom_services_indexer_document_midcom($object)
    {
        parent::midcom_services_indexer_document();
        
        $this->_set_type('midcom');
        
        if (is_a($object, 'midcom_helper_metadata'))
        {
            $this->_metadata =& $object;
        }
        else
        {
	        $this->_metadata =& midcom_helper_metadata::retrieve($object);
	        if ($this->_metadata == false)
	        {
	            debug_add('document_midcom: Failed to retrieve a Metadata object, aborting.');
	            return false;
	        }
        }
        
        $this->source = $this->_metadata->object->guid();
        $this->RI = $this->source;
        $this->document_url = $_MIDCOM->permalinks->create_permalink($this->source);
        
        $this->_process_metadata();
        $this->_process_topic();
    }
    
    
    /**
     * Processes the information contained in the metadata instance.
     */
    function _process_metadata()
    {
        /*
        FIXME: Convert to utilize DM2
        $datamanager =& $this->_metadata->get_datamanager();
        foreach ($datamanager->data as $key => $value)
        {
            switch ($key)
            {
                case 'created':
                	$this->created = $this->_metadata->get('created');
                    break;
                
                case 'creator':
                	$this->creator = $this->_metadata->get('creator');
                    break;
                    
                case 'edited':
                    $this->edited = $this->_metadata->get('edited');
                    break;
                    
                case 'editor':
                    $this->editor = $this->_metadata->get('editor');
                    break;
                    
                case '_schema':
                case '_storage_type':
                case '_storage_id':
                case '_storage_guid':
                    break;
                    
                default:
                    $this->add_text("META_{$key}", $this->datamanager_get_text_representation($this->_metadata->_datamanager, $key));
                    break; 
            }
        }
        $this->_metadata->release_datamanager();
        */
    }
    
    /**
     * Tries to determine the topic GUID and component, we use NAPs 
     * reverse-lookup capabilities.
     */
    function _process_topic()
    {
        $nav = new midcom_helper_nav();
        $object = $nav->resolve_guid($this->RI);
        if (! $object)
        {
            debug_add("Failed to resolve the topic, skipping autodetection.");
            return;
        }
        if ($object[MIDCOM_NAV_TYPE] == 'leaf')
        {
            $object = $nav->get_node($object[MIDCOM_NAV_NODEID]);
        }
        $this->topic_guid = $object[MIDCOM_NAV_GUID];
        $this->topic_url = $object[MIDCOM_NAV_FULLURL];
        $this->component = $object[MIDCOM_NAV_COMPONENT];
    }
    
}

?>