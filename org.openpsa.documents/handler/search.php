<?php
/**
 * @package org.openpsa.documents
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: document_handler.php,v 1.13 2006/05/10 16:25:51 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.documents search handler and viewer class.
 *
 * @package org.openpsa.documents
 *
 */
class org_openpsa_documents_handler_search extends midcom_baseclasses_components_handler
{
    var $_datamanagers;

    function __construct()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
    $this->_datamanagers['document'] = new midcom_helper_datamanager($this->_config->get('schemadb_document'));
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
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

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_search($handler_id, &$data)
    {
    $this->_datamanagers['document'] = new midcom_helper_datamanager($this->_config->get('schemadb_document'));
        $displayed = 0;
        midcom_show_style('show-search-header');
        if (!empty($this->_request_data['results']))
        {
            midcom_show_style('show-search-results-header');
            foreach ($this->_request_data['results'] as $document)
            {
                // $obj->RI will contain either document or attachment GUID depending on match, ->source will always contain the document GUID
                $this->_request_data['document'] = $this->_document_handler->_load_document($document->source);
                if ($this->_request_data['document'])
                {
                    $this->_datamanagers['document']->init($this->_request_data['document']);
                    $this->_request_data['document_dm'] = $this->_datamanagers['document']->get_array();
                    $this->_request_data['document_search'] = $document;
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