<?php
/**
 * @package net.fernmark.pedigree
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: create.php 4505 2006-10-29 15:53:49Z tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * create dog handler
 *
 * @package net.fernmark.pedigree
 */
class net_fernmark_pedigree_handler_dog_create extends midcom_baseclasses_components_handler
{
    /**
     * The dog which has been created
     *
     * @var midcom_db_dog
     * @access private
     */
    var $_dog = null;

    /**
     * The dog unser which to create
     *
     * @var midcom_db_dog
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
    function net_fernmark_pedigree_handler_dog_create()
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
    function & dm2_create_callback (&$controller)
    {
        $this->_dog = new net_fernmark_pedigree_dog_dba();
        $this->_dog->node = $this->_topic->id;

        if (! $this->_dog->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_dog);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a new dog, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_dog;
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
        $this->_topic->require_do('midgard:create');
        $this->_schema = 'dog';
        if (   isset($args[0])
            && !empty($args[0]))
        {
            $this->_parent = new net_fernmark_pedigree_dog_dba($args[0]);
            if (!is_a($this->_parent, 'net_fernmark_pedigree_dog_dba'))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not find parent dog '{$args[0]}'");
                // this will exit
            }
        }
        if ($this->_parent)
        {
            switch ($this->_parent->sex)
            {
                case NET_FERMARK_PEDIGREE_SEX_MALE:
                    $this->_defaults['sire'] = $this->_parent->id;
                    $this->_schemadb[$this->_schema]->fields['sire']['readonly'] = true;
                    break;
                case NET_FERMARK_PEDIGREE_SEX_FEMALE:
                    $this->_defaults['dam'] = $this->_parent->id;
                    $this->_schemadb[$this->_schema]->fields['dam']['readonly'] = true;
                    break;
            }
        }

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                /*
                $indexer =& $_MIDCOM->get_service('indexer');
                net_fernmark_pedigree_viewer::index($this->_controller->datamanager, $indexer, $this->_topic);
                */

                $_MIDCOM->relocate("dog/{$this->_dog->guid}.html");
                // This will exit.
            case 'cancel':
                if ($this->_parent)
                {
                    $_MIDCOM->relocate("dog/{$this->_parent->guid}.html");
                    // This will exit.
                }
                $_MIDCOM->relocate('');
                // This will exit.
        }

        $this->_prepare_request_data();
        if ( $this->_dog != null )
        {
            $_MIDCOM->set_26_request_metadata($this->_dog->revised, $this->_dog->guid);
        }
        if ($this->_parent)
        {
            $title = sprintf($this->_l10n_midcom->get('create offsrping for %s'), $this->_parent->name_with_kennel);
        }
        else
        {
            $title = $this->_l10n_midcom->get('create dog');
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
                MIDCOM_NAV_URL => "dog/{$this->_parent->guid}.html",
                MIDCOM_NAV_NAME => $this->_parent->name_with_kennel,
            );
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "create/dog/{$this->_parent->guid}.html",
                MIDCOM_NAV_NAME => $this->_request_data['title'],
            );
        }
        else
        {
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "create/dog.html",
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
        midcom_show_style('admin-create-dog');
    }



}

?>
