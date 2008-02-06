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
        $this->_request_switch['basic'] = array
        (
            'handler' => 'searchform'
        );
        
        // Resultlists, controlled using HTTP GET/POST
        $this->_request_switch[] = array
        (
            'fixed_args' => 'result', 
            'no_cache' => true, 
            'handler' => 'result'
        );
        
        // Advanced search form, no args
        $this->_request_switch['advanced'] = array
        (
            'fixed_args' => 'advanced',
            'handler' => 'searchform'
        );
        
        // OpenSearch description file
        $this->_request_switch['opensearch_description'] = array
        ( 
            'fixed_args' => 'opensearch.xml', 
            'handler' => 'opensearchdescription' 
        );
    }
    
    function _on_handle($handler_id, $args)
    {
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel'   => 'search',
                'type'  => 'application/opensearchdescription+xml',
                'title' => $this->_topic->extra,
                'href'  => $_MIDCOM->get_host_name() . $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'opensearch.xml',
            )
        );
        
        return true;
    }

    /**
     * Search form handler, nothing to do here.
     * 
     * It uses the handler ID to distinguish between basic and advanced search forms.
     * 
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param mixed &$data The local request data. 
     * @return boolean Indicating success.
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
                //$data['topic'] = (array_key_exists('topic', $_REQUEST) ? $_REQUEST['topic'] : '');
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
     * @param mixed &$data The local request data. 
     */
    function _show_searchform($handler_id, &$data)
    {
        midcom_show_style('search_form');
    }

    /**
     * Appends language to search terms
     *
     * @param string &$final_query reference to the query string to be passed on to the indexer.
     */
    function add_multilang_terms(&$final_query)
    {
        if ($GLOBALS['midcom_config']['i18n_multilang_strict'])
        {
            $final_query .= ' AND (__LANG:"' . $_MIDCOM->i18n->get_current_language() . '")';
        }
    }

    /**
     * Expand arrays of custom rules to end of query
     *
     * @param string &$final_query reference to the query string to be passed on to the indexer.
     * @param mixed $terms array or string to append
     */
    function append_terms_recursive(&$final_query, $terms)
    {
        if (is_array($terms))
        {
            foreach ($terms as $term)
            {
                $this->append_terms_recursive($final_query, $term);
            }
            return;
        }
        if (is_string($terms))
        {
            $final_query .= "{$terms}";
            return;
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('Don\'t know how to handle terms of type: ' . gettype($terms), MIDCOM_LOG_ERROR);
        debug_print_r('$terms', $terms);
        debug_pop();
        return;
    }

    /**
     * Queries the information from the index and prepares to display the result page.
     * 
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param mixed &$data The local request data. 
     * @return boolean Indicating success.
     */
    function _handler_result($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $indexer =& $_MIDCOM->get_service('indexer');
        
        // Sane defaults for REQUEST vars
        if (!isset($_REQUEST['type']))
        {
            $_REQUEST['type'] = 'basic';
        }
        if (!isset($_REQUEST['page']))
        {
            $_REQUEST['page'] = 1;
        }
        if (!isset($_REQUEST['component']))
        {
            $_REQUEST['component'] = '';
        }
        if (!isset($_REQUEST['topic']))
        {
            $_REQUEST['topic'] = '';
        }
        if (!isset($_REQUEST['lastmodified']))
        {
            $_REQUEST['lastmodified'] = 0;
        }
        // If we don't have a query string, relocate to empty search form
        if (!isset($_REQUEST['query']))
        {
            debug_add('$_REQUEST["query"] is not set, relocating back to form', MIDCOM_LOG_INFO);
            debug_pop();
            if ($data['type'] == 'basic')
            {
                $_MIDCOM->relocate('');
            }
            $_MIDCOM->relocate('advanced/');
        }

        $data['type'] = $_REQUEST['type'];
        $data['query'] = trim($_REQUEST['query']);

        if (   count(explode(' ', $data['query'])) == 1
            && strpos($data['query'], '*') === false
            && $this->_config->get('single_term_auto_wilcard'))
        {
            //If there is only one search term append * to the query if auto_wildcard is enabled
            $data['query'] .= '*';
        }

        switch ($data['type'])
        {
            case 'basic':
                $final_query = $data['query'];
                $this->add_multilang_terms($final_query);
                debug_add("Final query: {$final_query}");
                $result = $indexer->query($final_query);
                break;
            
            case 'advanced':
                $data['request_topic'] = trim($_REQUEST['topic']);
                $data['component'] = trim($_REQUEST['component']);
                $data['lastmodified'] = (integer) trim($_REQUEST['lastmodified']);
                if ($data['lastmodified'] > 0)
                {
                    $filter = new midcom_services_indexer_filter_date('__EDITED', $data['lastmodified'], 0);
                }
                else
                {
                    $filter = null;
                }
                
                if ($data['query'] != '' )
                {
                    $final_query = ( $GLOBALS['midcom_config']['indexer_backend'] == 'solr' ) ? $data['query'] : "({$data['query']})";
                }
                else
                {
                    $final_query = '';
                }
                
                if ($data['request_topic'] != '')
                {
                    if ($final_query != '')
                    {
                        $final_query .= ' AND ';
                    }
                    $final_query .= "__TOPIC_URL:\"{$data['request_topic']}*\"";
                }
               
                if ($data['component'] != '')
                {
                    if ($final_query != '')
                    {
                        $final_query .= ' AND ';
                    }
                    $final_query .= "__COMPONENT:{$data['component']}";
                }

                // Way to add very custom terms
                if (isset($_REQUEST['append_terms']))
                {
                    $this->append_terms_recursive($final_query, $_REQUEST['append_terms']);
                }

                $this->add_multilang_terms($final_query);
                debug_add("Final query: {$final_query}");
                
                $result = $indexer->query($final_query, $filter);
                break;
                
            default:
                $this->errstr = "Wrong handler ID {$handler_id} for searchform handler";
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
        }
        
        if ($result === false)
        {
            // Error while searching, we ignore this silently, as this is usually
            // a broken query. We don't have yet a way to pass error messages from
            // the indexer backend though (what would I give for a decent exectpion
            // handling here...)
            debug_add('Got boolean false as resultset (likely broken query), casting to empty array', MIDCOM_LOG_WARN);
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
        debug_pop();
        return true;
    }

    /**
     * Displays the resultset.
     * 
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
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
    
    /**
     * Prepare OpenSearch data file for browser search bar integration.
     * 
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param mixed &$data The local request data. 
     * @return boolean Indicating success.
     */
    function _handler_opensearchdescription($handler_id, $args, &$data)
    {
        $_MIDCOM->cache->content->content_type("application/opensearchdescription+xml");
        $_MIDCOM->header("Content-type: application/opensearchdescription+xml; charset=UTF-8");
        $_MIDCOM->skip_page_style = true;
        return true;
    }
    
    /**
     * Display OpenSearch data file for browser search bar integration.
     * 
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_opensearchdescription($handler_id, &$data)
    {
        $data['node'] = $this->_topic;
        midcom_show_style('opensearch_description');
    }
}

?>