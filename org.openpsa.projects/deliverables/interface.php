<?php
/**
 * @package org.openpsa.projects
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: interface.php,v 1.2 2005/10/25 17:51:55 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * Wrapper class for deliverables operations
 *
 * This allows us to change the ways the plugins work and are loaded at a later
 * time (in case we wish to move them to the actual target components)
 *
 * @package org.openpsa.projects
 *
 */
class org_openpsa_projects_deliverables_interface
{
    var $_plugins = array(); //List of plugins available

    function __construct()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_find_plugins();
        debug_pop();
    }

    /**
     * Searches for plugins and fills $this->_plugins
     */
    function _find_plugins()
    {
        //Hardcoded for now.
        $this->_plugins['org_openpsa_projects_deliverables_interface_plugin_noop'] = new org_openpsa_projects_deliverables_interface_plugin_noop();
    }

    /**
     * Returns an array of available plugins as objects
     */
    function list_plugins()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $ret = array();
        foreach ($this->_plugins as $plugin)
        {
            $ret[] = $plugin;
        }
        debug_pop();
        return $ret;
    }

    /**
     * Checks all deliverables for task returns true if all return true otherwise false
     * @param integer $task_id Identifier of an OpenPsa Projects task
     * @return boolean Whether all deliverables of the task have been completed
     */
    function check_all_deliverables_status($task_id)
    {
        $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_projects_deliverable');
        $qb->add_constraint('task', '=', $task_id);
        $deliverables = $_MIDCOM->dbfactory->exec_query_builder($qb);
        if (!is_array($deliverables))
        {
            //Failure to fetch deliverables
            return false;
        }
        if (count($deliverables)==0)
        {
            //We have no deliverables
            return true;
        }
        foreach ($deliverables as $deliverable)
        {
            $plugin = $this->deliverable_plugin($deliverable);
            if (!$plugin)
            {
                //PONDER: should we fail here
                continue;
            }
            if (!$plugin->status())
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Takes plugin name and returns classname
     */
    function _resolve_plugin($plugin)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        foreach ($this->_plugins as $class => $obj)
        {
            if ($obj->name === $plugin)
            {
                debug_pop();
                return $class;
            }
        }
        debug_pop();
        return false;
    }

    /**
     * Based on deliverable object id/guid returns correct plugin initialized
     */
    function get_deliverable_plugin($identifier)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (is_a($identifier, 'org_openpsa_projects_deliverable'))
        {
            $deliverable = $identifier;
        }
        else if ($identifier !== NULL)
        {
            $deliverable = new org_openpsa_projects_deliverable($identifier);
        }
        if (!isset($deliverable)
            || !is_a($deliverable, 'org_openpsa_projects_deliverable'))
        {
            debug_add('could not get deliverable object');
            debug_pop();
            return false;
        }
        if (empty($deliverable->plugin))
        {
            debug_add('deliverable has no plugin set');
            debug_pop();
            return false;
        }

        $classname = $this->_resolve_plugin($deliverable->plugin);
        if (!$classname)
        {
            debug_add("could not resolve plugin {$deliverable->plugin}");
            debug_pop();
            return false;
        }
        debug_add("initializing deliverable from class {$classname}");
        debug_pop();
        return new $classname($deliverable);
    }

    /**
     * Returns an empty (not initialized with deliverable object) plugin for plugin name
     */
    function get_plugin($name)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $classname = $this->_resolve_plugin($deliverable->plugin);
        if (!$classname)
        {
            debug_add("could not resolve plugin {$deliverable->plugin}");
            debug_pop();
            return false;
        }
        debug_add("initializing plugin from class {$classname}");
        debug_pop();
        return new $classname();
    }
}


?>