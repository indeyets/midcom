<?php
/**
 * @package org.openpsa.directmarketing
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: viewer.php,v 1.18 2006/07/06 15:48:43 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.directmarketing site interface class.
 * 
 * Direct marketing and mass mailing lists
 */
class org_openpsa_directmarketing_viewer extends midcom_baseclasses_components_request
{
    var $_datamanagers = array();
    var $_campaign_handler = null;
    var $_message_handler = null;
    var $_logger_handler = null;
    var $_toolbars;

    function org_openpsa_directmarketing_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);

        $this->_toolbars =& midcom_helper_toolbars::get_instance();

        // Load datamanagers for main classes
        $this->_initialize_datamanager('campaign', $this->_config->get('schemadb_campaign'));        
        $this->_initialize_datamanager('message', $this->_config->get('schemadb_message'));                

        // Load handler classes
        $this->_campaign_handler = new org_openpsa_directmarketing_campaign_handler(&$this->_datamanagers, &$this->_request_data, &$this->_config);
        $this->_request_data['campaign_handler'] = &$this->_campaign_handler;
        $this->_message_handler = new org_openpsa_directmarketing_message_handler(&$this->_datamanagers, &$this->_request_data, &$this->_config);
        $this->_request_data['message_handler'] = &$this->_message_handler;
        $this->_logger_handler = new org_openpsa_directmarketing_logger_handler(&$this->_datamanagers, &$this->_request_data, &$this->_config);
        $this->_request_data['logger_handler'] = &$this->_logger_handler;
        
        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();

        // Match /message/new/<campaign guid>/<schema>
        $this->_request_switch[] = array(
            'fixed_args' => Array('message','new'),
            'variable_args' => 2,                        
            'handler' => array(&$this->_message_handler,'new'),
        );

        // Match /message/list/<type>/<guid>
        $this->_request_switch[] = array(
            'fixed_args' => Array('message','list'),
            'variable_args' => 2,            
            'handler' => array(&$this->_message_handler,'list'),
        );
        
        // Match /campaign/import/<guid>
        $this->_request_switch[] = array(
            'fixed_args' => Array('campaign', 'import'),
            'variable_args' => 1,            
            'handler' => Array('org_openpsa_directmarketing_handler_import', 'index'),
        );

        // Match /campaign/import/simpleemails/<guid>
        $this->_request_switch[] = array(
            'fixed_args' => Array('campaign', 'import', 'simpleemails'),
            'variable_args' => 1,            
            'handler' => Array('org_openpsa_directmarketing_handler_import', 'simpleemails'),
        );

        // Match /campaign/import/simpleemails/<guid>
        $this->_request_switch[] = array(
            'fixed_args' => Array('campaign', 'import', 'vcards'),
            'variable_args' => 1,            
            'handler' => Array('org_openpsa_directmarketing_handler_import', 'vcards'),
        );

        // Match /message/send_bg/<message GUID>/<batch number>/<job guid>
        $this->_request_switch[] = array(
            'fixed_args' => array('message', 'send_bg'),
            'variable_args' => 3,
            'handler' => array(&$this->_message_handler,'send_bg'),
        );

        // Match /message/compose/<message_guid>
        $this->_request_switch[] = array(
            'fixed_args' => array('message', 'compose'),
            'variable_args' => 1,
            'handler' => array(&$this->_message_handler,'compose'),
        );

        // Match /message/<message GUID>/<action>/<param>
        $this->_request_switch[] = array(
            'fixed_args' => 'message',
            'variable_args' => 3,
            'handler' => array(&$this->_message_handler,'action'),
        );

        // Match /message/<message GUID>/<action>
        $this->_request_switch[] = array(
            'fixed_args' => 'message',
            'variable_args' => 2,
            'handler' => array(&$this->_message_handler,'action'),
        );


        // Match /message/<message GUID>
        $this->_request_switch[] = array(
            'fixed_args' => 'message',
            'variable_args' => 1,
            'handler' => array(&$this->_message_handler,'view'),
        );

        // Match /campaign/new
        $this->_request_switch[] = array(
            'fixed_args' => Array('campaign','new'),
            'handler' => array(&$this->_campaign_handler,'new'),
        );

        // Match /campaign/list
        $this->_request_switch[] = array(
            'fixed_args' => Array('campaign','list'),
            'handler' => array(&$this->_campaign_handler,'list'),
        );
        
        // Match /campaign/list/<person GUID>
        $this->_request_switch[] = array(
            'fixed_args' => Array('campaign','list'),
            'handler' => array(&$this->_campaign_handler,'list'),
            'variable_args' => 1,            
        );        

        // Match /campaign/unsubscribe/<member GUID>
        $this->_request_switch[] = array(
            'fixed_args' => Array('campaign','unsubscribe'),
            'handler' => array(&$this->_campaign_handler,'unsubscribe'),
            'variable_args' => 1,
        );
        
        // Match /campaign/unsubscribe_all/<person GUID>
        $this->_request_switch[] = array(
            'fixed_args' => Array('campaign','unsubscribe_all'),
            'handler' => array(&$this->_campaign_handler,'unsubscribe_all'),
            'variable_args' => 1,
        );

        // Match /campaign/<campaign GUID>/<action>
        $this->_request_switch[] = array(
            'fixed_args' => Array('campaign'),
            'handler' => array(&$this->_campaign_handler,'action'),
            'variable_args' => 2,            
        );        

        // Match /campaign/<campaign GUID>
        $this->_request_switch[] = array(
            'fixed_args' => 'campaign',
            'variable_args' => 1,
            'handler' => array(&$this->_campaign_handler,'view'),
        );

        // Match /logger/bounce
        $this->_request_switch[] = array(
            'fixed_args' => array ('logger', 'bounce'),
            'handler' => array(&$this->_logger_handler, 'bounce'),
        );

        // Match /logger/link
        $this->_request_switch[] = array(
            'fixed_args' => array ('logger', 'link'),
            'handler' => array(&$this->_logger_handler, 'link'),
        );

        // Match /logger/redirect/<TOKEN>/<URL>
        $this->_request_switch[] = array(
            'fixed_args' => array ('logger', 'redirect'),
            'variable_args' => 2,
            'handler' => array(&$this->_logger_handler, 'redirect'),
        );

        // Match /logger/redirect/<TOKEN>
        $this->_request_switch[] = array(
            'fixed_args' => array ('logger', 'redirect'),
            'variable_args' => 1,
            'handler' => array(&$this->_logger_handler, 'redirect'),
        );

        $this->_request_switch['config'] = Array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/org/openpsa/directmarketing/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );

        // Match /debug
        $this->_request_switch[] = array(
            'fixed_args' => 'debug',
            'handler' => 'debug'
        );

        // Match /
        $this->_request_switch[] = array(
            'handler' => 'frontpage'
        );
        
        // This component uses Ajax, include the handler javascripts
        $GLOBALS["midcom"]->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/ajaxutils.js");        
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
    }
    
    function _handler_frontpage($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_toolbars->top->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => 'campaign/new/',
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("create campaign"),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_people.png',
                MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_user_do('midgard:create', null, 'org_openpsa_directmarketing_campaign'),
            )
        );
        return true;
    }
    
    function _show_frontpage($handler_id, &$data)
    {
        midcom_show_style("show-frontpage");
    }

    function _handler_debug($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_request_data['config'] =& $this->_config;
        $this->_request_data['datamanagers'] =& $this->_datamanagers;
        return true;
    }
    
    function _show_debug($handler_id, &$data)
    {
        midcom_show_style("show-debug");
    }

}
?>
