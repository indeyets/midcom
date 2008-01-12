<?php
/**
 * @package net.nemein.feedcollector
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a URL handler class for net.nemein.feedcollector
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 *
 * @see midcom_baseclasses_components_handler
 * @package net.nemein.feedcollector
 */
class net_nemein_feedcollector_handler_manage extends midcom_baseclasses_components_handler
{

    /**
     * The Datamanager of the article to display (for delete mode)
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

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
     * Simple default constructor.
     */
    function net_nemein_feedcollector_handler_manage()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * _on_initialize is called by midcom on creation of the handler.
     */
    function _on_initialize()
    {
         $this->_content_topic =& $this->_request_data['content_topic'];
    }

    /**
     * Loads and prepares the schema database.
     *
     * Special treatment is done for the name field, which is set readonly for non-admins
     * if the simple_name_handling config option is set. (using an auto-generated urlname based
     * on the title, if it is missing.)
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb'];
        if (   $this->_config->get('simple_name_handling')
            && ! $_MIDCOM->auth->admin)
        {
            foreach (array_keys($this->_schemadb) as $name)
            {
                $this->_schemadb[$name]->fields['name']['readonly'] = true;
            }
        }
    }

    /**
     * Internal helper, loads the controller for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_feedtopic);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for article {$this->_feedtopic->id}.");
            // This will exit.
        }
    }

    /**
     * Internal helper, loads the datamanager for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_feedtopic))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for article {$this->_feedtopic->id}.");
            // This will exit.
        }
    }


    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function &dm2_create_callback (&$controller)
    {
        $this->_feedtopic = new net_nemein_feedcollector_topic_dba();
        $this->_feedtopic->node = $this->_content_topic->id;

        if (! $this->_article->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_article);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new feedtopic, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        // Callback possibility
        if ($this->_config->get('callback_function'))
        {
            if ($this->_config->get('callback_snippet'))
            {
                // mgd_include_snippet($this->_config->get('callback_snippet'));
                $eval = midcom_get_snippet_content($this->_config->get('callback_snippet'));

                if ($eval)
                {
                    eval($eval);
                }
            }

            $callback = $this->_config->get('callback_function');
            $callback($this->_feedtopic, $this->_content_topic);
        }

        return $this->_feedtopic;
    }


    /**
     * The handler for the index article.
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_manage ($handler_id, $args, &$data)
    {
        $this->_request_data['name']  = "net.nemein.feedcollector";
        $this->_request_data['permalinks'] = new midcom_services_permalinks();
        $this->_request_data['topic_introduction'] = $this->_config->get('topic_introduction');
        $this->_update_breadcrumb_line($handler_id);
        $title = $this->_l10n_midcom->get('index');
        $_MIDCOM->set_pagetitle(":: {$title}");

        $topics = array();

        $qb_feedtopics = net_nemein_feedcollector_topic_dba::new_query_builder();
        $qb_feedtopics->add_constraint('node', '=', (int)$this->_content_topic->id);
        $qb_feedtopics->add_order($this->_config->get('sort_order'));
        $feedtopics = $qb_feedtopics->execute();
        $this->topics = $feedtopics;

        return true;
    }

    /**
     * This function does the output.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_manage($handler_id, &$data)
    {
        midcom_show_style('manage-header');
        foreach($this->topics as $topic)
        {
            $this->_request_data['topic'] = $topic;
            midcom_show_style('manage-topic-item');
        }
        midcom_show_style('manage-footer');
    }

    /**
     * The handler for the index article.
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_delete ($handler_id, $args, &$data)
    {
        $this->_request_data['name']  = "net.nemein.feedcollector";
        $this->_request_data['topic_introduction'] = $this->_config->get('topic_introduction');
        $this->_update_breadcrumb_line($handler_id);
        $title = $this->_l10n_midcom->get('index');
        $_MIDCOM->set_pagetitle(":: {$title}");

        $this->_feedtopic = new net_nemein_feedcollector_topic_dba($args[0]);
        if (! $this->_feedtopic)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The feedcollector topic {$args[0]} was not found.");
            // This will exit.
        }
        $this->_feedtopic->require_do('midgard:delete');

        $this->_load_datamanager();

        if (array_key_exists('net_nemein_feedcollector_deleteok', $_REQUEST))
        {
            // Deletion confirmed.
            if (! $this->_feedtopic->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete feedcollector topic {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }

            // Update the index
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->delete($this->_feedtopic->guid);

            // Delete ok, relocating to welcome.
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists('net_nemein_feedcollector_deletecancel', $_REQUEST))
        {
            // Redirect to view page.
            $_MIDCOM->relocate("");
            // This will exit()
        }

        $this->_request_data['datamanager'] =& $this->_datamanager;

        return true;
    }

    /**
     * This function does the output.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_delete($handler_id, &$data)
    {

        $this->_request_data['topic'] = $this->_feedtopic;
        midcom_show_style('manage-topic-delete');
    }
    /**
     * The handler for the index article.
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit ($handler_id, $args, &$data)
    {
        $this->_request_data['name']  = "net.nemein.feedcollector";
        $this->_request_data['topic_introduction'] = $this->_config->get('topic_introduction');
        $this->_update_breadcrumb_line($handler_id);
        $title = $this->_l10n_midcom->get('index');
        $_MIDCOM->set_pagetitle(":: {$title}");

        $this->_feedtopic = new net_nemein_feedcollector_topic_dba($args[0]);
        if (!$this->_feedtopic)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The feedtopic {$args[0]} was not found.");
            // This will exit.
        }


        $this->_feedtopic->require_do('midgard:update');

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // *** FALL-THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate("");
                // This will exit.
        }

        $this->_request_data['controller'] =& $this->_controller;


        return true;
    }

    /**
     * This function does the output
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit($handler_id, &$data)
    {

        $this->_request_data['topic'] = $this->_feedtopic;
        midcom_show_style('manage-topic-edit');
    }


    function _update_breadcrumb_line()
    {
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "/",
            MIDCOM_NAV_NAME => $this->_l10n->get('manage'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>
