<?php
/**
 * @package net.nemein.bannedwords
 */

/**
 * @package net.nemein.bannedwords
 */
class net_nemein_bannedwords_edit_handler extends midcom_baseclasses_components_handler
{
    var $_banned_objects = null;

    var $_lang_banned_objects = null;

    var $_sitegroup = null;

    var $_controller = null;

    var $_schemadb = null;

    var $_banned = null;

    var $_content_topic = null;

    function net_nemein_bannedwords_edit_handler()
    {
        $_MIDCOM->load_library('net.nemein.bannedwords');
    $_MIDCOM->load_library('midcom.helper.datamanager2');
    parent::__construct();
    }

    function _on_initialize()
    {
        $_MIDCOM->style->prepend_component_styledir('net.nemein.bannedwords');
    $this->_sitegroup = $_MIDGARD['sitegroup'];
    $this->_content_topic = new midcom_db_topic($this->_topic->id);
    }

    function _prepare_request_data()
    {
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['schemadb'] =& $this->_schemadb;
    }

    function _load_schemadb()
    {
        $this->_schemadb =& midcom_helper_datamanager2_schema::load_database(
        'file:/net/nemein/bannedwords/config/schemadb_default.inc');
    }

    function _load_controller()
    {
        $this->_load_schemadb();
    $this->_controller =& midcom_helper_datamanager2_controller::create('create');
    $this->_controller->schemadb =& $this->_schemadb;
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
    $this->_controller->set_storage($this->_banned);
    if (! $this->_controller->initialize())
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 simple controller.");
        // This will exit.
    }
    }

    function & dm2_create_callback (&$controller)
    {
        $this->_banned = new net_nemein_bannedwords_word_dba();
    if (!$this->_banned->create())
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_r('We operated on this object:', $this->_banned);
        debug_pop();
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
            'Failed to create a new word, cannot continue. Last Midgard error was: '. mgd_errstr());
        // This will exit.
    }

    return $this->_banned;
    }

    function get_plugin_handlers()
    {
        return Array
    (
        'index' => Array
        (
            'handler' => array('net_nemein_bannedwords_edit_handler', 'manage'),
        ),
        'edit' => Array
        (
            'handler' => array('net_nemein_bannedwords_edit_handler', 'edit'),
        'fixed_args' => 'edit',
        'variable_args' => 1,
        ),
        'delete' => Array
        (
            'handler' => array('net_nemein_bannedwords_edit_handler', 'delete'),
        'fixed_args' => 'delete',
        'variable_args' => 1,
        ),
        'confirmdelete' => Array
        (
            'handler' => array('net_nemein_bannedwords_edit_handler', 'confirmdelete'),
        'fixed_args' => 'confirmdelete',
        'variable_args' => 1,
        ),

    );
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
            $_MIDCOM->relocate('__mfa/net.nemein.bannedwords');
        break;
        case 'cancel':
            $_MIDCOM->relocate('__mfa/net.nemein.bannedwords');
        break;
    }

        $this->_prepare_request_data();

        $qb = net_nemein_bannedwords_word_dba::new_query_builder();
    $qb->add_constraint('sitegroup', '=', $this->_sitegroup);

        $this->_banned_objects = $qb->execute();

        foreach($this->_banned_objects as $banned)
    {
        if (empty($banned->language))
        {
                 $this->_lang_banned_objects['nolang'][] = $banned;
        }
        else
        {
                $this->_lang_banned_objects[$banned->language][] = $banned;
            }
    }

        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_manage($handler_id, &$data)
    {
        if (!is_array($this->_lang_banned_objects))
        {
            $this->_lang_banned_objects = array();
        }

        foreach($this->_lang_banned_objects as $lang => $lang_banned)
        {
            $this->_request_data['language'] = $lang;
                midcom_show_style('net_nemein_bannedwords_wordlist_start');

            foreach($lang_banned as $banned)
            {
                    $this->_request_data['banned_object'] = $banned;
                midcom_show_style('net_nemein_bannedwords_wordlist_item');
            }

            midcom_show_style('net_nemein_bannedwords_wordlist_end');

        }
        $this->_request_data['controller'] = $this->_controller;
        midcom_show_style('net_nemein_bannedwords_word_add');
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
        $this->_banned = new net_nemein_bannedwords_word_dba();
    $this->_banned->get_by_guid($args[0]);
    $this->_load_controller_simple();

        switch ($this->_controller->process_form())
    {
        case 'save':
            $_MIDCOM->relocate('__mfa/net.nemein.bannedwords');
        break;
        case 'cancel':
            $_MIDCOM->relocate('__mfa/net.nemein.bannedwords');
        break;
    }

        $this->_prepare_request_data();

        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit($handler_id, &$data)
    {
        $this->_request_data['controller'] = $this->_controller;
    midcom_show_style('net_nemein_bannedwords_word_edit');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_content_topic->require_do('midgard:delete');

        if (array_key_exists("net_nemein_bannedwords_word_delete", $_POST))
    {
            $guid = $args[0];
            $banned = new net_nemein_bannedwords_word_dba();
        $banned->get_by_guid($guid);

            if ($banned->delete())
        {
            // TODO: handle error
            }
    }

        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_delete($handler_id, &$data)
    {
        $_MIDCOM->relocate('__mfa/net.nemein.bannedwords');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_confirmdelete($handler_id, $args, &$data)
    {
        $this->_request_data['delete_guid'] = $args[0];
        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_confirmdelete($handler_id, &$data)
    {
        midcom_show_style('net_nemein_bannedwords_word_confirmdelete');
    }
}

?>