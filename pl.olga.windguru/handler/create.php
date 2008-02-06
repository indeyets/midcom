<?php
/**
 * @package pl.olga.windguru
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: create.php 11276 2007-07-19 20:42:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * p.o.windguru create page handler
 *
 * @package pl.olga.windguru
 */
class pl_olga_windguru_handler_create extends midcom_baseclasses_components_handler
{

    /**
     * The article which has been created
     *
     * @var midcom_db_article
     * @access private
     */
    var $_article = null;

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
    function pl_olga_windguru_handler_create()
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
        $this->_controller->schemaname = 'default';
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
        $this->_article = new midcom_db_article();
        $this->_article->topic = $this->_topic->id;

        if (! $this->_article->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_article);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new spot, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_article;
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
     * @return boolean Indicating success.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:create');

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                if ($_MIDCOM->serviceloader->can_load('midcom_core_service_urlgenerator'))
                {
                    $urlgenerator = $_MIDCOM->serviceloader->load('midcom_core_service_urlgenerator');
                    $this->_article->name = $urlgenerator->from_string($this->_article->title);
                    $this->_article->update();
                }
                else
                {
                    $this->_article->name = $this->_article->title;
                }

                $_MIDCOM->relocate("{$this->_article->name}.html");
                // This will exit.

            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit.
        }

        $this->_prepare_request_data();
        //$_MIDCOM->set_26_request_metadata($this->_article->revised, $this->_article->guid);
        $title = $this->_l10n_midcom->get('create spot');
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");

        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "create.html",
            MIDCOM_NAV_NAME => $this->_l10n_midcom->get('create %s'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * Shows the loaded article.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_create ($handler_id, &$data)
    {
        midcom_show_style('spot-create');
    }



}

?>