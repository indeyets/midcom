<?php
/**
 * @package fi.mik.lentopaikkakisa
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Discussion forum create post handler
 *
 * @package fi.mik.lentopaikkakisa
 */

class fi_mik_lentopaikkakisa_handler_report extends midcom_baseclasses_components_handler
{
    /**
     * The report which has been created
     *
     * @var fi_mik_lentopaikkakisa_report_dba
     * @access private
     */
    var $_report = null;

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
     * The schema to use for the new article.
     *
     * @var string
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
        $this->_request_data['thread'] =& $this->_thread;
        $this->_request_data['parent_post'] =& $this->_parent_post;        
        $this->_request_data['schema'] =& $this->_schema;
        $this->_request_data['schemadb'] =& $this->_schemadb;
    }


    /**
     * Simple default constructor.
     */
    function fi_mik_lentopaikkakisa_handler_report()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Loads and prepares the schema database.
     *
     * Special treatement is done for the name field, which is set readonly for non-creates
     * if the simple_name_handling config option is set. (using an auto-generated urlname based
     * on the title, if it is missing.)
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        // TODO: Be extra smart here about populating/hiding fields
        /*if ($_MIDCOM->auth->user)
        {
            $user =& $_MIDCOM->auth->user->get_storage();
            foreach (array_keys($this->_schemadb) as $name)
            {
                $this->_schemadb[$name]->fields['reporter']['readonly'] = true;
                $this->_defaults['reporter'] = $user->name;
            }
        }*/
        $this->_defaults['date'] = time();
        $this->_defaults['aerodrome'] = 'EFHF';
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
        $this->_report = new fi_mik_lentopaikkakisa_report_dba();

        if (! $this->_report->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_report);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new post, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_report;
    }

    /**
     * Displays a report edit view.
     */
    function _handler_new($handler_id, $args, &$data)
    {
        // FIXME: This doesn't work for some reason
        $_MIDCOM->auth->require_user_do('midgard:create', null, 'fi_mik_lentopaikkakisa_report_dba');
        $_MIDCOM->auth->require_valid_user();

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Index the article
                //$indexer =& $_MIDCOM->get_service('indexer');
                //fi_mik_lentopaikkakisa_viewer::index($this->_controller->datamanager, $indexer, $this->_thread);

                // *** FALL THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
                // This will exit.
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_pagetitle($this->_l10n->get('report flight'));

        return true;
    }

    /**
     * Shows the loaded report editor
     */
    function _show_new($handler_id, &$data)
    {
        midcom_show_style('report-widget');
    }
}
?>