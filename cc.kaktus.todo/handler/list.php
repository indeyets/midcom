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
class cc_kaktus_todo_handler_list extends midcom_baseclasses_components_handler
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
     * Parent ID of the requested TODO branch
     *
     * @access private
     * @var integer
     */
    var $_parent_id = null;

    /**
     * ID of the filtering person
     *
     * @access private
     * @var integer
     */
    var $_person_id = null;

    /**
     * Type for filtering the results
     *
     * @access private
     * @var string
     */
    var $_type = '';

    /**
     * Simple constructor. Connect to the parent class.
     *
     * @access public
     */
    function cc_kaktus_todo_handler_list()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     *
     *
     *
     */
    function _on_initialize()
    {
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/cc.kaktus.todo/toggle.js');
        $this->_schemadb =& $this->_request_data['schemadb'];
    }

    /**
     * Loads the DM instance for a TODO list item
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);
        $this->_request_data['datamanager'] =& $this->_datamanager();

        if (   !$this->_datamanager
            || !$this->_datamanager->autoset_storage($this->_team))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a Datamanager 2 instance for team {$this->_team->name}");
            // This will exit
        }
    }

    /**
     * Get the filtering options for the query builder
     *
     * @access private
     */
    function _get_filters()
    {
        $filters = array ();

        // Filtering by group
        if ($this->_config->get('group'))
        {
            $filters['gid'] = array
            (
                'type' => '=',
                'value' => $this->_config->get('group'),
            );
        }

        if (array_key_exists('group', $_GET))
        {
            $filters['gid'] = array
            (
                'type' => '=',
                'value' => $_GET['group'],
            );
        }

        // Filtering by person
        if ($this->_config->get('person'))
        {
            $filters['pid'] = array
            (
                'type' => '=',
                'value' => $this->_config->get('person'),
            );
        }

        if (array_key_exists('person', $_GET))
        {
            $filters['pid'] = array
            (
                'type' => '=',
                'value' => $_GET['person'],
            );
        }

        if ($this->_type === 'overtime')
        {
            $filters['deadline'] = array
            (
                'type' => '<',
                'value' => date('Y-m-d'),
            );

            $filters['flag'] = array
            (
                'type' => '<>',
                'value' => CC_KAKTUS_TODO_TIME_FINISHED,
            );
        }

        if ($this->_type === 'finished')
        {
            $filters['flag'] = array
            (
                'type' => '=',
                'value' => CC_KAKTUS_TODO_TIME_FINISHED,
            );
        }

        return $filters;
    }

    /**
     * Loads a list of items
     *
     * @access private
     * @var integer Describing the owner item
     */
    function _load_items($id = null)
    {
        $qb = cc_kaktus_todo_item_dba::new_query_builder();

        if (!is_null($id))
        {
            $qb->add_constraint('up', '=', $id);
        }
        else
        {
            $qb->add_constraint('up', '=', 0);
        }

        if ($this->_config->get('person'))
        {
            $qb->add_constraint('pid', '=', $this->_config->get('person'));
        }

        foreach ($this->_get_filters() as $key => $array)
        {
            $qb->add_constraint($key, $array['type'], $array['value']);
        }

        return @$qb->execute_unchecked();
    }

    /**
     * List items
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        return true;
    }

    /**
     * Show TODO items
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     * @access private
     */
    function _show_list($handler_id, &$data)
    {
        midcom_show_style('todo_list_header');
        $this->_items = $this->_load_items($this->_parent_id);

        foreach ($this->_items as $item)
        {
            $this->_request_data['item'] =& $item;
            $this->_load_datamanager();
            midcom_show_style('todo_list_item');
        }

        midcom_show_style('todo_list_footer');
    }

    /**
     * List items that should have already been ready
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_overtime($handler_id, $args, &$data)
    {
        $this->_type = 'overtime';
        return true;
    }

    /**
     * Show TODO items
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     * @access private
     */
    function _show_overtime($handler_id, &$data)
    {
        midcom_show_style('todo_list_header');
        $this->_items = $this->_load_items($this->_parent_id);
        midcom_show_style('todo_list_footer');
    }

    /**
     * List items that should have already been ready
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_finished($handler_id, $args, &$data)
    {
        $this->_type = 'finished';
        return true;
    }

    /**
     * Show TODO items
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     * @access private
     */
    function _show_finished($handler_id, &$data)
    {
        midcom_show_style('todo_list_header');
        $this->_items = $this->_load_items($this->_parent_id);
        midcom_show_style('todo_list_footer');
    }
}
?>