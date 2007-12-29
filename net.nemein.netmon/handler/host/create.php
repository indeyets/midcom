<?php
/**
 * @package net.nemein.netmon
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: create.php 4505 2006-10-29 15:53:49Z tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * create host handler
 *
 * @package net.nemein.netmon
 */
class net_nemein_netmon_handler_host_create extends midcom_baseclasses_components_handler
{
    /**
     * The host which has been created
     *
     * @var midcom_db_host
     * @access private
     */
    var $_host = null;

    /**
     * The host unser which to create
     *
     * @var midcom_db_host
     * @access private
     */
    var $_parent = null;

    /**
     * The Controller of the article used for editing
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
     * The schema name in use, available only while a datamanager is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schema = null;

    /**
     * The defaults to use for the new article.
     *
     * @var Array
     * @access private
     */
    var $_defaults = Array();

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['controller'] =& $this->_controller;
    }


    /**
     * Simple default constructor.
     */
    function net_nemein_netmon_handler_host_create()
    {
        parent::midcom_baseclasses_components_handler();
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
        $this->_schemadb =& $this->_request_data['schemadb'];
    }

    /**
     * Internal helper, fires up the creation mode controller. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
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

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function &dm2_create_callback (&$controller)
    {
        $this->_host = new net_nemein_netmon_host_dba();
        $this->_host->name = $_POST['name'];

        if (! $this->_host->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_host);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a new host, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_host;
    }

    /**
     * Displays an article edit view.
     *
     * Note, that the article for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation article,
     */
    function _handler_create($handler_id, $args, &$data)
    {
        // todo: require_user_do
        $this->_schema = 'host';
        if (   isset($args[0])
            && !empty($args[0]))
        {
            $this->_parent = new net_nemein_netmon_host_dba($args[0]);
            if (!is_a($this->_parent, 'net_nemein_netmon_host_dba'))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not find parent host '{$args[0]}'");
                // this will exit
            }
        }
        if ($this->_parent)
        {
            $this->_defaults['parent'] = $this->_parent->id;
            $this->_schemadb[$this->_schema]->fields['parent']['readonly'] = true;
        }

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                /*
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nemein_netmon_viewer::index($this->_controller->datamanager, $indexer, $this->_topic);
                */

                $_MIDCOM->relocate("host/{$this->_host->guid}.html");
                // This will exit.
            case 'cancel':
                if ($this->_parent)
                {
                    $_MIDCOM->relocate("host/{$this->_parent->guid}.html");
                    // This will exit.
                }
                $_MIDCOM->relocate('');
                // This will exit.
        }

        $this->_prepare_request_data();
        if ( $this->_host != null ) 
        {
            $_MIDCOM->set_26_request_metadata($this->_host->revised, $this->_host->guid);
        }
        if ($this->_parent)
        {
            $title = sprintf($this->_l10n_midcom->get('create child host for %s'), $this->_parent->title);
        }
        else
        {
            $title = $this->_l10n_midcom->get('create host');
        }
        $this->_request_data['title'] = $title;
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line()
    {
        $tmp = Array();
        if ($this->_parent)
        {
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "host/{$this->_parent->guid}.html",
                MIDCOM_NAV_NAME => $this->_parent->title,
            );
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "create/host/{$this->_parent->guid}.html",
                MIDCOM_NAV_NAME => $this->_request_data['title'],
            );
        }
        else
        {
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "create/host.html",
                MIDCOM_NAV_NAME => $this->_request_data['title'],
            );
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * Shows the loaded article.
     */
    function _show_create ($handler_id, &$data)
    {
        midcom_show_style('admin-create-host');
    }



}

?>
