<?php

/**
 * @package midcom.helper.search
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM Indexer Front-End, Viewer Class
 * 
 * ...
 * 
 * @package midcom.helper.search
 */
class midcom_helper_search_viewer extends midcom_baseclasses_components_request
{
    /**
     * Constructor.
     * 
     * Nothing fancy, defines the request switch.
     */
    function midcom_helper_search_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
        
        // Default search form, no args, Basic search from
        $this->_request_switch['basic'] = Array ( 'handler' => 'searchform' );
        
        // Resultlists, controlled using HTTP GET/POST
		$this->_request_switch[] = Array ( 'fixed_args' => 'result', 'no_cache' => true, 'handler' => 'result' );
        
        // Advanced search form, no args
        $this->_request_switch['advanced'] = Array ( 'fixed_args' => 'advanced', 'handler' => 'searchform' );
    }
    
    
    /**
     * Search form handler, nothing to do here.
     * 
     * It uses the handler ID to distinguish between basic and advanced search forms.
     * 
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param mixed $data The local request data. 
     * @return bool Indicating success.
     */
    function _handler_searchform($handler_id, $args, &$data)
    {
        switch ($handler_id)
        {
            case 'basic':
                $data['query'] = (array_key_exists('query', $_REQUEST) ? $_REQUEST['query'] : '');
                break;
            
            case 'advanced':
                $data['query'] = (array_key_exists('query', $_REQUEST) ? $_REQUEST['query'] : '');
                $data['topic'] = (array_key_exists('topic', $_REQUEST) ? $_REQUEST['topic'] : '');
                $data['component'] = (array_key_exists('component', $_REQUEST) ? $_REQUEST['component'] : '');
                $data['lastmodified'] = (array_key_exists('lastmodified', $_REQUEST) ? ((integer) $_REQUEST['lastmodified']) : 0);
                break;
                
            default:
                $this->errstr = "Wrong handler ID {$handler_id} for searchform handler";
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
        }
        $data['type'] = $handler_id;
        return true;
    }
    
    /**
     * Search form show handler, displays the search form, including
     * some hints about how to write queries. 
     * 
     * @param mixed $handler_id The ID of the handler.
     * @param mixed $data The local request data. 
     */
    function _show_searchform($handler_id, &$data)
    {
        midcom_show_style('search_form');
    }
    
    /**
     * Queries the information from the index and prepares to display the result page.
     * 
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param mixed $data The local request data. 
     * @return bool Indicating success.
     */
    function _handler_result($handler_id, $args, &$data)
    {
        $indexer =& $GLOBALS['midcom']->get_service('indexer');
        
        $data['type'] = $_REQUEST['type'];
        
        switch ($data['type'])
        {
            case 'basic':
                $data['query'] = trim($_REQUEST['query']);
                
				if( count(explode(" ", $data['query'])) == 1 ) $data['query'] .= "*";
				
				$result = $indexer->query($data['query']);
                break;
            
            case 'advanced':
                $data['query'] = trim($_REQUEST['query']);
                
				if( count(explode(" ", $data['query'])) == 1 ) $data['query'] .= "*";
				
				$data['topic'] = trim($_REQUEST['topic']);
                // $data['topic2'] = trim($_REQUEST['topic2']);
                $data['component'] = trim($_REQUEST['component']);
                $data['lastmodified'] = (integer) (trim($_REQUEST['lastmodified']));
                if ($data['lastmodified'] > 0)
                {
                    $filter = new midcom_services_indexer_filter_date('__EDITED', $data['lastmodified'], 0);
                }
                else
                {
                    $filter = null;
                }
                
                if ($data['query'] != '')
                {
                    $final_query = "({$data['query']})";
                }
                else
                {
                    $final_query = '';
                }
                
                if ($data['topic'] != '')
                {
                    if ($final_query != '')
                    {
                        $final_query .= ' AND ';
                    }
                    $final_query .= "__TOPIC_URL:\"{$data['topic']}*\"";
                }
                /*
                *This if makes able to search more than one topics
                *You need to put in to the input value in the search form topics separated by ;
                *no spaces
                * /
                if ($data['topic2'] != '')
                {
                	if ($final_query != '')
                    {
                        $final_query .= ' AND ';
                    }
                	$topic_arrays = explode(";" ,$data['topic2']);
                	$final_tmp_query = "";
                	
                	foreach ($topic_arrays as $topic_tmp)
                	{
            	    	if ($final_tmp_query != '')
        	            {
    	                    $final_tmp_query .= ' OR ';
	                    }
	                    else
	                    {
		                    $final_tmp_query .= " ( ";
	                    }
                    	$final_tmp_query .= "__TOPIC_URL:\"{$topic_tmp}*\"";
                	}
                	$final_tmp_query .= " )";
                	
                	
                    $final_query .= $final_tmp_query;
                }
                */
                
                if ($data['component'] != '')
                {
                    if ($final_query != '')
                    {
                        $final_query .= ' AND ';
                    }
                    $final_query .= "__COMPONENT:{$data['component']}";
                }
                debug_add("Final query: {$final_query}");
                
                $result = $indexer->query($final_query, $filter);
                break;
                
            default:
                $this->errstr = "Wrong handler ID {$handler_id} for searchform handler";
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
        }
        
        if ($result === false)
        {
            // Error while searching, we ignore this silently, as this is usually
            // a broken query. We don't have yet a way to pass error messages from
            // the indexer backend though (what would I give for a decent exectpion
            // handling here...)
            $result = Array();
        }
        
        $count = count($result);
        $data['document_count'] = $count;
        
        if ($count > 0)
        {
	        $results_per_page = $this->_config->get('results_per_page');
	        $max_pages = ceil($count / $results_per_page);
            $page = min($_REQUEST['page'], $max_pages); 
	        $first_document_id = ($page - 1) * $results_per_page;
            $last_document_id = min(($count - 1), (($page * $results_per_page) - 1));
	        
	        $data['page'] = $page;
            $data['max_pages'] = $max_pages;
            $data['first_document_number'] = $first_document_id + 1;
            $data['last_document_number'] = $last_document_id + 1;
            $data['shown_documents'] = $last_document_id - $first_document_id + 1; 
            $data['results_per_page'] = $results_per_page;
            $data['result'] = array_slice($result, $first_document_id, $results_per_page);
        }
        return true;
    }
    
    /**
     * Displays the resultset.
     * 
     * @param mixed $handler_id The ID of the handler.
     * @param mixed $data The local request data.
     */
    function _show_result($handler_id, &$data)
    {
        if ($data['document_count'] > 0)
        {
            midcom_show_style('results');
        }
        else
        {
            midcom_show_style('no_match');
        }
    }
}

?>