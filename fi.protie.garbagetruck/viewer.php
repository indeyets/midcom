<?php
/**
* @package fi.protie.garbagetruck
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * Viewer class for fi.protie.garbagetruck.
 * 
 * @package fi.protie.garbagetruck
 */
class fi_protie_garbagetruck_viewer extends midcom_baseclasses_components_request
{
    /**
     * Collect the schemas here
     * 
     * @access private
     * @var Array
     */
    var $_schemadb = array ();
    
    /**
     * An instance of Navigation Access Point or class midcom_helper_nav
     * 
     * @access private
     * @var midcom_helper_nav
     */
    var $_nap = null;
    
    /**
     * Simple constructor.
     * 
     * @access protected
     */
    function fi_protie_garbagetruck_viewer ($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }
    
    /**
     * Define the request switches
     * 
     * @access private
     */
    function _on_initialize()
    {
        // Welcome page
        // Match /
        $this->_request_switch['welcome'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_area', 'list'),
        );
        
        // Area listing
        // Match /area/
        $this->_request_switch['area_list'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_area', 'list'),
            'fixed_args'    => array ('area'),
        );
        
        // Create a new area
        // Match /area/create/
        $this->_request_switch['area_create'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_area', 'create'),
            'fixed_args'    => array ('area', 'create'),
        );
        
        // View an area
        // Match /area/<area guid>/
        $this->_request_switch['area_view'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_area', 'view'),
            'fixed_args'    => array ('area'),
            'variable_args' => 1,
        );
        
        // Edit the requested area
        // Match /area/<area guid>/<action/
        $this->_request_switch['area_edit'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_area', 'action'),
            'fixed_args'    => array ('area'),
            'variable_args' => 2,
        );
        
        // Vehicle listing
        // Match /vehicle/
        $this->_request_switch['vehicle_list'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_vehicle', 'list'),
            'fixed_args'    => array ('vehicle'),
        );
        
        // Create a new vehicle
        // Match /vehicle/create/
        $this->_request_switch['vehicle_create'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_vehicle', 'create'),
            'fixed_args'    => array ('vehicle', 'create'),
        );
        
        // View an vehicle
        // Match /vehicle/<vehicle guid>/
        $this->_request_switch['vehicle_view'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_vehicle', 'view'),
            'fixed_args'    => array ('vehicle'),
            'variable_args' => 1,
        );
        
        // Edit the requested vehicle
        // Match /vehicle/<vehicle guid>/<action/
        $this->_request_switch['vehicle_edit'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_vehicle', 'edit'),
            'fixed_args'    => array ('vehicle'),
            'variable_args' => 2,
        );
        
        // View all of the routes
        // Match /route/
        $this->_request_switch['routes_list'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_route', 'list'),
            'fixed_args'    => array ('route'),
            'variable_args' => 0,
        );
        
        // View all of the routes
        // Match /route/list/
        $this->_request_switch['routes_list_2'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_route', 'list'),
            'fixed_args'    => array ('route', 'list'),
            'variable_args' => 0,
        );
        
        // View the routes for a certain area
        // Match /route/list/<area guid>/
        $this->_request_switch['routes_list_specified'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_route', 'list'),
            'fixed_args'    => array ('route', 'list'),
            'variable_args' => 1,
        );
        
        // Create a route
        // Match /route/create/<area guid>/
        $this->_request_switch['route_create'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_route', 'create'),
            'fixed_args'    => array ('route', 'create'),
            'variable_args' => 1,
        );
        
        // View a route
        // Match /route/<route guid>/
        $this->_request_switch['route_view'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_route', 'view'),
            'fixed_args'    => array ('route'),
            'variable_args' => 1,
        );
        
        // Edit a route
        // Match /route/<route guid>/<action>/
        $this->_request_switch['route_edit'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_route', 'edit'),
            'fixed_args'    => array ('route'),
            'variable_args' => 2,
        );
        
        // View person records
        // Match /person/create/
        $this->_request_switch['person_create'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_person', 'create'),
            'fixed_args'    => array ('person', 'create'),
            'variable_args' => 0,
        );
        
        // View person records
        // Match /person/<person guid>/
        $this->_request_switch['person_view'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_person', 'view'),
            'fixed_args'    => array ('person'),
            'variable_args' => 1,
        );
        
        // View person records
        // Match /person/<person guid>/<action>/
        $this->_request_switch['person_edit'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_person', 'edit'),
            'fixed_args'    => array ('person',),
            'variable_args' => 2,
        );
        
        // View log query form
        // Match /log/
        $this->_request_switch['log_query'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_query', 'query'),
            'fixed_args'    => array ('log'),
        );
        
        // View log query form
        // Match /log/results/
        $this->_request_switch['log_display'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_query', 'results'),
            'fixed_args'    => array ('log', 'results'),
        );
        
        // Create a log entry by selecting the route with universal chooser
        // Match /log/create/
        $this->_request_switch['log_create'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_log', 'create'),
            'fixed_args'    => array ('log', 'create'),
        );
        
        // Create a log entry for a specified route
        // Match /log/create/<route guid>/
        $this->_request_switch['log_create_route'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_log', 'create'),
            'fixed_args'    => array ('log', 'create'),
            'variable_args' => 1,
        );
        
        // View a single log entry
        // Match /log/<log guid>/
        $this->_request_switch['log_view'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_log', 'view'),
            'fixed_args'    => array ('log'),
            'variable_args' => 1,
        );
        
        // Edit or delete a single log entry
        // Match /log/<log guid>/<action>
        $this->_request_switch['log_edit'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_log', 'edit'),
            'fixed_args'    => array ('log'),
            'variable_args' => 2,
        );
        
        // Delete request for any used object type
        // Match /delete/<db type>/<db guid>/
        $this->_request_switch['delete'] = array
        (
            'handler'       => array ('fi_protie_garbagetruck_handler_delete', 'delete'),
            'fixed_args'    => array ('delete'),
            'variable_args' => 2,
        );
        
        // Configuration
        $this->_request_switch['config'] = array
        (
            'handler' => 'config_dm',
            'fixed_args' => array ('config'),
            'schemadb' => 'file:/fi/protie/garbagetruck/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => false
        );
    }
    
    /**
     * Load the schemas
     * 
     * @access private
     */
    function _on_handle($handler, $args)
    {
        $this->_request_data['rounding_precision'] = $this->_config->get('rounding_precision');
        
        $this->_request_data['schemadb_log'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_log'));
        $this->_request_data['schemadb_area'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_area'));
        $this->_request_data['schemadb_route'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_route'));
        $this->_request_data['schemadb_query'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_query'));
        $this->_request_data['schemadb_person'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_person'));
        $this->_request_data['schemadb_vehicle'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_vehicle'));
        
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL.'/fi.protie.garbagetruck/garbagetruck.css',
            )
        );
        
        return true;
    }
}
?>