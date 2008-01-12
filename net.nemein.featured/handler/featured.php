<?php
/**
 * @package net.nemein.featured
 */

/**
 * Featured object handler
 *
 * @package net.nemein.featured
 */
class net_nemein_featured_handler_featured extends midcom_baseclasses_components_handler
{
    var $_content_topic = null;

    var $_featured = null;

    var $_featured_objects = null;

    var $_featured_groups = null;

    var $_controller = null;

    var $_schemadb = null;

    /**
     * Simple default constructor.
     */
    function net_nemein_featured_handler_featured()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
        $this->_content_topic =& $this->_request_data['content_topic'];
    }

    function _prepare_request_data()
    {
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['schemadb'] =& $this->_schemadb;
    }

    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb'];
    }

    function _load_controller()
    {
        $this->_load_schemadb();

        $this->_request_data['defaults'] = Array();
        // Allow setting defaults from query string, useful for things like "create event for today" and chooser
        if (   isset($_GET['defaults'])
            && is_array($_GET['defaults']))
        {
            foreach ($_GET['defaults'] as $key => $value)
            {
                if (!isset($this->_schemadb['default']->fields[$key]))
                {
                    // No such field in schema
                    continue;
                }

                if ($key == 'object_location')
                {
                    $value = str_replace($_MIDCOM->get_host_prefix(), '', $value);
                }

                $this->_request_data['defaults'][$key] = $value;
            }
        }


        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->defaults = $this->_request_data['defaults'];
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }

    function _load_controller_simple()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_featured);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 simple controller.");
            // This will exit.
        }
    }

    function & dm2_create_callback (&$controller)
    {
        $this->_featured = new net_nemein_featured_item_dba();
        $this->_featured->topicGuid = $this->_content_topic->guid;
        if (!$this->_featured->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_featured);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,'Failed to create a new article, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }
        return $this->_featured;
    }

    function _get_featured_objects()
    {
        $this->_featured_groups = $this->_config->get('groups');

        $qb = net_nemein_featured_item_dba::new_query_builder();
        $qb->add_constraint('topicGuid', '=', $this->_content_topic->guid);
        $qb->add_order('metadata.score', 'ASC');

        $this->_featured_objects = $qb->execute();
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_manage($handler_id, $args, &$data)
    {
        $this->_content_topic->require_do('midgard:create');

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':

            break;
            case 'cancel':
            break;
        }

        $this->_prepare_request_data();

        $this->_get_featured_objects();

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_manage($handler_id, &$data)
    {

        foreach($this->_featured_groups as $name => $group)
        {
            $this->_request_data['featured_group'] = $group;
            midcom_show_style('admin_group_start');

            foreach($this->_featured_objects as $featured)
            {
                if ($featured->groupName == $name)
                {
                    $this->_request_data['featured_item'] = $featured;
                    midcom_show_style('admin_group_item');
                }
            }

            midcom_show_style('admin_group_end');
        }

        //$this->request_data['featured_groups'] = $this->config->get('groups');

        midcom_show_style('admin_create');
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_featured = new net_nemein_featured_item_dba();
        $this->_featured->get_by_guid($args[0]);
        $this->_featured->delete();

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_delete($handler_id, &$data)
    {
        $_MIDCOM->uimessages->add($data['l10n']->get('net.nemein.featured'), $data['l10n']->get('item deleted successfully'), 'ok');
        $_MIDCOM->relocate('manage');
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_content_topic->require_do('midgard:edit');

        $this->_featured = new net_nemein_featured_item_dba();
        $this->_featured->get_by_guid($args[0]);

        $this->_load_controller_simple();

        switch ($this->_controller->process_form())
        {
            case 'save':
                $_MIDCOM->uimessages->add($data['l10n']->get('net.nemein.featured'), $data['l10n']->get('item updated successfully'), 'ok');
                $_MIDCOM->relocate('manage');
            break;
            case 'cancel':
                $_MIDCOM->relocate('manage');
            break;
        }

        $this->_prepare_request_data();

        $this->_get_featured_objects();

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit($handler_id, &$data)
    {
        $this->_request_data['controller'] = $this->_controller;
        midcom_show_style('admin_edit');
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_move_up($handler_id, $args, &$data)
    {
        $this->_featured = new net_nemein_featured_item_dba();
        $this->_featured->get_by_guid($args[0]);
        $this->_featured->metadata->score++;

        if (!$this->_featured->update())
        {
            // handle error
        }

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_move_up($handler_id, &$data)
    {
        $_MIDCOM->relocate('manage');
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_move_down($handler_id, $args, &$data)
    {
        $this->_featured = new net_nemein_featured_item_dba();
        $this->_featured->get_by_guid($args[0]);
        $this->_featured->metadata->score--;

        if (!$this->_featured->update())
        {
            // handle error
        }

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_move_down($handler_id, &$data)
    {
        $_MIDCOM->relocate('manage');
    }
}

?>
