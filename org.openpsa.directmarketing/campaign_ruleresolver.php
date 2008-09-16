<?php
/**
 * @package org.openpsa.directmarketing
 * @author Nemein Oy http://www.nemein.com/
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * Resolves smart-campaign rules array to one or more QB instances
 * with correct constraints, and merges the results.
 *
 * Rules array structure:
 * <code>
 * array(
 *    'type' => 'AND',
 *    'classes' => array(
 *        array (
 *            'type' => 'OR',
 *            'class' => 'org_openpsa_contacts_person',
 *            'rules' => array(
 *                array(
 *                    'property' => 'email',
 *                    'match' => 'LIKE',
 *                    'value' => '%@%'
 *                ),
 *                array(
 *                    'property' => 'handphone',
 *                    'match' => '<>',
 *                    'value' => ''
 *                ),
 *            ),
 *        ),
 *        array (
 *            'type' => 'AND',
 *            'class' => 'midgard_parameter',
 *            'rules' => array(
 *                array(
 *                    'property' => 'tablename',
 *                    'match' => '=',
 *                    'value' => 'person'
 *                ),
 *                array(
 *                    'property' => 'domain',
 *                    'match' => '=',
 *                    'value' => 'openpsa_test'
 *                ),
 *                array(
 *                    'property' => 'name',
 *                    'match' => '=',
 *                    'value' => 'param_match'
 *                ),
 *                array(
 *                    'property' => 'value',
 *                    'match' => '=',
 *                    'value' => 'bar'
 *                ),
 *            ),
 *        ),
 *    ),
 * ),
 * </code>
 *
 * NOTE: subgroups are processed before rules, subgroups must match class of parent group
 * until midgard core has the new infinite JOINs system. The root level group array is
 * named 'classes' because there should never be be two groups on this level using the same class
 *
 * @package org.openpsa.directmarketing
 */
class org_openpsa_directmarketing_campaign_ruleresolver
{
    var $_qbs = array(); //QB instances used by class
    var $_results =  array(); //Resultsets from said QBs
    var $_rules = null; //Copy of rules as received
    var $_seek =  array(); //index for quickly finding out which persons are found via which classes

    function __construct($rules = false)
    {
        // Make sure all supported classes are loaded
        $_MIDCOM->componentloader->load_graceful('org.maemo.devcodes');
        $_MIDCOM->componentloader->load_graceful('org.openpsa.contacts');
        if ($rules)
        {
            return $this->resolve($rules);
        }
    }

    /**
     * Recurses trough the rules array and creates QB instances & constraints as needed
     * @param array $rules rules array
     * @return boolean indicating success/failure
     */
    function resolve($rules)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_rules = $rules;
        if (!is_array($rules))
        {
            debug_add('rules is not an array', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (!array_key_exists('classes', $rules))
        {
            debug_add('rules[classes] is defined', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (!is_array($rules['classes']))
        {
            debug_add('rules[classes] is not an array', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        reset ($rules['classes']);
        foreach ($rules['classes'] as $group)
        {
            $this->_resolve_rule_group($group);
        }

        debug_add("this->_qbs:\n===\n" . sprint_r($this->_qbs) . "===\n");
        debug_pop();
        return true;
    }

    /**
     * Executes the QBs instanced via resolve, merges results and returns
     * single array of persons (or false in case of failure)
     */
    function execute()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!is_array($this->_rules))
        {
            debug_add('this->_rules is not an array', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (!array_key_exists('type', $this->_rules))
        {
            $this->_rules['type'] = 'AND';
        }
        reset ($this->_qbs);
        foreach ($this->_qbs as $class => $qb)
        {
            //We're only interested in results from current SG
            $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
            //mgd_debug_start();
            if (is_a($qb, 'midcom_core_querybuilder'))
            {
                $this->_results[$class] = $qb->execute();
            }
            else
            {
                //Standard midgard QB, silence due to unnecessary notice
                $this->_results[$class] = @$qb->execute();
            }
            //mgd_debug_stop();
            $this->_normalize_to_persons(&$this->_results[$class], $class);
        }
        debug_add("this->_results:\n===\n" . sprint_r($this->_results) . "===\n");

        $ret = array();
        switch (strtoupper($this->_rules['type']))
        {
            case 'OR':
                reset ($this->_results);
                foreach ($this->_results as $class_persons)
                {
                    foreach ($class_persons as $person)
                    {
                        $ret[$person->guid] = $person;
                    }
                }
                break;
            case 'AND':
                reset($this->_seek);
                foreach ($this->_seek as $guid => $result_tables)
                {
                    //debug_add("checking {$guid} is present in all classes");
                    reset($this->_qbs);
                    foreach ($this->_qbs as $class => $qb)
                    {
                        if (!array_key_exists($class, $result_tables))
                        {
                            //debug_add("not found in class {$class}, skipping");
                            continue 2;
                        }
                        //debug_add("found in class {$class}");
                    }
                    //debug_add('found in all classes, adding to array to be returned');
                    $ret[$guid] = $this->_seek[$guid][$class];
                }
                break;
            default:
                debug_add('invalid group type', MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
                break;
        }

        debug_pop();
        return $ret;
    }

    /**
     * Normalizes the various intermediate classes to org_openpsa_contacts_persons
     * for final results merging. Removes those entries which cannot be normalized.
     */
    function _normalize_to_persons(&$array, $from_class)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        debug_add("called with from_class: {$from_class}, array count: " . count($array));
        reset($array);
        foreach ($array as $k => $obj)
        {
            //debug_add("processing key #{$k}, object id #{$obj->id}, class: " . get_class($obj));
            switch (true)
            {
                //We need to fill the seek table, thus this no-op here (matches org_openpsa_contacts_person as well)
                case is_a($obj, 'midcom_org_openpsa_person'):
                    break;
                //Make all other persons org_openpsa_contacts_persons
                case is_a($obj, 'midgard_person'):
                    $array[$k] = new org_openpsa_contacts_person($obj->id);
                    break;
                // Expand add org_openpsa_contacts_person for each group member
                case is_a($obj, 'midgard_group'):
                case is_a($obj, 'org_openpsa_organization'):
                    unset ($array[$k]);
                    $this->_expand_group_members2persons($obj->id, $array);
                    break;
                //Expand member to org_openpsa_contacts_person
                case is_a($obj, 'midgard_member'):
                case is_a($obj, 'midgard_eventmember'):
                    $array[$k] = new org_openpsa_contacts_person($obj->uid);
                    break;
                //Expand various parameters to corresponding org_openpsa_contacts_person(s)
                case is_a($obj, 'midgard_parameter'):
                    switch ($obj->tablename)
                    {
                        case 'person':
                            $array[$k] = new org_openpsa_contacts_person($obj->oid);
                            break;
                        case 'grp':
                            unset ($array[$k]);
                            $this->_expand_group_members2persons($obj->oid, $array);
                            break;
                        default:
                            debug_add("parameters for table {$obj->tablename} not supported");
                            unset ($array[$k]);
                            break;
                    }
                    break;
                case is_a($obj, 'midcom_org_openpsa_campaign_member'):
                case is_a($obj, 'midcom_org_openpsa_campaign_message_receipt'):
                case is_a($obj, 'midcom_org_openpsa_link_log'):
                    $array[$k] = new org_openpsa_contacts_person($obj->person);
                    break;
                case is_a($obj, 'org_maemo_devcodes_application'):
                    $array[$k] = new org_openpsa_contacts_person($obj->applicant);
                    break;
                case is_a($obj, 'org_maemo_devcodes_code'):
                    $array[$k] = new org_openpsa_contacts_person($obj->recipient);
                    break;
                default:
                    debug_add("class " . get_class($obj) . " not supported", MIDCOM_LOG_WARN);
                    unset ($array[$k]);
                    break;
            }
            if (array_key_exists($k, $array))
            {
                if (   !array_key_exists($array[$k]->guid, $this->_seek)
                    || !is_array($this->_seek[$array[$k]->guid]))
                {
                    $this->_seek[$array[$k]->guid] = array();
                }
                //debug_add("referring \"{$array[$k]->rname}\" via this->_seek[{$array[$k]->guid}][{$from_class}]");
                $this->_seek[$array[$k]->guid][$from_class] =& $array[$k];
            }
        }
        debug_pop();
    }

    /**
     * Adds group #$id members to array as org_openpsa_contacts_persons
     */
    function _expand_group_members2persons($id, &$array)
    {
        $qb_grp_mem = new midgard_query_builder('midgard_member');
        $qb_grp_mem->add_constraint('gid', '=', $id);
        $grp_mems = @$qb_grp_mem->execute();
        if (!is_array($grp_mems))
        {
            return;
        }
        foreach($grp_mems as $grp_mem)
        {
            $array[] = new org_openpsa_contacts_person($grp_mem->uid);
        }
    }

    /**
     * Resolves the rules in a single rule group
     * @param array $group single group from rules array
     * @param object $qb related QB object
     * @return boolean indicating success/failure
     */
    function _resolve_rule_group($group, $match_class = false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!is_array($group))
        {
            debug_add('group is not an array', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (!array_key_exists('rules', $group))
        {
            debug_add('group[rules] is not defined', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (!is_array($group['rules']))
        {
            debug_add('group[rules] is not an array', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (   !array_key_exists('class', $group)
            || empty($group['class']))
        {
            debug_add('group[class] is not defined', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (   $match_class
            && (
                $group['class'] != $match_class
                )
            )
        {
            debug_add("{$group['class']} != {$match_class}, unmatched classes where match required", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (!array_key_exists($group['class'], $this->_qbs))
        {
            $tmpObj = new $group['class']();
            if (!method_exists($tmpObj, 'new_query_builder'))
            {
                $this->_qbs[$group['class']] = new midgard_query_builder($group['class']);
            }
            else
            {
                $this->_qbs[$group['class']] = call_user_func(array($group['class'], 'new_query_builder'));
            }
        }
        debug_add("qb =& this->_qbs[{$group['class']}]");
        $qb =& $this->_qbs[$group['class']];
        if (!array_key_exists('type', $group))
        {
            $group['type'] = 'AND';
        }
        debug_add("calling qb->begin_group(strtoupper({$group['type']}))");
        $qb->begin_group(strtoupper($group['type']));
        if (array_key_exists('groups', $group))
        {
            foreach ($group['groups'] as $subgroup)
            {
                $this->_resolve_rule_group($subgroup, $group['class']);
            }
        }
        foreach ($group['rules'] as $rule)
        {
            $this->_parse_rule($rule, $qb);
        }
        debug_add('calling qb->end_group()');
        $qb->end_group();
        debug_pop();
        return true;
    }

    /**
     * Parses a rule definition array, and adds QB constraints accordingly
     * @param array $rule rule definition array
     * @param object $qb reference to groups QB instance
     * @return boolean indicating success/failure
     */
    function _parse_rule($rule, &$qb)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!is_array($rule))
        {
            debug_add('rule is not an array', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (   !array_key_exists('property', $rule)
            || !array_key_exists('match', $rule)
            || !array_key_exists('value', $rule)
            )
        {
            debug_add('rule array does not have required keys present', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        debug_add("calling qb->add_constraint({$rule['property']}, {$rule['match']}, {$rule['value']})");
        $qb->add_constraint($rule['property'], $rule['match'], $rule['value']);

        debug_pop();
        return true;
    }
}

?>