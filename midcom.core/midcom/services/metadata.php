<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:metadata.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Metadata service.
 *
 * This service utilizes MidCOM's metadata system to provide meaningful, auto-generated
 * meta and link tags into documents. It is also entry point site builders can use to
 * retrieve metadata about current page.
 *
 * @package midcom.services
 */
class midcom_services_metadata extends midcom_baseclasses_core_object
{
    /**
     * The metadata currently available. This array is indexed by context id; each
     * value consists of an flat array of two metadata objects, the first object being 
     * the Node metadata, the second View metadata. The metadata objects are created 
     * on-demand.
     *
     * @var Array
     * @access private
     */
    var $_metadata = Array();

    /**
     * Simple constructor, calls base class.
     */
    function midcom_services_metadata()
    {
        parent::midcom_baseclasses_core_object();
    }

    /**
     * Returns a reference to the node metadata of the specified context. The metadata
     * will be created if this is the first request.
     *
     * @param int $context_id The context to retrieve the node metadata for, this
     *     defaults to the current context.
     */
    function & get_node_metadata ($context_id = null)
    {
        if ($context_id === null)
        {
            $context_id = $_MIDCOM->get_current_context();
        }

        if (! array_key_exists($context_id, $this->_metadata))
        {
            $this->_create_metadata($context_id);
        }

        return $this->_metadata[$context_id][MIDCOM_METADATA_NODE];
    }

    /**
     * Returns a reference to the view metadata of the specified context. The metadata
     * will be created if this is the first request.
     *
     * @param int $context_id The context to retrieve the view metadata for, this
     *     defaults to the current context.
     */
    function & get_view_metadata ($context_id = null)
    {
        if ($context_id === null)
        {
            $context_id = $_MIDCOM->get_current_context();
        }

        if (! array_key_exists($context_id, $this->_metadata))
        {
            $this->_create_metadata($context_id);
        }

        return $this->_metadata[$context_id][MIDCOM_METADATA_VIEW];
    }

    /**
     * Creates the node and view metadata for a given context ID.
     *
     * @param int $context_id The context ID for whicht the metadata should be created.
     */
    function _create_metadata ($context_id)
    {
        if ($context_id === null)
        {
            $topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
        }
        else
        {
            $topic = $_MIDCOM->get_context_data($context_id, MIDCOM_CONTEXT_CONTENTTOPIC);
        }

        if (! is_a($topic, 'midcom_baseclasses_database_topic'))
        {
            // Force-Cast to DBA object
            $topic = new midcom_db_topic($topic->id);
        }
        $this->_metadata[$context_id] = Array();  
    
        $this->_metadata[$context_id][MIDCOM_METADATA_NODE] =& midcom_helper_metadata::retrieve($topic);
        $this->_metadata[$context_id][MIDCOM_METADATA_VIEW] = null;
    }
    
    /**
     * Binds view metadata to a DBA content object
     *
     * @param DBAObject $object The DBA class instance to bind to.
     */
    function bind_to(&$object)
    {
        $this->bind_metadata_to_object(MIDCOM_METADATA_VIEW, $object);
    }
    
    function bind_metadata_to_object($metadata_type, &$object, $context_id = null)
    {
        if ($context_id === null)
        {
            $context_id = $_MIDCOM->get_current_context();
        }
        
        $this->_metadata[$context_id][$metadata_type] =& midcom_helper_metadata::retrieve($object);
        
        // Update MidCOM 2.6 request metadata if appropriate
        $request_metadata = $_MIDCOM->get_26_request_metadata($context_id);
        if ($this->_metadata[$context_id][$metadata_type]->get('edited') > $request_metadata['lastmodified'])
        {
        }
    }
    
    /**
     * Populates appropriate metadata into XHTML documents based on metadata information
     * available to MidCOM for the request.
     */
    function populate_meta_head()
    {
        // Populate the 2.6 request metadata into view
        $request_metadata = $_MIDCOM->get_26_request_metadata();
        
        // HTML generator information
        $_MIDCOM->add_meta_head(
            array(
                'name' => 'generator',
                'content' => 'Midgard/'.mgd_version().' MidCOM/'.$GLOBALS['midcom_version'].' PHP/'.phpversion()
            )
        );
        
        // PermaLink into machine-detectable format
        $_MIDCOM->add_link_head(
            array(
                'rel' => 'bookmark',
                'href' => $request_metadata['permalink']
            )
        );

        // Last revision time for the entire page
        $_MIDCOM->add_meta_head(
            array(
                'name' => 'lastupdated',
                'content' => gmdate('Y-m-d H:i\Z', $request_metadata['lastmodified'])
            )
        );
        
        $view_metadata =& $this->get_view_metadata();
        if ($view_metadata)
        {
        }
    }
}
?>