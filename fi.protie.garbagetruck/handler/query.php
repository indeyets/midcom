<?php
/**
* @package fi.protie.garbagetruck
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * Query form related handlers
 * 
 * @package fi.protie.garbagetruck
 */
class fi_protie_garbagetruck_handler_query extends midcom_baseclasses_components_handler
{
    /**
     * Schema instance of the query form
     * 
     * @access private
     */
    var $_schemadb = null;
    
    /**
     * Object containing the selected route
     * 
     * @access private
     * @var fi_protie_garbagetruck_log_db
     */
    var $_log = null;
    
    /**
     * DM2 instance
     * 
     * @access private
     */
    var $_datamanager = null;
    
    /**
     * DM2 controller instance
     * 
     * @access private
     */
    var $_controller = null;
    
    /**
     * Collector for the query result values
     * 
     * @access private
     * @var array
     */
    var $_summary = array ();
    
    /**
     * Collector for the query result values
     * 
     * @access private
     * @var array
     */
    var $_average = array ();
    
    /**
     * Simple constructor, which calls for the parent constructor.
     * 
     * @access public
     */
    function fi_protie_garbagetruck_handler_query()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Simple collector, which creates the reference between the original instances and
     * request data for outputting the information.
     * 
     * @access private
     */
    function _populate_request_data()
    {
        $this->_request_data['route'] =& $this->_route;
        $this->_request_data['log'] =& $this->_log;
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }
    
    /**
     * Get the schemadbs connected to routes
     * 
     * @access private
     */
    function _on_initialize()
    {
        $this->_schemadb =& $this->_request_data['schemadb_query'];
        $this->_schemadb_log =& $this->_request_data['schemadb_log'];
    }
    
    /**
     * Load the query controller, which mainly helps with the widgets and the query form
     * 
     * @access private
     */
    function _load_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = 'query';
        $this->_controller->callback_object =& $this;
        
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }
    
    /**
     * DM2 creation callback
     */
    function &dm2_create_callback(&$controller)
    {
//        $_MIDCOM->relocate('log/results');
    }
    
    /**
     * Serialize the POST form and transform it into a GET string, which will be
     * passed on to the relocated /log/results/ request switch
     * 
     * @access private
     */
    function _convert_to_get()
    {
        $string = '';
        
        if (   array_key_exists('start', $_POST)
            && ereg('([0-9]{1,4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})', $_POST['start'], $regs))
        {
            $_POST['start'] = mktime((int) $regs[4], (int) $regs[5], (int) $regs[6], (int) $regs[2], (int) $regs[3], (int) $regs[1]);
        }
        
        if (array_key_exists('end', $_POST))
        {
            $_POST['end'] = mktime((int) $regs[4], (int) $regs[5], (int) $regs[6], (int) $regs[2], (int) $regs[3], (int) $regs[1]);
        }
        
        foreach ($this->_schemadb['query']->fields as $key => $array)
        {
            if (!array_key_exists($key, $_POST))
            {
                continue;
            }
            
            if (!is_array($_POST[$key]))
            {
                $string .= "{$key}={$_POST[$key]}&";
                continue;
            }
            
            $string .= "{$key}=serialized_".serialize($_POST[$key])."&";
        }
        
        return $string;
    }
    
    /**
     * Load the datamanager for the requested object.
     * 
     * @access private
     */
    function _load_log_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb_log);
        
        if (   !$this->_datamanager
            || !$this->_datamanager->autoset_storage($this->_request_data['log']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a Datamanager 2 instance for team {$this->_log->name}");
            // This will exit
        }
    }
    
    /**
     * Use the query string to get the results.
     * 
     * @access private
     */
    function _get_results()
    {
        $qb = fi_protie_garbagetruck_log_dba::new_query_builder();
        
        // Filter by requested start time
        if (   array_key_exists('start', $_GET)
            && $_GET['start'] != '-1')
        {
            $qb->add_constraint('recorddate', '>', $_GET['start']);
        }
        
        // Filter by requested end time
        if (   array_key_exists('end', $_GET)
            && $_GET['end'] != '-1')
        {
            $qb->add_constraint('recorddate', '<', $_GET['end']);
        }
        
        foreach ($_GET as $key => $value)
        {
            if (   $key == 'start'
                || $key == 'end')
            {
                continue;
            }
            
            if (substr($value, 0, 11) === 'serialized_')
            {
                $value = str_replace('serialized_', '', $value);
                
                $array = unserialize($value);
                
                $qb->begin_group('AND');
                foreach ($array as $id => $val)
                {
                    $qb->add_constraint($key, 'LIKE', "%{$id}%");
                }
                $qb->end_group();
                
                continue;
            }
            
            $qb->add_constraint($key, '=', $value);
            
        }
        
        $qb->add_order('recorddate');
        
        $results = @$qb->execute_unchecked();
        return $results;
    }
    
    /**
     * Simple handler for the log query form
     * 
     * @access private
     * @return boolean Indicating success
     */
    function _handler_query($handler_id, $args, &$data)
    {
        if (array_key_exists('midcom_helper_datamanager2_save', $_POST))
        {
            $_MIDCOM->relocate('log/results/?'.$this->_convert_to_get());
        }
        
        $this->_load_controller();
        
        $this->_component_data['active_leaf'] = FI_PROTIE_GARBAGETRUCK_LEAFID_LOG;
        
        if ($this->_topic->can_do('midgard:create'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'log/create/',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create a log entry'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new-event.png',
                )
            );
        }
        
        return true;
    }
    
    /**
     * Show the query form
     * 
     * @access private
     */
    function _show_query($handler_id, &$data)
    {
        $this->_populate_request_data();
        $this->_request_data['page_title'] = $this->_l10n->get('search logs');
        
        midcom_show_style('create_form');
    }
    
    /**
     * Results handler, which checks the requested query
     * 
     * @access private
     */
    function _handler_results($handler_id, $args, &$data)
    {
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "log/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('back to query form'),
//                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            )
        );
        
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL.'/fi.protie.garbagetruck/garbagetruck-print.css',
                'media' => 'print',
            )
        );
        
        $this->_component_data['active_leaf'] = FI_PROTIE_GARBAGETRUCK_LEAFID_LOG;
        
        return true;
    }
    
    /**
     * Show the results
     * 
     * @access private
     */
    function _show_results($handler_id, &$data)
    {
        $this->_request_data['page_title'] = $this->_l10n->get('query results');
        
        $this->_populate_request_data();
        
        $results = $this->_get_results();
        
        if (count($results) === 0)
        {
            midcom_show_style('results_empty');
            return;
        }
        
        midcom_show_style('results_header');
        
        
        foreach ($results as $i => $result)
        {
            $this->_request_data['log'] =& $result;
            $this->_load_log_datamanager();
            $this->_request_data['datamanager'] =& $this->_datamanager;
            $this->_view = $this->_datamanager->get_content_html();
            
            foreach ($this->_view as $key => $value)
            {
                if (!array_key_exists($key, $this->_summary))
                {
                    $this->_summary[$key] = 0;
                }
                
                if (   !is_numeric($value)
                    || trim($value) == '')
                {
                    $value = 0;
                }
                
                $this->_summary[$key] += (int) $value;
            }
            
            if (fmod($i, 2) == 0)
            {
                $this->_request_data['row_class'] = 'odd';
            }
            else
            {
                $this->_request_data['row_class'] = 'even';
            }
            
            midcom_show_style('results_item');
        }
        
        $this->_request_data['summary'] = $this->_summary;
        $this->_request_data['items'] = count($results);
        
        midcom_show_style('results_footer');
    }
}
?>