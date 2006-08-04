<?php
/**
 * @package org.openpsa.reports
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: viewer.php,v 1.3 2006/05/29 14:13:13 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.reports site interface class.
 * 
 * Reporting interfaces to various org.openpsa components
 */
class org_openpsa_reports_viewer extends midcom_baseclasses_components_request
{
    var $_datamanagers = array();
    var $_projects_handler = null;
    /*
    var $_contacts_handler = null;
    */
    
    /**
     * Constructor.
     */
    function org_openpsa_reports_viewer($topic, $config)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        parent::midcom_baseclasses_components_request($topic, $config);

        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();

        // Load datamanagers for main classes
        $this->_initialize_datamanager('projects', $this->_config->get('schemadb_queryform_projects'));
        /*
        $this->_initialize_datamanager('contacts', $this->_config->get('schemadb_queryform_contacts'));
        */

        // Load handler classes
        $this->_projects_handler = new org_openpsa_reports_projects_handler(&$this->_datamanagers, &$this->_request_data);
        /*
        $this->_contacts_handler = new org_openpsa_reports_contacts_handler(&$this->_datamanagers, &$this->_request_data);
        */
        
        //TODO: Create the report switches dynamically (when having more than one handler class)

        // Match /projects/get
        $this->_request_switch[] = array(
            'fixed_args' => array ('projects', 'get'),
            'handler' => array(&$this->_projects_handler, 'report_generator_get'),
        );

        // Match /projects/<guid>/<filename>
        $this->_request_switch[] = array(
            'fixed_args' => 'projects',
            'variable_args' => 2,
            'handler' => array(&$this->_projects_handler, 'report_generator'),
        );

        // Match /projects/<guid>
        $this->_request_switch[] = array(
            'fixed_args' => 'projects',
            'variable_args' => 1,
            'handler' => array(&$this->_projects_handler, 'report_generator'),
        );

        // Match /projects
        $this->_request_switch[] = array(
            'fixed_args' => 'projects',
            'handler' => array(&$this->_projects_handler, 'query_form'),
        );

        /*
        // Match /contacts
        $this->_request_switch[] = array(
            'fixed_args' => 'contacts',        
            'handler' => array(&$this->_contacts_handler, 'query_form'),
        );
        */

        // Match /csv/<filename>
        $this->_request_switch[] = array(
            'fixed_args'    => 'csv',
            'variable_args' => 1,
            'handler'       => 'csv',
        );

        // Match /
        $this->_request_switch[] = array(
            'handler' => 'frontpage'
        );
        
        debug_pop();
        return true;
    }

    function _populate_toolbar()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        /*
        //Add icon for user settings
        $GLOBALS['org_openpsa_core_toolbar']->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'settings.html',
            MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('settings'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        */
        
        debug_pop();
        return true;
    }

    function _initialize_datamanager($type, $schemadb_snippet)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // Load schema database snippet or file
        debug_add("Loading Schema Database", MIDCOM_LOG_DEBUG);
        $schemadb_contents = midcom_get_snippet_content($schemadb_snippet);
        eval("\$schemadb = Array ( {$schemadb_contents} );");
        // Initialize the datamanager with the schema
        $this->_datamanagers[$type] = new midcom_helper_datamanager($schemadb);

        if (!$this->_datamanagers[$type]) {
            debug_pop();
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, "Datamanager could not be instantinated.");
            // This will exit. 	 
        }
        
        debug_pop();
        return true;
    }

    /**
     * The CSV handlers return a posted variable with correct headers
     */
    function _handler_csv($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if ( !isset($_POST['org_openpsa_reports_csv']) )
        {
            debug_add('Variable org_openpsa_reports_csv not set in _POST, aborting');
            debug_pop();
            return false;
        }
        
        //We're outputting CSV
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->cache->content->content_type('application/csv');
        
        debug_pop();
        return true;
    }

    function _show_csv($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        echo $_POST['org_openpsa_reports_csv'];
        
        debug_pop();
        return true;
    }
    
    function _handler_frontpage($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
                
        //TODO: Determine "best" report to redirect to, now goes to projects as it's the only one implemented.
        
        if ($no_op = true)
        {
            debug_pop();
            $_MIDCOM->relocate( $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                                . 'projects/');
            //This will exit
        }

        debug_pop();
        return true;
    }
    
    
    /**
     * We will no longer hit this method, but kept in case we decided redirecting is stupid or something...
     */
    function _show_frontpage($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        midcom_show_style("show-frontpage");
        
        debug_pop();
        return true;
    }
}
