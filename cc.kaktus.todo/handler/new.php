<?php
/**
* @package cc.kaktus.todo
* @author The Midgard Project, http://www.midgard-project.org
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * @package cc.kaktus.todo
 */
class cc_kaktus_todo_handler_new extends midcom_baseclasses_components_handler
{
    /**
     * TODO list item
     *
     * @access private
     * @var cc_kaktus_todo_item
     */
    var $_todo = null;

    /**
     * DM2 instance for a TODO item
     *
     * @access private
     */
    var $_datamanager = null;

    /**
     * DM2 instance for creating a new instance
     *
     * @access private
     */
    var $_controller = null;

    /**
     * Parent ID of the requested TODO branch
     *
     * @access private
     * @var integer
     */
    var $_parent_id = 0;

    /**
     * Simple constructor. Connect to the parent class.
     *
     * @access public
     */
    function cc_kaktus_todo_handler_new()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Initialize the schemas et al.
     *
     * @access private
     */
    function _on_initialize()
    {
        $this->_schemadb =& $this->_request_data['schemadb'];
    }

    /**
     * Set the defaults for the create controller
     *
     * @access private
     */
    function _set_defaults()
    {
        // Initialize the array
        $defaults = array ();

        // Set the default user ID
        if ($_MIDGARD['user'])
        {
            $defaults['pid'] = $_MIDGARD['user'];
        }

        // Set the root TODO item
        $defaults['up'] = $this->_parent_id;
        $defaults['deadline'] = date('Y-m-d').' 00:00:00';

        return $defaults;
    }

    /**
     * Load the Datamanager controller instance
     *
     * @access private
     */
    function _load_create_controller()
    {
        $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('create');
        $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb'];
        $this->_request_data['controller']->schemaname = 'default';
        $this->_request_data['controller']->defaults = $this->_set_defaults();
        $this->_request_data['controller']->callback_object =& $this;

        if (!$this->_request_data['controller']->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }

    /**
     * DM2 Create callback method ties the created context to the submitted form and
     * adds the details not submitted by the form.
     *
     * @access private
     * @return Object midcom_db_event containing the information of the created event
     */
    function & dm2_create_callback(&$controller)
    {
        $this->_item = new cc_kaktus_todo_item_dba();

        $this->_item->up = $this->_parent_id;
        $this->_item->topic = $this->_topic->id;

        if (!$this->_item->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_item);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new item, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_event;
    }

    /**
     * Handler for creation of a new TODO list item. Checks the permissions and initializes
     * DM2 controller scripts.
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_new($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:create');

        if (array_key_exists(0, $args))
        {
            $this->_parent_id = $args[0];
        }

        $this->_load_create_controller();

        switch ($this->_request_data['controller']->process_form())
        {
            case 'save':
                $_MIDCOM->relocate($this->_item->id.'/');
                // This will exit
                break;

            case 'cancel':
                $_MIDCOM->relocate($this->_parent_id.'/');
                $_MIDCOM->relocate('');
                // This will exit
        }

        return true;
    }

    /**
     * Show the creation form
     *
     * @access private
     */
    function _show_new($handler_id, &$data)
    {
        $this->_request_data['page_title'] = $this->_l10n->get('create a new item');
        midcom_show_style('create_form');
    }
}
?>