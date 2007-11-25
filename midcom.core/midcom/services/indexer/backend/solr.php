<?php

require_once 'HTTP/Request.php';
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:xmltcp.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */


/**
 * Solr implementation of the indexer backend. This works by communicating with solr
 * over http requests. It uses the same tcphost and tcpport settings as the old TCP indexer used.
 * 
 * @package midcom.services
 * @see midcom_services_indexer
 * @see midcom_services_indexer_backend
 * @see midcom_services_indexer_XMLComm_RequestWriter
 * @see midcom_services_indexer_XMLComm_ResponseParser
 * 
 */

class midcom_services_indexer_backend_solr extends midcom_services_indexer_backend
{
   
    /**
     * The xml factory class
     * @var object midcom_services_indexer_solrDocumentFactory
     */
    private $factory = null;
    /**
     * The http_request wrapper 
     * @var object midcom_services_indexer_solrRequest
     */
    private $request = null; 
    /**
     * Constructor is empty at this time.
     */
    function __construct()
    {
        parent::midcom_services_indexer_backend();
        // Nothing to do yet.
        //
        $this->factory = new midcom_services_indexer_solrDocumentFactory;
        $this->request = new midcom_services_indexer_solrRequest($this->factory);
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
     * @return bool Indicating success.
     */   
    function index ($documents)
    {
        $this->factory->reset();
        if (!is_array($documents)) 
        {
            $documents = array( $documents );
        }

        foreach ($documents as $document ) 
        {
            $this->factory->add($document);
        }
        
        return $this->request->execute();
    }
    
    /**
     * Removes the document with the given resource identifier from the index.
     * 
     * @param string $RI The resource identifier of the document that should be deleted.
     * @return bool Indicating success.
     */
    function delete ($RI)
    {
        $this->factory->delete($RI);
        return $this->request->execute();

    }
    
    /**
     * Clear the index completely.
     * This will drop the current index.
     * NB: It is probably better to just stop the indexer and delete the data/index directory!
     * @return bool Indicating success.
     */
    function delete_all()
    {
        $this->factory->delete_all();
        return $this->request->execute();
    }
    
    /**
     * Query the index and, if set, restrict the query by a given filter.
     * @param string $query The query, which must suite the backends query syntax.
     * @param midcom_services_indexer_filter $filter An optional filter used to restrict the query. This may be null indicating no filter.
     * @return Array An array of documents matching the query, or false on a failure.
     */
    function query ($query, $filter)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if ($filter !== null) 
        {
            if ($filter->type == 'datefilter') 
            {
                $format = "Y-m-dTH:i:s"  ; //1995-12-31T23:59:59Z
                $query .= sprintf(" AND %s:[%s TO %s]", 
                                    $filter->get_field(), 
                                    gmdate($format, $filter->get_start()) . "Z", 
                                    gmdate($format, ($filter->get_end() == 0 ) ? time() : $filter->get_end()) . "Z");
            }
        }
        /* In fact this is probably best left for midcom.helper.search to decide
        if ($GLOBALS['midcom_config']['i18n_multilang_strict'])
        {
            $query .= ' AND (__LANG:"' . $_MIDCOM->i18n->get_current_language() . '" OR __LANG:"")';
        }
        */

        // TODO: Make this configurable, even better: adapt the whole indexer system to fetching enable querying for counts and slices
        $maxrows = 1000;
        $url = "http://{$GLOBALS['midcom_config']['indexer_xmltcp_host']}:{$GLOBALS['midcom_config']['indexer_xmltcp_port']}/solr/select?q={$query}&fl=*,score&rows={$maxrows}";
        if (isset($_REQUEST['debug'])) var_dump($url);

        $options = array();
        $options['method'] = HTTP_REQUEST_METHOD_GET ;

        $request = new HTTP_Request($url, $options);
        $request->addHeader('Accept-Charset', 'UTF-8');
        $request->addHeader('Content-type', 'text/xml; charset=utf-8');

        $err = $request->sendRequest(true);
        $this->code = $request->getResponseCode();
        if ($this->code != 200 || PEAR :: isError($err)) {
            $msg = (is_object($err)) ? $err->getMessage() : "";
            debug_add("Failed to execute Request {$url}:{$this->code} {$msg}", MIDCOM_LOG_WARN); 
            debug_pop();
            return false;
        }
        $body = $request->getResponseBody();
        debug_add("Got response\n===\n{$body}\n===\n");
        $response = DomDocument::loadXML($body);
        $xquery = new DomXPath($response);
        $result = array();

        $num = $xquery->query('/response/result')->item(0);
        if ($num->getAttribute('numFound') == 0) {
            return array();
        }

        foreach ($xquery->query('/response/result/doc') as $res)
        {
    	    $doc = new midcom_services_indexer_document();
            foreach ($res->childNodes as $str) {
                $name = $str->getAttribute('name');
                
                $doc->add_result($name,($str->tagName == 'float') ? (float) $str->nodeValue : (string) $str->nodeValue  ) ;
                if ($name == 'RI') {
                    $doc->add_result('__RI', $str->nodeValue);
                }
                if ($name == 'score' && $filter == null) {
                    $doc->score = (float) $str->nodeValue;
                }
                
            }
            $result[] = $doc;
        }
        debug_add(sprintf('Returning %d results', count($result)), MIDCOM_LOG_INFO);
        debug_pop();
        return $result;
    }
}

/**
 * This class provides methods to make XML for the different solr xml requests. 
 * @package midcom.services
 * @see midcom_services_indexer
 */

class midcom_services_indexer_solrDocumentFactory {

    /*
     * The xml document to post.
     * */
    var $document = null;

    public function __construct()
    {
        $this->xml = new DomDocument('1.0', 'UTF-8');
    }

    function reset()
    {
        $this->xml = new DomDocument('1.0', 'UTF-8');
    }

    /**
     * Adds a document to the index. 
     */ 
    public function add($document) 
    {

        $root = $this->xml->createElement('add');
        $this->xml->appendChild($root);
        $element = $this->xml->createElement('doc');
        $this->xml->documentElement->appendChild($element);
        $field = $this->xml->createElement('field');
        $field->setAttribute('name','RI');
        $field->nodeValue = $document->RI;
        $element->appendChild($field);
 
        foreach ($document->list_fields() as $field_name)  {
            $field_record = $document->get_field_record($field_name);
            $field = $this->xml->createElement('field');
            $field->setAttribute('name', $field_record['name']);
            // Escape entities etc to prevent Solr from throwing a hissy fit
            $field->nodeValue = htmlspecialchars($field_record['content']);
            $element->appendChild($field);
        }

    }
    /**
     * Deletes one element
     * @param $id the element id
     */
    public function delete($id) 
    {
        $this->reset();
        $root = $this->xml->createElement('delete');
        $this->xml->appendChild($root);
        //$element = $this->xml->createElement('delete');
        //$this->xml->documentElement->appendChild($element);
        $id_element = $this->xml->createElement('id');
        $this->xml->documentElement->appendChild($id_element);
        $id_element->nodeValue = $id;
    }
    /**
     * Deletes all elements with the id defined
     * (this should be all midgard documents)
     */
    public function delete_all() 
    {
        $this->reset();
        $root = $this->xml->createElement('delete');
        $this->xml->appendChild($root);
        $element = $this->xml->createElement('delete');
        $this->xml->documentElement->appendChild($element);
        $query = $this->xml->createElement('delete');
        $element->appendChild($query);
        $query->nodeValue = "id:[ *TO* ]";
    }

    /**
     * Returns the generated XML
     */
    public function to_xml() 
    {
        if (isset($_REQUEST['debug'])) 
        {
            echo $this->xml->saveXML();
            die();
        }
        return $this->xml->saveXML();
    }

}


/**
 * This class handles the posting to the server. 
 * It's a simple wrapper around the HTTP_request library.
 */

class midcom_services_indexer_solrRequest {

    /*
     * the HTTP_Request object
     * */
    var $request = null;

    /**
     * The xml factory
     */
    var $factory = null;

    function __construct ($factory) {
        $this->factory = $factory;
    }

    function execute() {
        return $this->do_post($this->factory->to_xml());

    }
    /*
     * posts the xml to the suggested url using HTTP_Request.
     * */
    function do_post($xml)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $options = array();
        $options['method'] = HTTP_REQUEST_METHOD_POST ;
        //$url = $GLOBALS['midcom_config']['indexer_solr_url'];
        $url = "http://" . $GLOBALS['midcom_config']['indexer_xmltcp_host'] . 
            ":" . $GLOBALS['midcom_config']['indexer_xmltcp_port'] . "/solr/update";
        $this->request = new HTTP_Request($url, $options);

        $this->request->addRawPostData($xml);
        $this->request->addHeader('Accept-Charset', 'UTF-8');
        $this->request->addHeader('Content-type', 'text/xml; charset=utf-8');
        debug_add("POSTing XML to {$url}\n===\n{$xml}\n===\n");
        $err = $this->request->sendRequest(true);

        $this->code = $this->request->getResponseCode();
        debug_add("Got response code {$this->code}, body\n===\n" . $this->request->getResponseBody() . "\n===\n");

        if (   $this->code != 200
            || PEAR :: isError($err))
        {
            $errstr = '';
            if (is_a($err, 'PEAR_Error'))
            {
                $errstr = $err->getMessage();
            }
            debug_add("Failed to execute Request {$url}:{$this->code} {$errstr}", MIDCOM_LOG_WARN); 
            debug_add("Request content: \n$xml", MIDCOM_LOG_DEBUG);
            debug_pop();
            return false;
        }
        $this->request->addRawPostData('<commit/>');
        $this->request->addHeader('Accept-Charset', 'UTF-8');
        $this->request->addHeader('Content-type', 'text/xml; charset=utf-8');
        $err = $this->request->sendRequest(true);

        if ($this->request->getResponseCode() != 200 || PEAR :: isError($err))
        {
            debug_add("Failed to execute Request {$url}: {$err->getMessage()}", MIDCOM_LOG_WARN); 
            debug_add("Request content: \n$xml", MIDCOM_LOG_INFO); 
            debug_pop();
            return false;
        }
        debug_add('POST ok');
        debug_pop();
        return true;

    }        
       
}

?>
