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
     * Class of the current page per each context. 
     * Typically these are thesame as the schema name of the current object's Datamanager schema. 
     * This can be used for changing site styling based on body class="" etc.
     *
     * @var Array
     * @access private
     */
    var $_page_classes = Array();

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
     * @param int $context_id The context ID for which the metadata should be created.
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
     * Sets the class of the current page for a context
     *
     * @param string $page_class The class that should be used for the page
     * @param int $context_id The context ID for which the page class should be set.
     */
    function set_page_class($page_class, $context_id = null)
    {
        if ($context_id === null)
        {
            $context_id = $_MIDCOM->get_current_context();
        }
        
        // Append current topic to page class
        $page_class .= ' ' . str_replace('.', '_', $_MIDCOM->get_context_data(MIDCOM_CONTEXT_COMPONENT));
        
        // Append a custom class from topic to page class
        $topic_class = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC)->get_parameter('midcom.services.metadata', 'page_class');
        if (!empty($topic_class))
        {
            $page_class .= " {$topic_class}";
        }

        $this->_page_classes[$context_id] = $page_class;
    }

    /**
     * Gets the class of the current page of a context
     *
     * @param int $context_id The context ID for the page class.
     * @return string The page class
     */
    function get_page_class($context_id = null)
    {
        if ($context_id === null)
        {
            $context_id = $_MIDCOM->get_current_context();
        }

        if (array_key_exists($context_id, $this->_page_classes))
        {
            return $this->_page_classes[$context_id];
        }
        else
        {
            return 'default';
        }
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
    
    /*
     * Binds object to given metadata type.
     */
    function bind_metadata_to_object($metadata_type, &$object, $context_id = null)
    {
        if ($context_id === null)
        {
            $context_id = $_MIDCOM->get_current_context();
        }
        
        $this->_metadata[$context_id][$metadata_type] =& midcom_helper_metadata::retrieve($object);
        if (!$this->_metadata[$context_id][$metadata_type])
        {
            return;
        }
                
        // Update MidCOM 2.6 request metadata if appropriate
        $request_metadata = $_MIDCOM->get_26_request_metadata($context_id);
        $edited = $this->_metadata[$context_id][$metadata_type]->get('edited');
        if ($edited > $request_metadata['lastmodified'])
        {
            $_MIDCOM->set_26_request_metadata($edited, $request_metadata['permalinkguid']);
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
                'content' => 'Midgard/' . mgd_version() . ' MidCOM/' . $GLOBALS['midcom_version'] . ' PHP/' . phpversion()
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
                'content' => @gmdate('Y-m-d H:i:s\Z', $request_metadata['lastmodified'])
            )
        );
        
        // If an object has been bound we have more information available
        $view_metadata =& $this->get_view_metadata();
        if ($view_metadata)
        {
            // TODO: Add support for tags here
            $keywords = $view_metadata->get('keywords');
            if ($keywords != '')
            {
                $_MIDCOM->add_meta_head(
                    array(
                        'name' => 'keywords',
                        'content' => $keywords
                    )
                );
            }
            
            // Description
            $description = $view_metadata->get('description');
            if ($description != '')
            {
                $_MIDCOM->add_meta_head(
                    array(
                        'name' => 'dc.description',
                        'content' => $description
                    )
                );
            }
            
            // Creation date
            $_MIDCOM->add_meta_head(
                array(
                    'name' => 'dc.date',
                    'content' => gmdate('Y-m-d', (int) $view_metadata->get('published'))
                )
            );
            
            if ($GLOBALS['midcom_config']['positioning_enable'])
            {
                if (!class_exists('org_routamc_positioning_object'))
                {
                    // Load the positioning library
                    $_MIDCOM->load_library('org.routamc.positioning');
                }

                // Display position metadata
                $object_position = new org_routamc_positioning_object($view_metadata->object);
                $object_position->set_metadata();
            } 
            
            // Display links to language versions
            $translations = $view_metadata->get_languages();
            foreach ($translations as $translation)
            {
                if ($translation['host']->id == $_MIDGARD['host'])
                {
                    // This is the host we're in, no need to link
                    continue;
                }
                
                $_MIDCOM->add_link_head
                (
                    array
                    (
                        'rel'   => 'alternate',
                        'type'  => 'text/html',
                        'title' => "In {$translation['name']}",
                        'hreflang' => $translation['code'],
                        'href'  => "{$translation['url']}midcom-permalink-{$view_metadata->object->guid}",
                    )
                );
            }
        }
    }
}
?>