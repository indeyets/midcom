<?php
/**
 * @package org.openpsa.documents
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: viewer.php,v 1.14 2006/05/10 16:25:51 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.documents site interface class.
 *
 * Document management and WebDAV file share
 */
class org_openpsa_documents_viewer extends midcom_baseclasses_components_request
{

    var $_datamanagers = array();
    var $_directory_handler = null;
    var $_metadata_handler = null;

    /**
     * Constructor.
     */
    function org_openpsa_documents_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);

        // Load datamanagers for main classes
        $this->_initialize_datamanager('directory', $this->_config->get('schemadb_directory'));
        $this->_initialize_datamanager('metadata', $this->_config->get('schemadb_metadata'));

        // Pass topic to handlers
        $this->_request_data['directory'] = new org_openpsa_documents_directory($this->_topic->id);
        $this->_request_data['enable_versioning'] = $this->_config->get('enable_versioning');

        // Load handler classes
        $this->_metadata_handler = new org_openpsa_documents_metadata_handler(&$this->_datamanagers, &$this->_request_data);
        $this->_directory_handler = new org_openpsa_documents_directory_handler(&$this->_datamanagers, &$this->_request_data);

        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();

        // Match /document_metadata/new/choosefolder
        $this->_request_switch['metadata_new_choosefolder'] = array(
            'fixed_args' => Array('document_metadata','new','choosefolder'),
            'handler' => array(&$this->_metadata_handler,'metadata_new'),
        );

        // Match /document_metadata/<document GUID>/action
        $this->_request_switch['metadata_action'] = array(
            'fixed_args' => 'document_metadata',
            'variable_args' => 2,
            'handler' => array(&$this->_metadata_handler,'metadata_action'),
        );

        // Match /document_metadata/new
        $this->_request_switch['metadata_new'] = array(
            'fixed_args' => Array('document_metadata','new'),
            'handler' => array(&$this->_metadata_handler,'metadata_new'),
        );

        // Match /document_metadata/<document GUID>
        $this->_request_switch[] = array(
            'fixed_args' => 'document_metadata',
            'variable_args' => 1,
            'handler' => array(&$this->_metadata_handler,'metadata'),
        );

        // Match /edit
        $this->_request_switch[] = array(
            'fixed_args' => 'edit',
            'handler' => array(&$this->_directory_handler,'directory_edit'),
        );

        // Match /new
        $this->_request_switch[] = array(
            'fixed_args' => 'new',
            'handler' => array(&$this->_directory_handler,'directory_new'),
        );

        // Match /search
        $this->_request_switch[] = array(
            'fixed_args' => 'search',
            'handler' => 'search'
        );

        /**
        * URL method disabled until MidCOM bug #235 is fixed
        // Match /filename
        $this->_request_switch[] = array(
            'variable_args' => 1,
            'handler' => 'attachment'
        );
        */

        // Match /
        $this->_request_switch[] = array(
            'handler' => array(&$this->_directory_handler,'directory'),
        );

        // This component uses the PEAR HTML_TreeMenu package, include the handler javascripts
        // TODO: State this AIS dependency somehow?
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/midcom.admin.content/TreeMenu.js");

    }

    function _initialize_datamanager($type, $schemadb_snippet)
    {
        // Load schema database snippet or file
        debug_add("Loading Schema Database", MIDCOM_LOG_DEBUG);
        $schemadb_contents = midcom_get_snippet_content($schemadb_snippet);
        eval("\$schemadb = Array ( {$schemadb_contents} );");
        // Initialize the datamanager with the schema
        $this->_datamanagers[$type] = new midcom_helper_datamanager($schemadb);

        if (!$this->_datamanagers[$type]) {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Datamanager could not be instantiated.");
            // This will exit.
        }
    }

    function _handler_attachment($handler_id, $args, &$data)
    {
        // This hook is for direct PUT and GET of files
        return false;
    }

    function _handler_search($handler_id, $args, &$data)
    {
        $this->_request_data['results'] = array();
        if (array_key_exists('search', $_GET))
        {
            // Figure out where we are
            $nap = new midcom_helper_nav();
            $node = $nap->get_node($nap->get_current_node());

            // Instantiate indexer
            $indexer =& $_MIDCOM->get_service('indexer');

            // Add the search parameters
            $query = $_GET['search'];
            $query .= " AND __TOPIC_URL:\"{$node[MIDCOM_NAV_FULLURL]}*\"";
            $query .= " AND __COMPONENT:org.openpsa.documents";
            // TODO: Metadata support

            // Run the search
            $this->_request_data['results'] = $indexer->query($query, null);
        }
        return true;
    }

    function _show_search($handler_id, &$data)
    {
        $displayed = 0;
        midcom_show_style('show-search-header');
        if (count($this->_request_data['results']))
        {
            midcom_show_style('show-search-results-header');
            foreach ($this->_request_data['results'] as $document)
            {
                // $obj->RI will contain either document or attachement GUID depending on match, ->source will always contain the document GUID
                $this->_request_data['metadata'] = $this->_metadata_handler->_load_metadata($document->source);
                if ($this->_request_data['metadata'])
                {
                    $this->_datamanagers['metadata']->init($this->_request_data['metadata']);
                    $this->_request_data['metadata_dm'] = $this->_datamanagers['metadata']->get_array();
                    $this->_request_data['metadata_search'] = $document;
                    midcom_show_style('show-search-results-item');
                    $displayed++;
                }
            }
            midcom_show_style('show-search-results-footer');
        }
        if ($displayed == 0)
        {
            midcom_show_style('show-search-noresults');
        }
        midcom_show_style('show-search-footer');
    }

}
?>