<?php
/**
 * @package net.nehmer.publications
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Publications create page handler
 *
 * @package net.nehmer.publications
 */

class net_nehmer_publications_handler_create extends midcom_baseclasses_components_handler
{
    /**
     * The publication which has been created
     *
     * @var net_nehmer_publications_entry
     * @access private
     */
    var $_publication = null;

    /**
     * The Controller of the publication used for editing
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
     * The schema to use for the new publication.
     *
     * @var string
     * @access private
     */
    var $_schema = null;

    /**
     * The defaults to use for the new publication.
     *
     * @var Array
     * @access private
     */
    var $_defaults = Array();

    /**
     * The list of default categories to which a new entry should be added to.
     *
     * @var Array
     * @access private
     */
    var $_default_categories = null;

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['schema'] =& $this->_schema;
        $this->_request_data['schemadb'] =& $this->_schemadb;
    }

    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Loads the topic filter list.
     */
    function _on_initialize()
    {
        $defaults = $this->_config->get('default_categories');
        if ($defaults)
        {
            $this->_default_categories = explode(':', $defaults);
        }
    }

    /**
     * Loads and prepares the schema database.
     *
     * All fields present in the topic filter list will be populated with the corresponding
     * defaults.
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb'];

        $hidden_fields_raw = $this->_config->get('hide_fields');
        if ($hidden_fields_raw)
        {
            $hidden_fields = explode(',', $hidden_fields_raw);
            foreach ($this->_schemadb as $schemaname => $copy)
            {
                foreach ($hidden_fields as $fieldname)
                {
                    $this->_schemadb[$schemaname]->fields[$fieldname]['hidden'] = true;
                    $this->_schemadb[$schemaname]->fields[$fieldname]['required'] = false;
                }
            }
        }
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
    function & dm2_create_callback (&$controller)
    {
        $this->_publication = new net_nehmer_publications_entry();

        if (! $this->_publication->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_publication);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new publication, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        foreach ($this->_default_categories as $category)
        {
            $this->_publication->add_to_category($category);
        }

        return $this->_publication;
    }

    /**
     * Displays a publication edit view.
     *
     * Note, that the publication for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation publication
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:create');

        $this->_schema = $args[0];

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Index the publication
                $indexer =& $_MIDCOM->get_service('indexer');
                $this->_publication->index($this->_controller->datamanager, $indexer, $this->_topic);
                // *** FALL THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit.
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_publication->guid);
        $title = sprintf($this->_l10n_midcom->get('create %s'), $this->_schemadb[$this->_schema]->description);
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

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "create/{$this->_schema}.html",
            MIDCOM_NAV_NAME => sprintf($this->_l10n_midcom->get('create %s'), $this->_schemadb[$this->_schema]->description),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * Shows the loaded publication.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_create ($handler_id, &$data)
    {
        midcom_show_style('admin-create');
    }



}

?>