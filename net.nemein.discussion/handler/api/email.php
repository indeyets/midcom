<?php
/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: email.php 5434 2007-03-02 16:32:35Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * E-Mail import handler. 
 *
 * 
 * @package net.nemein.discussion
 * @todo Rewrite for discussion (copied from blog)
 */
class net_nemein_discussion_handler_api_email extends midcom_baseclasses_components_handler
{
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

    var $importer = false;

    function net_nemein_discussion_handler_api_email()
    {
        parent::__construct();
    }

    /**
     * Loads and prepares the schema database.
     *
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
     *
     * Dummy for now
     */
    function & dm2_create_callback (&$controller)
    {
        return false;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_import($handler_id, $args, &$data)
    {
        $this->_load_controller();

        //Content-Type
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->cache->content->content_type('text/plain');

        if (!$this->_config->get('api_email_enable'))
        {
            return false;
        }

        if (   !array_key_exists('message_source', $_POST)
            || empty($_POST['message_source']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, '_POST[\'message_source\'] not present or empty.');
            // This will exit.
        }

        if (!class_exists('net_nemein_discussion_email_importer'))
        {
            require(MIDCOM_ROOT . '/net/nemein/discussion/helper_email_import.php');
        }

        $_MIDCOM->auth->request_sudo('net.nemein.discussion');
        $this->importer = new net_nemein_discussion_email_importer();
        $importer =& $this->importer;
        $importer->_config =& $this->_config;
        $importer->topic = $this->_topic->id;
        $importer->midcom_topic =& $this->_topic;
        $importer->controller =& $this->_controller;

        if (!$importer->parse($_POST['message_source']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not parse message from source.');
            // This will exit();
        }
        if (   !$importer->import($this->_config->get('api_email_strict_parent'), $this->_config->get('api_email_use_force'))
            && (   $importer->is_duplicate
                && !$this->_config->get('api_email_silently_ignore_duplicate')))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not import message.');
            // This will exit();
        }

        $_MIDCOM->auth->drop_sudo();
        debug_pop();
        return true;
    }
    
    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_import($handler_id, &$data)
    {
        //All done
        echo "OK\n";
    }
}