<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: action.php,v 1.2 2006/05/10 16:27:39 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Task action handler
 *
 * @package org.openpsa.projects
 */
class org_openpsa_projects_handler_task_action extends midcom_baseclasses_components_handler
{

    function __construct()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
    }

    function _load_task($identifier)
    {
        $task = new org_openpsa_projects_task($identifier);

        if (!is_object($task))
        {
            return false;
        }

        return $task;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_action($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        // Check if we get the task
        $this->_request_data['task'] = $this->_load_task($args[0]);
        if (!$this->_request_data['task'])
        {
            return false;
        }

        // Check if the action is a valid one
        $this->_request_data['task_action'] = $args[1];
        switch ($args[1])
        {
            case 'reopen':
                $this->_request_data['task']->require_do('midgard:update');
                $this->_request_data['task']->reopen();
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                $_MIDCOM->relocate("{$prefix}task/{$this->_request_data['task']->guid}/");
                // This will exit()

            case 'complete':
                $this->_request_data['task']->require_do('midgard:update');
                $this->_request_data['task']->complete();
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                $_MIDCOM->relocate("{$prefix}task/{$this->_request_data['task']->guid}/");
                // This will exit()
            default:
                return false;
        }
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_action($handler_id, &$data)
    {
    }
}
?>