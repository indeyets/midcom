<?php
/**
 * Pages QB resultsets
 */
class org_openpsa_qbpager extends midcom_baseclasses_components_purecode
{
    var $_midcom_qb = false;
    var $_midcom_qb_count = false;
    var $_pager_id = false;
    var $_offset = 0;
    var $_limit;
    var $_prefix = '';
    var $_request_data = array();
    var $_current_page = 1;
    var $results_per_page = 25;
    var $count = false;
    var $_count_mode = false;
    var $display_pages = 10;

    /**
     * parameter listening enabled
     * 
     * @access private
     * @var boolean
     */
    var $_listen_params = false;
    
    /**
     * Registered get -parameters for listening
     * 
     * @access private
     * @var array
     */
    var $_get_params = array();
    
    /**
     * Cache for parameters to be listened
     * 
     * @access private
     * @var string
     */
    var $_params_cache = false;

    function org_openpsa_qbpager($classname, $pager_id)
    {
        $this->_component = 'org.openpsa.qbpager';
        parent::midcom_baseclasses_components_purecode();

        $this->_limit =& $this->results_per_page;
        $this->_pager_id = $pager_id;
        $this->_midcom_qb = $_MIDCOM->dbfactory->new_query_builder($classname);
        // Make another QB for counting, we need to do this to avoid trouble with core internal references system
        $this->_midcom_qb_count = $_MIDCOM->dbfactory->new_query_builder($classname);
        if (!$this->_sanity_check())
        {
            return false;
        }
        $this->_prefix = 'org_openpsa_qbpager_' . $this->_pager_id . '_';

        return true;
    }

    function listen_parameter($name, $value=false)
    {
        if (empty($name))
        {
            return;
        }
        
        if (   isset($this->_get_params[$name])
            && $this->_get_params[$name] == $value)
        {
            return;
        }
        $this->_get_params[$name] = $value;
        
        $this->_listen_params = true;
    }
    
    function _collect_parameters()
    {
        if (empty($this->_get_params))
        {
            $this->_params_cache = '';
            return;
        }
        
        $_prefix = '&';
        $this->_params_cache = '';
        
        foreach ($this->_get_params as $key => $value)
        {
            if (isset($_GET[$key]))
            {
                if ($value)
                {
                    if (is_array($value))
                    {
                        foreach ($value as $val)
                        {
                            if ($_GET[$key] == $val)
                            {
                                $this->_params_cache .= "{$_prefix}{$key}={$val}";
                            }
                        }
                    }
                    elseif ($_GET[$key] == $value)
                    {
                        $this->_params_cache .= "{$_prefix}{$key}={$value}";
                    }
                }
                elseif (! $_GET[$key])
                {
                    $this->_params_cache .= "{$_prefix}{$key}";
                }
            }
        }
    }
    
    function _get_parameter_string()
    {
        if (! $this->_listen_params)
        {
            return '';
        }
        
        if (! $this->_params_cache)
        {
            $this->_collect_parameters();
        }
        
        return $this->_params_cache;
    }

    /**
     * Makes sure we have some absolutely required things properly set
     */
    function _sanity_check()
    {
        if (!is_object($this->_midcom_qb))
        {
            debug_add('this->_midcom_qb is not an object', MIDCOM_LOG_WARN);
            return false;
        }
        if (empty($this->_pager_id))
        {
            debug_add('this->_pager_id is not set (needed for distinguishing different instances on same request)', MIDCOM_LOG_WARN);
            return false;
        }
        return true;
    }

    /**
     * Check $_REQUEST for variables and sets internal status accordingly
     */
    function _check_page_vars()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $page_var = $this->_prefix . 'page';
        $results_var =  $this->_prefix . 'results';
        if (   array_key_exists($page_var, $_REQUEST)
            && !empty($_REQUEST[$page_var]))
        {
            debug_add("{$page_var} has value: {$_REQUEST[$page_var]}");
            $this->_current_page = $_REQUEST[$page_var];
        }
        if (   array_key_exists($results_var, $_REQUEST)
            && !empty($_REQUEST[$results_var]))
        {
            debug_add("{$results_var} has value: {$_REQUEST[$results_var]}");
            $this->results_per_page = $_REQUEST[$results_var];
        }
        $this->_offset = ($this->_current_page-1)*$this->results_per_page;
        if ($this->_offset<0)
        {
            $this->_offset = 0;
        }
        debug_pop();
        return;
    }
    
    /**
     * Get the current page number
     */
    function get_current_page()
    {
        return $this->_current_page;
    }


    /** 
     * Fetch all $_GET variables, but leave out the page number
     *
     */
    function _get_query_string()
    {
	$query_string = '';
        foreach(explode('&',$_SERVER["QUERY_STRING"]) as $key)
        {
    	    if( !preg_match('/org_openpsa_qbpager/', $key) 
		&& $key != '')
    	    {
		$query_string .= '&amp;'.$key;
            }
        }
        return $query_string;
    } 
    
    
    /**
     * Displays previous/next selector
     */
    function show_previousnext($acl_checks=false)
    {
        $this->_request_data['prefix'] = $this->_prefix;
        $this->_request_data['current_page'] = $this->_current_page;
        $this->_request_data['page_count'] = $this->count_pages($acl_checks);
        $this->_request_data['results_per_page'] = $this->_limit;
        $this->_request_data['offset'] = $this->_offset;
        $this->_request_data['display_pages'] = $this->display_pages;
        //Won't work (wrong scope), so the code is copied below.
        //midcom_show_style('show-pages');
        $data =& $this->_request_data;

        //Skip the header in case we only have one page
        if ($data['page_count'] <= 1)
        {
            return;
        }

        //TODO: "showing results (offset)-(offset+limit)
        //TODO: Localizations
        $page_var = $data['prefix'] . 'page';
        $results_var =  $data['prefix'] . 'results';
        echo '<div class="org_openpsa_qbpager_previousnext">';

        if ($data['current_page'] > 1)
        {
            $previous = $data['current_page'] - 1;
            echo "\n<a class=\"previous_page\" href=\"?{$page_var}={$previous}" . $this->_get_query_string() . "\">" . $this->_l10n->get('previous') . "</a>";
        }

        if ($data['current_page'] < $data['page_count'])
        {
            $next = $data['current_page'] + 1;
            echo "\n<a class=\"next_page\" href=\"?{$page_var}={$next}" . $this->_get_query_string() . "\">" . $this->_l10n->get('next') . "</a>";
        }

        echo "\n</div>\n";

        return;
    }

    /**
     * Displays page selector
     */
    function show_pages($acl_checks=false)
    {
        $this->_request_data['prefix'] = $this->_prefix;
        $this->_request_data['current_page'] = $this->_current_page;
        $this->_request_data['page_count'] = $this->count_pages($acl_checks);
        $this->_request_data['results_per_page'] = $this->_limit;
        $this->_request_data['offset'] = $this->_offset;
        $this->_request_data['display_pages'] = $this->display_pages;
        //Won't work (wrong scope), so the code is copied below.
        //midcom_show_style('show-pages');
        $data =& $this->_request_data;

        //Skip the header in case we only have one page
        if ($data['page_count'] <= 1)
        {
            return;
        }

        //TODO: "showing results (offset)-(offset+limit)
        //TODO: Localizations
        $page_var = $data['prefix'] . 'page';
        $results_var =  $data['prefix'] . 'results';
        echo '<div class="org_openpsa_qbpager_pages">';
        $page = 0;
        $display_start = $data['current_page'] - ceil($data['display_pages']/2);
        if ($display_start < 0)
        {
            $display_start = 0;
        }
        $display_end = $data['current_page'] + ceil($data['display_pages']/2);
        if ($display_end > $data['page_count'])
        {
            $display_end = $data['page_count'];
        }

        if ($data['current_page'] > 1)
        {
            $previous = $data['current_page'] - 1;
            if ($previous != 1)
            {
                echo "\n<a class=\"first_page\" href=\"?{$page_var}=1" . $this->_get_query_string() . "\">" . $this->_l10n->get('first') . "</a>";
            }
            echo "\n<a class=\"previous_page\" href=\"?{$page_var}={$previous}" . $this->_get_query_string() . "\">" . $this->_l10n->get('previous') . "</a>";
        }


        while ($page++ < $display_end)
        {
            if ($page < $display_start)
            {
                continue;
            }
            if ($page == $data['current_page'])
            {
                echo "\n<span class=\"current_page\">{$page}</span>";
                continue;
            }
            echo "\n<a class=\"select_page\" href=\"?{$page_var}={$page}" . $this->_get_query_string() . "\">{$page}</a>";
        }

        if ($data['current_page'] < $data['page_count'])
        {
            $next = $data['current_page'] + 1;
            echo "\n<a class=\"next_page\" href=\"?{$page_var}={$next}" . $this->_get_query_string() . "\">" . $this->_l10n->get('next') . "</a>";

            if ($next != $data['page_count'])
            {
                echo "\n<a class=\"last_page\" href=\"?{$page_var}={$data['page_count']}" . $this->_get_query_string() . "\">" . $this->_l10n->get('last') . "</a>";
            }
        }

        echo "\n</div>\n";

        return;
    }
    
    /**
     * Displays page selector as list
     */
    function show_pages_as_list($acl_checks=false)
    {
        $link_suffix = $this->_get_parameter_string();
        
        $this->_request_data['prefix'] = $this->_prefix;
        $this->_request_data['current_page'] = $this->_current_page;
        $this->_request_data['page_count'] = $this->count_pages($acl_checks);
        $this->_request_data['results_per_page'] = $this->_limit;
        $this->_request_data['offset'] = $this->_offset;
        $this->_request_data['display_pages'] = $this->display_pages;
        //Won't work (wrong scope), so the code is copied below.
        //midcom_show_style('show-pages');
        $data =& $this->_request_data;

        //Skip the header in case we only have one page
        if ($data['page_count'] <= 1)
        {
            return;
        }

        //TODO: "showing results (offset)-(offset+limit)
        //TODO: Localizations
        $page_var = $data['prefix'] . 'page';
        $results_var =  $data['prefix'] . 'results';
        echo '<div class="org_openpsa_qbpager_pages">';
        echo "\n    <ul>\n";
        $page = 0;
        $display_start = $data['current_page'] - ceil($data['display_pages']/2);
        if ($display_start < 0)
        {
            $display_start = 0;
        }
        $display_end = $data['current_page'] + ceil($data['display_pages']/2);
        if ($display_end > $data['page_count'])
        {
            $display_end = $data['page_count'];
        }
        
        if ($data['current_page'] > 1)
        {
            $previous = $data['current_page'] - 1;
            if ($previous != 1)
            {
                //echo "\n<li class=\"first\"><a class=\"first_page\" href=\"?{$page_var}=1\">&nbsp;</a></li>";
                //echo "\n<li class=\"separator\"></li>";
            }

            echo "\n<li class=\"prev\" onclick=\"window.location='?{$page_var}={$previous}{$link_suffix}';\"></li>";

            if ($display_start > 1)
            {
                echo "\n<li class=\"separator\"></li>";
                echo "\n<li class=\"page last\" onclick=\"window.location='?{$page_var}=1{$link_suffix}';\">1</li>";
                echo "\n<li class=\"separator\"></li>";
                echo "\n<li class=\"page splitter\">...</li>";
                echo "\n<li class=\"separator\"></li>";
            }
        }


        while ($page++ < $display_end)
        {
            if ($page < $display_start)
            {
                continue;
            }
            if ($page == $data['current_page'])
            {
                echo "\n<li class=\"page active\">{$page}</li>";
                echo "\n<li class=\"separator\"></li>";
                continue;
            }
            echo "\n<li class=\"page\" onclick=\"window.location='?{$page_var}={$page}{$link_suffix}';\">{$page}</li>";
            echo "\n<li class=\"separator\"></li>";
        }

        if ($data['current_page'] < $data['page_count'])
        {
            $next = $data['current_page'] + 1;
            
            if ($next != $data['page_count'])
            {
                // echo "\n<li class=\"separator\"></li>";
                //echo "\n<li class=\"last\"><a class=\"last_page\" href=\"?{$page_var}={$data['page_count']}\">&nbsp;</a></li>";
                echo "\n<li class=\"separator\"></li>";
                echo "\n<li class=\"page splitter\">...</li>";
                echo "\n<li class=\"separator\"></li>";
                echo "\n<li class=\"page last\" onclick=\"window.location='?{$page_var}={$data['page_count']}{$link_suffix}';\">{$data['page_count']}</li>";
            }
            
            
            echo "\n<li class=\"next\" onclick=\"window.location='?{$page_var}={$next}{$link_suffix}';\"></li>";
        }

        echo "\n    </ul>\n";
        echo "</div>\n";

        return;
    }

    /**
     * sets LIMIT and OFFSET for requested page
     */
    function _qb_limits(&$qb)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_check_page_vars();
        $qb->set_limit($this->_limit);
        $qb->set_offset($this->_offset);
        debug_add("set offset to {$this->_offset} and limit to {$this->_limit}");
        debug_pop();
        return;
    }

    function _clear_qb_limits(&$qb)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $limit = abs(pow(2,31)-1); //Largest signed integer we can use as limit.
        $offset = 0;
        $qb->set_limit($limit);
        $qb->set_offset($offset);
        debug_add("set offset to {$offset} and limit to {$limit}");
        debug_pop();
        return;
    }

    function execute()
    {
        if (!$this->_sanity_check())
        {
            return false;
        }
        $this->_qb_limits($this->_midcom_qb);
        return $this->_midcom_qb->execute();
    }

    function execute_unchecked()
    {
        if (!$this->_sanity_check())
        {
            return false;
        }
        $this->_qb_limits($this->_midcom_qb);
        return $this->_midcom_qb->execute_unchecked();
    }

    /**
     * Returns number of total pages for query
     *
     * By default returns a number of pages without any ACL checks, checked
     * count is available but is much slower.
     */
    function count_pages($acl_checks=false)
    {
        if (!$this->_sanity_check())
        {
            return false;
        }
        if (!$acl_checks)
        {
            $this->count_unchecked();
        }
        else
        {
            $this->count();
        }
        return ceil($this->count/$this->results_per_page);
    }

    //These two wrapped to prevent their use since the pager needs them internally
    function set_limit($limit)
    {
        //PONDER: should we allow some special case here, I think not
        debug_add('operation not allowed', MIDCOM_LOG_WARN);
        return false;
    }

    function set_offset($offset)
    {
        //PONDER: should we allow some special case here, I think not
        debug_add('operation not allowed', MIDCOM_LOG_WARN);
        return false;
    }

    //Rest of supported methods wrapped with extra sanity check
    function add_constraint($param, $op, $val)
    {
        if (!$this->_sanity_check())
        {
            return false;
        }
        $this->_midcom_qb_count->add_constraint($param, $op, $val);
        return $this->_midcom_qb->add_constraint($param, $op, $val);
    }

    function add_order($param, $sort='ASC')
    {
        if (!$this->_sanity_check())
        {
            return false;
        }
        return $this->_midcom_qb->add_order($param, $sort);
    }

    function begin_group($type)
    {
        if (!$this->_sanity_check())
        {
            return false;
        }
        $this->_midcom_qb_count->begin_group($type);
        return $this->_midcom_qb->begin_group($type);
    }

    function end_group()
    {
        if (!$this->_sanity_check())
        {
            return false;
        }
        $this->_midcom_qb_count->end_group();
        return $this->_midcom_qb->end_group();
    }

    function set_lang($lang)
    {
        if (!$this->_sanity_check())
        {
            return false;
        }
        $this->_midcom_qb_count->set_lang($lang);
        return $this->_midcom_qb->set_lang($lang);
    }

    function count()
    {
        if (!$this->_sanity_check())
        {
            return false;
        }
        if (   !$this->count
            || $this->_count_mode != 'count')
        {
            $this->count = $this->_midcom_qb_count->count();
        }
        $this->_count_mode = 'count';
        return $this->count;
    }

    function count_unchecked()
    {
        if (!$this->_sanity_check())
        {
            return false;
        }
        if (   !$this->count
            || $this->_count_mode != 'count_unchecked')
        {
            $this->count = $this->_midcom_qb_count->count_unchecked();
        }
        $this->_count_mode = 'count_unchecked';
        return $this->count;
    }

}
?>