<?php
/**
 * @package midcom.helper.replicator
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: acl_editor.php 4207 2006-09-26 08:41:44Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Basic handler class
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_manager extends midcom_baseclasses_components_handler
{
    /**
     * The current local configuration.
     *
     * @var midcom_helper_configuration
     */
    var $_local_config;

    /**
     * The object we're managing
     *
     * @var object
     * @access private
     */
    var $_subscription = null;

    /**
     * The Datamanager of the member to display
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The Controller of the member used for editing
     *
     * @var midcom_helper_datamanager2_controller_simple
     * @access private
     */
    var $_controller = null;

    /**
     * The schema database in use, available only while a datamanager is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The schema to use for the new subscription.
     *
     * @var string
     * @access private
     */
    var $_schema = 'default';

    /**
     * The defaults to use for the new subscription.
     *
     * @var Array
     * @access private
     */
    var $_defaults = Array();

    function midcom_helper_replicator_manager()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function get_plugin_handlers()
    {
        $_MIDCOM->load_library('midgard.admin.asgard');
        return array
        (
            'list' => array
            (
                'handler' => array('midcom_helper_replicator_manager', 'list'),
            ),
            'create' => array
            (
                'handler' => array('midcom_helper_replicator_manager', 'create'),
                'fixed_args' => 'create',
                'variable_args' => 1,
            ),
            'edit' => array
            (
                'handler' => array('midcom_helper_replicator_manager', 'edit'),
                'fixed_args' => 'edit',
                'variable_args' => 1,
            ),
            'object' => array
            (
                'handler' => array('midcom_helper_replicator_manager', 'object'),
                'fixed_args' => 'object',
                'variable_args' => 1,
            ),
        );
    }

    function _on_initialize()
    {
        $_MIDCOM->load_library('midcom.helper.replicator');
        $_MIDCOM->load_library('midcom.helper.datamanager2');

        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midcom.helper.replicator');

        $component_data =& $GLOBALS['midcom_component_data']['midcom.helper.replicator'];
        $this->_local_config = $component_data['config'];

        $this->_load_schemadb();

        midgard_admin_asgard_plugin::prepare_plugin('', &$this->_request_data);
        foreach (array_keys($this->_schemadb) as $name)
        {
            if (preg_match('/midcom.helper.replicator\/create\//', $_MIDGARD['uri']))
            {
                $prefix = '';
            }
            else
            {
                $prefix = 'create/';
            }

            $this->_request_data['asgard_toolbar']->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "{$prefix}{$name}.html",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n_midcom->get('create %s'),
                        $_MIDCOM->i18n->get_string($this->_schemadb[$name]->description, 'midcom.helper.replicator')
                    ),
                    MIDCOM_TOOLBAR_ICON => 'midcom.helper.replicator/replicate-server-16.png',
                )
            );
        }
    }

    function _update_breadcrumb($handler_id)
    {
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => '__mfa/asgard_midcom.helper.replicator/',
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('replication subscriptions', 'midcom.helper.replicator'),
        );

        switch ($handler_id)
        {
            case '____mfa-asgard_midcom.helper.replicator-edit':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard_midcom.helper.replicator/edit/{$this->_subscription->guid}/",
                    MIDCOM_NAV_NAME => sprintf($this->_l10n_midcom->get('edit %s: %s'), $this->_request_data['view_type'], $this->_subscription->title),
                );
                break;
            case '____mfa-asgard_midcom.helper.replicator-create':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard_midcom.helper.replicator/create/{$this->_schema}.html",
                    MIDCOM_NAV_NAME => $this->_request_data['view_title'],
                );
                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * Loads and prepares the schema database.
     *
     * Special treatment is done for the name field, which is set readonly for non-creates
     * if the simple_name_handling config option is set. (using an auto-generated urlname based
     * on the title, if it is missing.)
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database('file:/midcom/helper/replicator/config/schemadb_default.inc');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_admin_user();

        $qb = midcom_helper_replicator_subscription_dba::new_query_builder();
        $qb->add_order('title');
        $data['subscriptions'] = $qb->execute();

        $this->_update_breadcrumb($handler_id);

        $data['view_title'] = $_MIDCOM->i18n->get_string('replication subscriptions', 'midcom.helper.replicator');
        $_MIDCOM->set_pagetitle($data['view_title']);

        return true;
    }

    function _show_list($handler_id, &$data)
    {
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');

        $data['schemadb'] =& $this->_schemadb;
        $data['local_config'] =& $this->_local_config;
        midcom_show_style('midcom-helper-replicator-list');

        midcom_show_style('midgard_admin_asgard_footer');

    }

    /**
     * Internal helper, loads the controller for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_subscription, $this->_subscription->exporter);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for article {$this->_article->id}.");
            // This will exit.
        }
    }

    /**
     * Internal helper, fires up the creation mode controller. Any error triggers a 500.
     *
     * @access private
     */
    function _load_create_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = $this->_schema;
        $this->_controller->defaults = $this->_defaults;
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }

    function _modify_schema_for_object($subscription)
    {
        $transporter = midcom_helper_replicator_transporter::create($subscription);
        $transporter->add_ui_options($this->_schemadb[$subscription->exporter]);
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_subscription = new midcom_helper_replicator_subscription_dba($args[0]);
        if (!$this->_subscription)
        {
            return false;
        }
        $this->_subscription->require_do('midgard:update');

        $_MIDCOM->bind_view_to_object($this->_subscription, $this->_subscription->exporter);

        $this->_modify_schema_for_object($this->_subscription);

        // Load the datamanager controller
        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                $_MIDCOM->relocate("__mfa/asgard_midcom.helper.replicator/edit/{$this->_subscription->guid}/");
                // This will exit.

            case 'cancel':
                $_MIDCOM->relocate('__mfa/asgard_midcom.helper.replicator/');
                // This will exit.
        }

        $data['view_type'] = $_MIDCOM->i18n->get_string($this->_schemadb[$this->_subscription->exporter]->description, 'midcom.helper.replicator');

        $this->_update_breadcrumb($handler_id);

        $data['view_title'] = $this->_subscription->title;
        $_MIDCOM->set_pagetitle($data['view_title']);

        return true;
    }

    function _show_edit($handler_id, &$data)
    {
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');

        $data['controller'] =& $this->_controller;
        midcom_show_style('midcom-helper-replicator-edit');

        midcom_show_style('midgard_admin_asgard_footer');
    }

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function & dm2_create_callback (&$controller)
    {
        $this->_subscription = new midcom_helper_replicator_subscription_dba();
        $this->_subscription->exporter = $this->_schema;

        if (! $this->_subscription->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_subscription);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to create a new subscription, cannot continue. Error: " . mgd_errstr());
            // This will exit.
        }

        return $this->_subscription;
    }

    /**
     * Displays an article edit view.
     *
     * Note, that the article for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation article
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_admin_user();

        $this->_schema = $args[0];
        if (!array_key_exists($this->_schema, $this->_schemadb))
        {
            return false;
        }
        $this->_schema =& $this->_schema;

        $this->_load_create_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                $_MIDCOM->relocate("__mfa/asgard_midcom.helper.replicator/edit/{$this->_subscription->guid}/");
                // This will exit.

            case 'cancel':
                $_MIDCOM->relocate('__mfa/asgard_midcom.helper.replicator/');
                // This will exit.
        }

        $data['view_title'] = sprintf($this->_l10n_midcom->get('create %s'), $this->_schemadb[$this->_schema]->description);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");

        $this->_update_breadcrumb($handler_id);

        return true;
    }

    function _show_create($handler_id, &$data)
    {
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');

        $data['controller'] =& $this->_controller;
        midcom_show_style('midcom-helper-replicator-create');

        midcom_show_style('midgard_admin_asgard_footer');
    }

    function _resolve_object_title($object)
    {
        $vars = get_object_vars($object);

        if (   array_key_exists('title', $vars)
            && $object->title)
        {
            return $object->title;
        }
        elseif (array_key_exists('name', $vars))
        {
            return $object->name;
        }
        else
        {
            return "#{$object->id}";
        }
    }

    /**
     * Displays replication information for an object
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_object($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_admin_user();

        $bind_toolbar = true;
        $data['object'] = $_MIDCOM->dbfactory->get_object_by_guid($args[0]);
        if (!$data['object'])
        {
            $bind_toolbar = false;
            // Fallback to midgard core to find deleted object
            foreach($_MIDGARD['schema']['types'] as $mgdschema_class => $dummy)
            {
                if (empty($mgdschema_class))
                {
                    continue;
                }
                $qb = new midgard_query_builder($mgdschema_class);
                $qb->add_constraint('guid', '=', $args[0]);
                $qb->include_deleted();
                $objects = $qb->execute();
                if (empty($objects))
                {
                    continue;
                }
                $data['object'] = $objects[0];
                break;
            }
        }
        if (!$data['object'])
        {
            return false;
        }


        if ($bind_toolbar)
        {
            $_MIDCOM->bind_view_to_object($data['object']);
        }
        $data['language_code'] = '';
        midgard_admin_asgard_plugin::bind_to_object($data['object'], $handler_id, &$data);

        $data['view_title'] = sprintf($_MIDCOM->i18n->get_string('replication information for %s', 'midcom.helper.replicator'), $this->_resolve_object_title($data['object']));
        $_MIDCOM->set_pagetitle($data['view_title']);

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/midcom.helper.replicator/replicator.css",
            )
        );

        return true;
    }

    function _show_object($handler_id, &$data)
    {
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');

        midcom_show_style('midcom-helper-replicator-object');

        midcom_show_style('midgard_admin_asgard_footer');

    }
}
?>