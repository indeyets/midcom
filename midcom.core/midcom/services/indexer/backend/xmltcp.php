<?php

/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:xmltcp.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** XML Communitcation driver library */
require_once 'XMLCommClient.php';

/**
 * XMLComm implementation using a TCP/IP interface.
 * 
 * ...
 * 
 * @abstract Abstract indexer backend class
 * @package midcom.services
 * @see midcom_services_indexer
 * @see midcom_services_indexer_backend
 * @see midcom_services_indexer_XMLComm_RequestWriter
 * @see midcom_services_indexer_XMLComm_ResponseParser
 * 
 * @todo Check if there is a better way to handle the exec loop, which looks rather PHP-workaroundy right now.
 */

class midcom_services_indexer_backend_xmltcp extends midcom_services_indexer_backend
{
    /**
     * The request to execute.
     * 
     * @access private
     * @var midcom_services_indexer_XMLComm_RequestWriter
     */
    var $_request = null;
    
    /**
     * The response received.
     * 
     * @access private
     * @var midcom_services_indexer_XMLComm_ResponseParser
     */
    var $_response = null;
    
    
    /**
     * Constructor is empty at this time.
     */
    function midcom_services_indexer_backend_xmltcp ()
    {
        parent::midcom_services_indexer_backend();
        // Nothing to do yet.
    }
    
    /**
     * Adds a document to the index.
     * 
     * Any warning will be treated as error.
     * 
     * Note, that $document may also be an array of documents without further
     * changes to this backend.
     * 
     * @param Array $documents A list of midcom_services_indexer_document objects.
     * @return boolean Indicating success.
     */   
    function index ($documents)
    {
        $this->_request = new midcom_services_indexer_XMLComm_RequestWriter();
        $this->_request->add_index(0, $documents);
        if (! $this->_exec())
        {
            return false;
        }
        return (! array_key_exists(0, $this->_response->warnings));
    }
    
    /**
     * Removes the document with the given resource identifier from the index.
     * 
     * @param string $RI The resource identifier of the document that should be deleted.
     * @return boolean Indicating success.
     */
    function delete ($RI)
    {
        $this->_request = new midcom_services_indexer_XMLComm_RequestWriter();
        $this->_request->add_delete(0, $RI);
        if (! $this->_exec())
        {
            return false;
        }
        return (! array_key_exists(0, $this->_response->warnings));
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
        $this->_request = new midcom_services_indexer_XMLComm_RequestWriter();
        $this->_request->add_deleteall(0);
        if (! $this->_exec())
        {
            return false;
        }
        return (! array_key_exists(0, $this->_response->warnings));
    }
    
    /**
     * Query the index and, if set, restrict the query by a given filter.
     * 
     * ...
     * 
     * @param string $query The query, which must suite the backends query syntax.
     * @param midcom_services_indexer_filter $filter An optional filter used to restrict the query. This may be null indicating no filter.
     * @return Array An array of documents matching the query, or false on a failure.
     */
    function query ($query, $filter)
    {
        $this->_request = new midcom_services_indexer_XMLComm_RequestWriter();
        $this->_request->add_query(0, $query, $filter);
        if (   ! $this->_exec()
            || array_key_exists(0, $this->_response->warnings))
        {
            return false;
        }
        if (   ! is_array($this->_response->resultsets)
            || count($this->_response->resultsets) == 0 )
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Warning, the resultsets returned by the query are invalid.', MIDCOM_LOG_ERROR);
            debug_print_r('Got this response:', $this->_response);
            debug_pop();
            return false;
        }
        return $this->_response->resultsets[0];
    }
    
    
    /**
     * This private helper will create a socket connection to the indexer daemon and
     * execute the query.
     * 
     * Note, that both classes call generate_error on critical errors.
     * @return boolean Indicating success of execution, does not indicate indexer-reported
     *     errors or warnings.
     */
    function _exec ()
    {
        debug_push_class($this, 'exec');
        
        $errstr = '';
        $errcode = 0;
        
        $socket = @fsockopen($GLOBALS['midcom_config']['indexer_xmltcp_host'],
            $GLOBALS['midcom_config']['indexer_xmltcp_port'],
            $errstr, $errcode);
        if (! $socket)
        {
            debug_add("Failed to establish a connection to the indexer: {$errstr} ({$errcode})", MIDCOM_LOG_CRIT);
            debug_pop();
            return false;
            // This will exit
        }
        $xml = $this->_request->get_xml(true);
        debug_print_r('Will send this request:', $xml);
        fwrite($socket, $xml);
        $response = '';
        while (! feof($socket))
        {
            $response .= fread($socket, 4096);
        }
        fclose($socket);
        debug_print_r('We got this response:', $response);
        
        $this->_response = new midcom_services_indexer_XMLComm_ResponseReader();
        $this->_response->parse($response);
        foreach ($this->_response->warnings as $id => $warning)
        {
            debug_add("Failed to execute Request {$id}: {$warning}", MIDCOM_LOG_WARN); 
        }
        debug_pop();
        return true;
    }
    
    
    
}

?>