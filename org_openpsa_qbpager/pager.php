<?php
/**
 * @package org_openpsa_qbpager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/**
 * Pages QB resultsets
 *
 * @package org_openpsa_qbpager
 */
class org_openpsa_qbpager_pager
{
    private $qb = false;
    private $qb_count = false;
    private $pager_id = false;
    private $offset = 0;
    private $limit;
    private $prefix = '';
    private $data = array();
    private $current_page = 1;
    public $results_per_page = 25;
    public $display_pages = 10;
    public $count = false;
    private $_count_mode = false;
    
    /**
     * parameter listening enabled
     *
     * @access private
     * @var boolean
     */
    private $listen_parameters = false;

    /**
     * Registered HTTP GET -parameters for listening
     *
     * @access private
     * @var array
     */
    private $http_get_params = array();

    /**
     * Cache for parameters to be listened
     *
     * @access private
     * @var string
     */
    private $parameter_cache = false;
    
    function __construct($classname, $pager_id = null)
    {
        $this->limit =& $this->results_per_page;
        
        if (is_null($pager_id))
        {
            $pager_id = $classname;
        }
        
        if (!class_exists($classname))
        {
            throw new Exception("MgdSchema class {$classname} not loaded.");
        }

        $this->pager_id = $pager_id;
        $this->qb = new midgard_query_builder($classname);
        // Make another QB for counting, we need to do this to avoid trouble with core internal references system
        $this->qb_count = new midgard_query_builder($classname);
        if (!$this->check_sanity())
        {
            return false;
        }
        $this->prefix = 'org_openpsa_qbpager_' . $this->pager_id . '_';

        return true;
    }
    
    public function listen_parameter($name, $value=false)
    {
        if (empty($name))
        {
            return;
        }

        if (   isset($this->http_get_parameters[$name])
            && $this->http_get_parameters[$name] == $value)
        {
            return;
        }
        $this->http_get_parameters[$name] = $value;

        $this->listen_parameters = true;
    }
    
    private function collect_parameters()
    {
        if (empty($this->http_get_parameters))
        {
            $this->_parameter_cache = '';
            return;
        }

        $_prefix = '&';
        $this->_parameter_cache = '';

        foreach ($this->http_get_parameters as $key => $value)
        {
            if (isset($_MIDCOM->dispatcher->get[$key]))
            {
                if ($value)
                {
                    if (is_array($value))
                    {
                        foreach ($value as $val)
                        {
                            if ($_MIDCOM->dispatcher->get[$key] == $val)
                            {
                                $this->_parameter_cache .= "{$_prefix}{$key}={$val}";
                            }
                        }
                    }
                    elseif ($_MIDCOM->dispatcher->get[$key] == $value)
                    {
                        $this->_parameter_cache .= "{$_prefix}{$key}={$value}";
                    }
                    elseif ($value == "*")
                    {
                        $this->_parameter_cache .= "{$_prefix}{$key}={$_MIDCOM->dispatcher->get[$key]}";
                    }
                }
                elseif (! $_MIDCOM->dispatcher->get[$key])
                {
                    $this->_parameter_cache .= "{$_prefix}{$key}";
                }
            }
        }
    }

    private function get_parameter_string()
    {
        if (! $this->listen_parameters)
        {
            return '';
        }

        if (! $this->_parameter_cache)
        {
            $this->collect_parameters();
        }

        return $this->_parameter_cache;
    }

    /**
     * Makes sure we have some absolutely required things properly set
     */
    private function check_sanity()
    {
        if (!is_object($this->qb))
        {
            return false;
        }
        
        if (empty($this->pager_id))
        {

            return false;
        }
        return true;
    }
    
    /**
     * Check $_REQUEST for variables and sets internal status accordingly
     */
    private function check_request_variables()
    {
        $page_var = $this->prefix . 'page';
        $results_var =  $this->prefix . 'results';
        if (   array_key_exists($page_var, $_REQUEST)
            && !empty($_REQUEST[$page_var]))
        {
            $this->current_page = $_REQUEST[$page_var];
        }
        if (   array_key_exists($results_var, $_REQUEST)
            && !empty($_REQUEST[$results_var]))
        {
            $this->results_per_page = $_REQUEST[$results_var];
        }
        $this->offset = ($this->current_page-1) * $this->results_per_page;
        if ($this->offset < 0)
        {
            $this->offset = 0;
        }
        return;
    }
    
    /**
     * Get the current page number
     */
    public function get_current_page()
    {
        return $this->current_page;
    }
    
    /**
     * Generates a previous/next selector
     *
     * @return string Previous/Next selector
     */
    public function get_previousnext($acl_checks = false)
    {
        $this->data['prefix'] = $this->prefix;
        $this->data['current_page'] = $this->current_page;
        $this->data['page_count'] = $this->count_pages($acl_checks);
        $this->data['results_per_page'] = $this->limit;
        $this->data['offset'] = $this->offset;
        $this->data['display_pages'] = $this->display_pages;
        
        $previousnext = '';

        //Won't work (wrong scope), so the code is copied below.
        //midcom_show_style('show-pages');
        $data =& $this->data;

        //Skip the header in case we only have one page
        if ($data['page_count'] <= 1)
        {
            return;
        }

        //TODO: "showing results (offset)-(offset+limit)
        //TODO: Localizations
        $page_var = $data['prefix'] . 'page';
        $results_var =  $data['prefix'] . 'results';
        $previousnext .= '<div class="org_openpsa_qbpager_previousnext">';

        if ($data['current_page'] > 1)
        {
            $previous = $data['current_page'] - 1;
            $previousnext .= "\n<a class=\"previous_page\" href=\"?{$page_var}={$previous}" . $this->get_query_string() . "\">" . 'previous' . "</a>";
        }

        if ($data['current_page'] < $data['page_count'])
        {
            $next = $data['current_page'] + 1;
            $previousnext .= "\n<a class=\"next_page\" href=\"?{$page_var}={$next}" . $this->get_query_string() . "\">" . 'next' . "</a>";
        }

        $previousnext .= "\n</div>\n";

        return $previousnext;
    }
    
    /**
     * Displays page selector
     */
    public function get_pages($acl_checks=false)
    {
        $this->data['prefix'] = $this->prefix;
        $this->data['current_page'] = $this->current_page;
        $this->data['page_count'] = $this->count_pages($acl_checks);
        $this->data['results_per_page'] = $this->limit;
        $this->data['offset'] = $this->offset;
        $this->data['display_pages'] = $this->display_pages;
        //Won't work (wrong scope), so the code is copied below.
        //midcom_show_style('show-pages');
        $data =& $this->data;

        //Skip the header in case we only have one page
        if ($data['page_count'] <= 1)
        {
            return;
        }
        
        $pages = '';

        //TODO: "showing results (offset)-(offset+limit)
        //TODO: Localizations
        $page_var = $data['prefix'] . 'page';
        $results_var =  $data['prefix'] . 'results';
        $pages .= '<div class="org_openpsa_qbpager_pages">';
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
                $pages .= "\n<a class=\"first_page\" href=\"?{$page_var}=1" . $this->get_query_string() . "\">" . 'first' . "</a>";
            }
            $pages .= "\n<a class=\"previous_page\" href=\"?{$page_var}={$previous}" . $this->get_query_string() . "\">" . 'previous' . "</a>";
        }


        while ($page++ < $display_end)
        {
            if ($page < $display_start)
            {
                continue;
            }
            if ($page == $data['current_page'])
            {
                $pages .= "\n<span class=\"current_page\">{$page}</span>";
                continue;
            }
            $pages .= "\n<a class=\"select_page\" href=\"?{$page_var}={$page}" . $this->get_query_string() . "\">{$page}</a>";
        }

        if ($data['current_page'] < $data['page_count'])
        {
            $next = $data['current_page'] + 1;
            $pages .= "\n<a class=\"next_page\" href=\"?{$page_var}={$next}" . $this->get_query_string() . "\">" . 'next' . "</a>";

            if ($next != $data['page_count'])
            {
                $pages .= "\n<a class=\"last_page\" href=\"?{$page_var}={$data['page_count']}" . $this->get_query_string() . "\">" . 'last' . "</a>";
            }
        }

        $pages .= "\n</div>\n";

        return $pages;
    }

    /**
     * Displays page selector as XML
     */
    public function get_pages_as_xml($acl_checks = false)
    {
        $pages_xml_str = "<pages ";
        
        $this->data['prefix'] = $this->prefix;
        $this->data['current_page'] = $this->current_page;
        $this->data['page_count'] = $this->count_pages($acl_checks);
        $this->data['results_per_page'] = $this->limit;
        $this->data['offset'] = $this->offset;
        $this->data['display_pages'] = $this->display_pages;
        $data =& $this->data;
        
        $pages_xml_str .= "total=\"{$data['page_count']}\">\n";
        
        //Skip the header in case we only have one page
        if ($data['page_count'] <= 1)
        {
            $pages_xml_str .= "</pages>\n";
            return $pages_xml_str;
        }

        //TODO: "showing results (offset)-(offset+limit)

        $page_var = $data['prefix'] . 'page';
        $results_var =  $data['prefix'] . 'results';
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
                $pages_xml_str .= "<page class=\"first_page\" number=\"1\" url=\"?{$page_var}=1" . $this->get_query_string() . "\"><![CDATA[" . 'first' . "]]></page>\n";
            }
            $pages_xml_str .= "<page class=\"previous_page\" number=\"{$previous}\" url=\"?{$page_var}={$previous}" . $this->get_query_string() . "\"><![CDATA[" . 'previous' . "]]></page>\n";
        }


        while ($page++ < $display_end)
        {
            if ($page < $display_start)
            {
                continue;
            }
            
            if ($page == $data['current_page'])
            {
                $pages_xml_str .= "<page class=\"current_page\" number=\"{$page}\" url=\"\">{$page}</page>\n";
                continue;
            }
            
            $pages_xml_str .= "<page class=\"select_page\" number=\"{$page}\" url=\"?{$page_var}={$page}" . $this->get_query_string() . "\">{$page}</page>\n";
        }

        if ($data['current_page'] < $data['page_count'])
        {
            $next = $data['current_page'] + 1;
            $pages_xml_str .= "<page class=\"next_page\" number=\"{$next}\" url=\"?{$page_var}={$next}" . $this->get_query_string() . "\"><![CDATA[" . 'next' . "]]></page>\n";

            if ($next != $data['page_count'])
            {
                $pages_xml_str .= "<page class=\"last_page\" number=\"{$data['page_count']}\" url=\"?{$page_var}={$data['page_count']}" . $this->get_query_string() . "\"><![CDATA[" . 'last' . "]]></page>\n";
            }
        }
        
        $pages_xml_str .= "</pages>\n";
        
        return $pages_xml_str;
    }
    
    /**
     * Displays page selector as list
     */
    public function get_pages_as_list($acl_checks=false)
    {
        $link_suffix = $this->get_parameter_string();

        $this->data['prefix'] = $this->prefix;
        $this->data['current_page'] = $this->current_page;
        $this->data['page_count'] = $this->count_pages($acl_checks);
        $this->data['results_per_page'] = $this->limit;
        $this->data['offset'] = $this->offset;
        $this->data['display_pages'] = $this->display_pages;
        //Won't work (wrong scope), so the code is copied below.
        //midcom_show_style('show-pages');
        $data =& $this->data;
        
        $pages = '';

        //Skip the header in case we only have one page
        if ($data['page_count'] <= 1)
        {
            return;
        }

        //TODO: "showing results (offset)-(offset+limit)
        //TODO: Localizations
        $page_var = $data['prefix'] . 'page';
        $results_var =  $data['prefix'] . 'results';
        $pages .= '<div class="org_openpsa_qbpager_pages">';
        $pages .= "\n    <ul>\n";
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
                //$pages .= "\n<li class=\"first\"><a class=\"first_page\" href=\"?{$page_var}=1\">&nbsp;</a></li>";
                //$pages .= "\n<li class=\"separator\"></li>";
            }

            $pages .= "\n<li class=\"prev\" onclick=\"window.location='?{$page_var}={$previous}{$link_suffix}';\"></li>";

            if ($display_start > 1)
            {
                $pages .= "\n<li class=\"separator\"></li>";
                $pages .= "\n<li class=\"page last\" onclick=\"window.location='?{$page_var}=1{$link_suffix}';\">1</li>";
                $pages .= "\n<li class=\"separator\"></li>";
                $pages .= "\n<li class=\"page splitter\">...</li>";
                $pages .= "\n<li class=\"separator\"></li>";
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
                $pages .= "\n<li class=\"page active\">{$page}</li>";
                $pages .= "\n<li class=\"separator\"></li>";
                continue;
            }
            if ($page < $data['page_count'])
            {
                $pages .= "\n<li class=\"page\" onclick=\"window.location='?{$page_var}={$page}{$link_suffix}';\">{$page}</li>";
                $pages .= "\n<li class=\"separator\"></li>";
            }
        }

        if ($data['current_page'] < $data['page_count'])
        {
            $next = $data['current_page'] + 1;

            if ($next != $data['page_count'])
            {
                //$pages .= "\n<li class=\"separator\"></li>";
                //$pages .= "\n<li class=\"last\"><a class=\"last_page\" href=\"?{$page_var}={$data['page_count']}\">&nbsp;</a></li>";
                $pages .= "\n<li class=\"page splitter\">...</li>";
                $pages .= "\n<li class=\"separator\"></li>";
                $pages .= "\n<li class=\"page last\" onclick=\"window.location='?{$page_var}={$data['page_count']}{$link_suffix}';\">{$data['page_count']}</li>";
            }


            $pages .= "\n<li class=\"next\" onclick=\"window.location='?{$page_var}={$next}{$link_suffix}';\"></li>";
        }

        $pages .= "\n    </ul>\n";
        $pages .= "</div>\n";

        return $pages;
    }
    
    /**
     * Fetch all $_MIDCOM->dispatcher->get variables, but leave out the page number
     *
     */
    private function get_query_string()
    {
        $query_string = '';
        foreach (explode('&', $_SERVER['QUERY_STRING']) as $key)
        {
            if(   !preg_match('/org_openpsa_qbpager/', $key)
               && $key != '')
            {
                $query_string .= '&amp;'.$key;
            }
        }
        return $query_string;
    }
    
    /**
     * sets LIMIT and OFFSET for requested page
     */
    private function set_qb_limits(&$qb)
    {
        $this->check_request_variables();
        $qb->set_limit($this->limit);
        $qb->set_offset($this->offset);
        return;
    }

    private function clear_qb_limits(&$qb)
    {
        $limit = abs(pow(2, 31) - 1); //Largest signed integer we can use as limit.
        $offset = 0;
        $qb->set_limit($limit);
        $qb->set_offset($offset);
        return;
    }

    public function execute()
    {
        if (!$this->check_sanity())
        {
            return false;
        }

        $this->set_qb_limits($this->qb);
        return $this->qb->execute();
    }

    /**
     * Returns number of total pages for query
     *
     * By default returns a number of pages without any ACL checks, checked
     * count is available but is much slower.
     */
    public function count_pages($acl_checks=false)
    {
        if (!$this->check_sanity())
        {
            return false;
        }
        
        $this->count = $this->count();
        
        if (!$this->results_per_page)
        {
            $this->results_per_page = 4;
        }
        
        return ceil($this->count / $this->results_per_page);
    }
    
    // Rest of supported methods wrapped with extra sanity check
    public function add_constraint($param, $op, $val)
    {
        if (!$this->check_sanity())
        {
            return false;
        }
        $this->qb_count->add_constraint($param, $op, $val);
        return $this->qb->add_constraint($param, $op, $val);
    }

    public function add_order($param, $sort='ASC')
    {
        if (!$this->check_sanity())
        {
            return false;
        }
        return $this->qb->add_order($param, $sort);
    }

    public function begin_group($type)
    {
        if (!$this->check_sanity())
        {
            return false;
        }
        $this->qb_count->begin_group($type);
        return $this->qb->begin_group($type);
    }

    public function end_group()
    {
        if (!$this->check_sanity())
        {
            return false;
        }
        $this->qb_count->end_group();
        return $this->qb->end_group();
    }

    public function include_deleted()
    {
        $this->qb_count->include_deleted();
        return $this->qb->include_deleted();
    }

    public function set_lang($lang)
    {
        if (!$this->check_sanity())
        {
            return false;
        }
        $this->qb_count->set_lang($lang);
        return $this->qb->set_lang($lang);
    }

    public function count()
    {
        if (!$this->check_sanity())
        {
            return false;
        }
        if (   !$this->count
            || $this->count_mode != 'count')
        {
            $this->count = $this->qb_count->count();
        }
        $this->count_mode = 'count';
        return $this->count;
    }
}
?>