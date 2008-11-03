<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:indexer.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This class is the main access points into the MidCOM Indexer subsystem.
 *
 * It allows you to maintain and query the MidCOM document index.
 *
 * Do not instantiate this class directly. Instead use the get_service
 * method on midcom_application using the service name 'indexer' to obtain
 * a running instance. You <i>must</i> honor the reference of that call.
 *
 *
 *
 * @package midcom.services
 * @see midcom_services_indexer_document
 * @see midcom_services_indexer_backend
 * @see midcom_services_indexer_filter
 *
 * @todo Batch indexing support
 * @todo Write code examples
 * @todo More elaborate class introduction.
 */

class midcom_services_indexer
{
    /**
     * The backend indexer implementation
     *
     * @access private
     * @var midcom_services_indexer_backend
     */
    var $_backend = null;

    /**
     * Flag for disabled indexing, set by the constructor.
     *
     * @access private
     * @var boolean
     */
    var $_disabled = false;

    /**
     * Initialization
     *
     * The constructor will initialize the indexer backend using the MidCOM
     * configuration by default. If you need a different indexer backend, you
     * can always explicitly instantiate a backend and pass it to the
     * constructor. In that case you have to load the corresponding PHP file
     * manually.
     *
     * @param midcom_services_indexer_backend $backend An explicit indexer to initialize with.
     */
    function __construct($backend = null)
    {
        if ($GLOBALS['midcom_config']['indexer_backend'] == false)
        {
            $this->_disabled = true;
            return;
        }

        if (is_null($backend))
        {
            $class = "midcom_services_indexer_backend_{$GLOBALS['midcom_config']['indexer_backend']}";
            $this->_backend = new $class();
        }
        else
        {
            $this->_backend = $backend;
        }
    }

    /**
     * Simple helper, returns true if the indexer service is online, false if it is disabled.
     *
     * @return boolean Service state.
     */
    function enabled()
    {
        return ! $this->_disabled;
    }

    /**
     * Adds a document to the index.
     *
     * A finished document object must be passed to this object. If the index
     * already contains a record with the same Resource Identifier, the record
     * is replaced.
     *
     * Support of batch-indexing using an Array of documents instead of a single
     * document is possible (and strongly advised for performance reasons).
     *
     *
     *
     * @param mixed $documents One or more documents to be indexed, so this is either a
     *           midcom_services_indexer_document or an Array of these objects.
     * @return boolean Indicating success.
     */
    function index ($documents)
    {
        if ($this->_disabled)
        {
            return true;
        }

        if (! is_array($documents))
        {
            $documents = Array($documents);
        }
        if (count($documents) == 0)
        {
            // Nothing to do.
            return true;
        }

        foreach ($documents as $key => $value)
        {
            // We don't have a document. Try auto-cast to a suitable document.
            // arg to _cast_to_document is passed by-reference.
            if (! is_a($documents[$key], 'midcom_services_indexer_document'))
            {
                if (! $this->_index_cast_to_document($documents[$key]))
                {
                    debug_push_class(__CLASS__, __FILE__);
                    debug_add("Encountered an unsupported argument while processing the document {$key}, aborting. See the debug messages for details.",
                        MIDCOM_LOG_ERROR);
                    debug_print_r("The document at type {$key} is invalid:", $document[$key]);
                    debug_pop();
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Encountered an unsupported argument while processing the document {$key}");
                }
            }

            $documents[$key]->members_to_fields();
        }
        return $this->_backend->index($documents);
    }

    /**
     * Automatic helper which transforms a reference-passed object into an indexable document.
     * Where necessary (f.x. with the DM instances) automatic indexing of subclasses is done.
     *
     * Currently supported arguments:
     *
     * - Datamangager 1 Instances (midcom_helper_datamanager)
     *
     * Note, that this is conceptually different from the public new_document operation: It might
     * already trigger indexing of dependant objects: A Datamanager 1 instance for example will
     * automatically reindex all BLOBs defined in the schema.
     *
     * @param object &$object A reference to the supported object types which allow for automatic
     *     casting (see above).
     * @return boolean Indicating success.
     * @access protected
     */
    function _index_cast_to_document(&$object)
    {
        if (is_a($object, 'midcom_helper_datamanager'))
        {
            // Make a copy, as the document is created by-reference and this might make problems during the iterations.
            $datamanager = $object;
            $datamanager->reindex_autoindex_blobs();
            $object = $this->new_document($datamanager);
            $datamanager->destroy();
            return true;
        }

        if (is_a($object, 'midcom_helper_datamanager2_datamanager'))
        {
            $object = $this->new_document($object);
            return true;
        }


        return false;
    }


    /**
     * Removes the document with the given resource identifier from the index.
     *
     * @param string $RI The resource identifier of the document that should be deleted.
     * @return boolean Indicating success.
     */
    function delete ($RI)
    {
        if ($this->_disabled)
        {
            return true;
        }

        return $this->_backend->delete($RI);
    }

    /**
     * Clear the index completely.
     *
     * This will drop the current index.
     *
     * @return boolean Indicating success.
     */
    function delete_all()
    {
        if ($this->_disabled)
        {
            return true;
        }
        return $this->_backend->delete_all();
    }

    /**
     * Query the index and, if set, restrict the query by a given filter.
     *
     * The filter argument is optional and may be a subclass of indexer_filter.
     * The backend determines what filters are supported and how they are
     * treated.
     *
     * The query syntax is also dependant on the backend. Refer to its documentation
     * how queries should be built.
     *
     * @param string $query The query, which must suite the backends query syntax. It is assumed to be in the site charset.
     * @param midcom_services_indexer_filter $filter An optional filter used to restrict the query.
     * @return Array An array of documents matching the query, or false on a failure.
     * @todo Refactor into multiple methods
     */
    function query($query, $filter = null)
    {
        if ($this->_disabled)
        {
            return false;
        }

        global $midcom_config;

        // Do charset translations
        $i18n =& $_MIDCOM->get_service('i18n');
        $query = $i18n->convert_to_utf8($query);

        $nav = new midcom_helper_nav();
        $result_raw = $this->_backend->query($query, $filter);
        if ($result_raw === false)
        {
            debug_add("Failed to execute the query, aborting.", MIDCOM_LOG_INFO);
            return false;
        }
        $result = Array();
        foreach ($result_raw as $document)
        {
            $document->fields_to_members();

            // Permission checks
            debug_add("Doing Permission and Visibility Checks for {$document->title}");

            // midgard:read verification, we simply try to create an object instance
            // In the case, we distinguish between MidCOM documents, where we can check
            // the RI identified object directly, and pure documents, where we use the
            // topic instead.
            $topic = new midcom_db_topic($document->topic_guid);
            if (! $topic)
            {
                // Skip document, the object is hidden.
                debug_add("Skipping the generic document {$document->title}, its topic seems to be invisible, we cannot proceed.");
                continue;
            }

            // this checks acls!
            if ($document->is_a('midcom'))
            {
                // Try to retrieve object:
                // Strip language code from end of RI if it looks like "<GUID>_<LANG>" (because *many* places suppose it's plain GUID)
                $object = $_MIDCOM->dbfactory->get_object_by_guid(preg_replace('/^([0-9a-f]{32,80})_[a-z]{2}$/', '\\1', $document->RI));
                if (   !$object
                    || !$object->guid)
                {
                    // Skip document, the object is hidden.
                    debug_add("Skipping the MidCOM document {$document->title} because it is not viewable");
                    continue;
                }
            }
            $result[] = $document;
        }
        return $result;
    }

    /**
     * This function tries to instantiate the most specific document class
     * for the object given in the parameter.
     *
     * This class will not return empty document base class instances if nothing
     * specific can be found. If you are in this situation, you need to instantiate
     * an appropriate document manually and populate it.
     *
     * The checking sequence is like this right now:
     *
     * 1. If a datamanager instance is passed, it is transformed into a midcom_services_indexer_document_datamanager.
     * 2. If a Metadata object is passed, it is transformed into a midcom_services_indexer_document_midcom.
     * 3. Next, the method tries to retrieve a MidCOM Metadata object using the parameter directly. If successful,
     *    again, a midcom_services_indexer_document_midcom is returned.
     *
     * This factory method will work even if the indexer is disabled. You can check this
     * with the enabled() method of this class.
     *
     * @todo Move to a full factory pattern here to save document php file parsings where possible.
     *     This means that all document creations will in the future be handled by this method.
     *
     * @param object &$object The object for which a document instance is required, passed by reference.
     * @return midcom_services_indexer_document A valid document class as specific as possible. Returns
     *     false on error or if no specific class match could be found.
     */
    function new_document(&$object)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_type('Searching an instance for this object type:', $object);

        // Scan for datamanager instances.
        if (is_a($object, 'midcom_helper_datamanager'))
        {
            debug_add('This is a document_datamanager');
            debug_pop();
            return new midcom_services_indexer_document_datamanager($object);
        }
        if (is_a($object, 'midcom_helper_datamanager2_datamanager'))
        {
            debug_add('This is a document_datamanager2');
            debug_pop();
            return new midcom_services_indexer_document_datamanager2($object);
        }

        // Maybe we have a metadata object...
        if (is_a($object, 'midcom_helper_metadata'))
        {
            debug_add('This is a metadata document, built from a metadata object.');
            debug_pop();
            return new midcom_services_indexer_document_midcom($object);
        }

        // Try to get a metadata object for the argument passed
        // This should catch all DBA objects as well.
        $metadata =& midcom_helper_metadata::retrieve($object);
        if ($metadata)
        {
            debug_add('Successfully fetched a Metadata object for the argument.');
            debug_pop();
            return new midcom_services_indexer_document_midcom($metadata);
        }

        // No specific match found.
        debug_add('No match found for this type.');
        debug_pop();
        return false;
    }





}

?>