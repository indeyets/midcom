<?php
/**
 * @package net.nemein.beaexporter
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for state-log objects
 *
 * @package net.nemein.beaexporter
 */
class net_nemein_beaexporter_state_dba extends __net_nemein_beaexporter_state_dba
{
    var $_valid_actions = array
        (
            'created' => true,
            'updated' => true,
            'deleted' => true,
        );

    function net_nemein_beaexporter_state_dba($id = null)
    {
        return parent::__net_nemein_beaexporter_state_dba($id);
    }

    /**
     * Get the state for given object or guid
     */
    function get_for($input)
    {
        $guid = false;
        if (   is_object($input)
            || !empty($input->guid))
        {
            $guid = $input->guid;
        }
        if (mgd_is_guid($input))
        {
            $guid = $input;
        }
        if (!$guid)
        {
            return false;
        }
        $qb = net_nemein_beaexporter_state_dba::new_query_builder();
        $qb->add_constraint('objectguid', '=', $guid);
        $stat = $qb->execute();
        if (empty($stat))
        {
            return false;
        }
        return $stat[0];
    }

    /**
     * Create a state for given object
     */
    function create_for($object, $url, $action = 'created')
    {
        if (   !is_object($object)
            || empty($object->guid))
        {
            return false;
        }
        $state = new net_nemein_beaexporter_state_dba();
        $state->targeturl = $url;
        $state->objectaction = $action;
        $state->timestamp = time();
        $state->objectguid = $object->guid;
        return $state->create();
    }

    function _on_creating()
    {
        if (net_nemein_beaexporter_state_dba::get_for($this->objectguid))
        {
            // Only one state object per target object
            return false;
        }
        if (!array_key_exists($this->objectaction, $this->_valid_actions))
        {
            debug_add("action '{$this->objectaction}' is not valid, valid actions\n===\n" . sprint_r($this->_valid_actions) . "===\n", MIDCOM_LOG_ERROR);
            // Invalid action
            return false;
        }
        return true;
    }

    function _on_updating()
    {
        if (!array_key_exists($this->objectaction, $this->_valid_actions))
        {
            // Invalid action
            return false;
        }
        $this->timestamp = time();
        return true;
    }
}
?>