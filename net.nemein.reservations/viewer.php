<?php
/**
 * @package net.nemein.reservations
 * @author The Midgard Project, http://www.midgard-project.net
 * @copyright The Midgard Project, http://www.midgard-project.net
 * @license http://www.gnu.net/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module. 
 * 
 * @package net.nemein.reservations
 */
class net_nemein_reservations_viewer extends midcom_baseclasses_components_request
{
    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
    }

    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {
        // *** Prepare the request switch ***
        /*  */
        $this->_request_switch['config'] = array
        (
            'handler' => array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/net/nemein/reservations/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => array('config'),
        );

        // Handle /create/<schema>
        $this->_request_switch['create_resource'] = array
        (
            'handler' => array('net_nemein_reservations_handler_create', 'create'),
            'fixed_args' => array('create'),
            'variable_args' => 1,
        );
        
        // Handle /edit/<resource>
        $this->_request_switch['edit_resource'] = array
        (
            'handler' => array('net_nemein_reservations_handler_admin', 'edit'),
            'fixed_args' => array('edit'),
            'variable_args' => 1,
        );

        // Handle /delete/<resource>
        $this->_request_switch['delete_resource'] = array
        (
            'handler' => array('net_nemein_reservations_handler_admin', 'delete'),
            'fixed_args' => array('delete'),
            'variable_args' => 1,
        );
        
        // Handle /reservation/create/<resource>
        $this->_request_switch['create_reservation'] = array
        (
            'handler' => array('net_nemein_reservations_handler_reservation_create', 'create'),
            'fixed_args' => array('reservation', 'create'),
            'variable_args' => 1,
        );
        
        // Handle /reservation/create/<resource>/<date>
        $this->_request_switch['create_reservation_date'] = array
        (
            'handler' => array('net_nemein_reservations_handler_reservation_create', 'create'),
            'fixed_args' => array('reservation', 'create'),
            'variable_args' => 2,
        );

        // Handle /reservation/list/<date>
        $this->_request_switch['list_reservations_date'] = array
        (
            'handler' => array('net_nemein_reservations_handler_reservation_list', 'list'),
            'fixed_args' => array('reservation', 'list'),
            'variable_args' => 1,
        );

        // Handle /reservation/list
        $this->_request_switch['list_reservations'] = array
        (
            'handler' => array('net_nemein_reservations_handler_reservation_list', 'list'),
            'fixed_args' => array('reservation', 'list'),
        );
        
        // Handle /reservation/edit/<reservation>
        $this->_request_switch['edit_reservation'] = array
        (
            'handler' => array('net_nemein_reservations_handler_reservation_admin', 'edit'),
            'fixed_args' => array('reservation', 'edit'),
            'variable_args' => 1,
        );

        // Handle /reservation/delete/<reservation>
        $this->_request_switch['delete_reservation'] = array
        (
            'handler' => array('net_nemein_reservations_handler_reservation_admin', 'delete'),
            'fixed_args' => array('reservation', 'delete'),
            'variable_args' => 1,
        );
        
        // Handle /reservation/repeat/<reservation>
        $this->_request_switch['repeat_reservation'] = array
        (
            'handler' => array ('net_nemein_reservations_handler_reservation_repeat', 'repeat'),
            'fixed_args' => array ('reservation', 'repeat'),
            'variable_args' => 1,
        );
        
        // Handle /reservation/<resource>
        $this->_request_switch['view_reservation'] = array
        (
            'handler' => array('net_nemein_reservations_handler_reservation_view', 'view'),
            'fixed_args' => array('reservation'),
            'variable_args' => 1,
        );
        
        // Handle /view/<resource>
        $this->_request_switch['view_now'] = array
        (
            'handler' => array('net_nemein_reservations_handler_resource', 'view'),
            'fixed_args' => array('view'),
            'variable_args' => 1,
        );

        // Handle /
        $this->_request_switch['index'] = array
        (
            'handler' => array('net_nemein_reservations_handler_resource', 'list'),
        );

        $_MIDCOM->add_link_head(array(
            'rel' => 'stylesheet',
            'type' => 'text/css',
            'href' => MIDCOM_STATIC_URL . '/net.nemein.reservations/reservations.css',
        ));
    }
    
    /**
     * Populates the node toolbar depending on the user's rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {
        if ($this->_topic->can_do('midgard:create'))
        {
            foreach (array_keys($this->_request_data['schemadb_resource']) as $name)
            {
                $this->_node_toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "create/{$name}.html",
                        MIDCOM_TOOLBAR_LABEL => sprintf
                        (
                            $this->_l10n_midcom->get('create %s'),
                            $this->_l10n->get($this->_request_data['schemadb_resource'][$name]->description)
                        ),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                        MIDCOM_TOOLBAR_ACCESSKEY => 'n',
                    )
                );
            }
        }
        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'config.html',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
        }
        if ($GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']->can_do('midcom:privileges'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__ais/acl/edit/{$GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('root event privileges'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('root event privileges helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
        }
    }
    
    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        $this->_request_data['schemadb_resource'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_resource'));
        $this->_request_data['schemadb_reservation'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_reservation'));

        $this->_populate_node_toolbar();

        $_MIDCOM->add_link_head
        (
            array
            (
                'type' => 'text/css',
                'rel'  => 'stylesheet',
                'href' => MIDCOM_STATIC_URL . '/net.nemein.reservations/reservations.css',
            )
        );
        
        return true;
    }
    
    function load_resource($arg)
    {
        if (version_compare(mgd_version(), '1.8.0alpha1', '>='))
        {
            debug_add('1.8.x detected, doing with single QB');
            // 1.8 allows us to do this the easy way
            $qb = org_openpsa_calendar_resource_dba::new_query_builder();
            $qb->begin_group('OR');
                $qb->add_constraint('name', '=', $arg);
                $qb->add_constraint('guid', '=', $arg);
            $qb->end_group();
            $resources = $qb->execute();
            if (count($resources) > 0)
            {
                return $resources[0];
            }
        }
        else
        {
            debug_add('1.7.x detected, doing separate checks');
            // 1.7 requires that we check for guid and name separately
            debug_add('Trying to fetch with name');
            $qb = org_openpsa_calendar_resource_dba::new_query_builder();
            $qb->add_constraint('name', '=', $arg);
            $resources = $qb->execute();
            if (count($resources) > 0)
            {
                return $resources[0];
            }
            elseif (mgd_is_guid($arg))
            {
                debug_add('mgd_is_guid returned true, trying to fetch with guid');
                $resource = new org_openpsa_calendar_resource_dba($arg);
                if (   is_object($resource)
                    && is_a($resource, 'org_openpsa_calendar_resource_dba'))
                {
                    return $resource;
                }
            }
        }
        return false;
    }
}
?>