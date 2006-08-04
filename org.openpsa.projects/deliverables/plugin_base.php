<?php
/**
 * @package org.openpsa.projects
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: plugin_base.php,v 1.3 2006/01/03 13:28:22 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */
 
/**
 * Baseclass for deliverables plugins
 */
class org_openpsa_projects_deliverables_interface_plugin
{
    var $_deliverable; //A deliverable object
    var $name; //Name of the plugin
    var $description; //Description of the plugin
    
    function org_openpsa_projects_deliverables_interface_plugin($identifier=NULL)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (is_a($identifier, 'org_openpsa_projects_deliverable'))
        {
            $this->_deliverable = $identifier;
        }
        else if ($identifier !== NULL)
        {
            $this->_deliverable = new org_openpsa_projects_deliverable($identifier);
        }
        debug_pop();
    }
    
    /**
     * Returns true or false depending on whether the plugin considers the target
     * "delivered" or not.
     * @return boolean
     */
    function status()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('This method must be overridden');
        debug_pop();
        return false;
    }

    /**
     * In task view the select plugin(s) row for this
     */
    function render_select_plugin($task_id=NULL)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (empty($this->name))
        {
            debug_add('the plugin has no name, aborting');
            debug_pop();
            return false;
        }
        debug_pop();
        return "<div class=\"org_openpsa_projects_deliverables_interface_plugin\">{$this->name}</div>\n";        
    }

    /**
     * Creates the deliverable object (NOTE: not the target)
     */
    function handle_select_plugin($task_id)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (empty($this->name))
        {
            debug_add('the plugin has no name, aborting');
            debug_pop();
            return false;
        }
        $deliverable = new org_openpsa_projects_deliverable();
        $deliverable->task = $task_id;
        $deliverable->plugin = $this->name;
        $stat = $deliverable->create();
        if (!$stat)
        {
            debug_add("could not create deliverable\n===\n" . sprint_r($deliverable) . "===\nmgd_errstr=" . mgd_errstr());
            debug_pop();
            return false;
        }
        debug_pop();
        return true;
    }
    
    /**
     * Depending on deliverable status this will either render a way to
     * create the target or a link to the target
     *
     * There are two modes, 'task' and 'todo' for different renderings.
     */
    function render_deliverable($mode = 'task')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (empty($this->name))
        {
            debug_add('the plugin has no name, aborting');
            debug_pop();
            return false;
        }
        switch ($mode)
        {
            case 'task':
            case 'todo':
                debug_pop();
                return "<div class=\"org_openpsa_projects_deliverables_interface_plugin\">{$this->name}</div>\n";
                break;
            default:
                debug_add("invalid mode: {$mode}", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
        }
    }

    /**
     * This will create the deliverable target
     *
     * At this time the mode selection has no effect.
     */
    function handle_deliverable($mode = 'task')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug('baseclass default is to return true');
        debug_pop();
        return true;
    }
}
 
?>