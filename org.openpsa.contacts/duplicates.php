<?php
/**
 * @package org.openpsa.contacts
 * @author Nemein Oy http://www.nemein.com/
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * Search for duplicate persons and groups in database
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_duplicates
{
    /**
     * Used to store map of probabilities when seeking duplicates for given person/group
     */
    var $p_map = array();

    /**
     * Pointer to component configuration object.
     */
    var $config = null;

    /**
     * Cache memberships when possible
     */
    var $_membership_cache = array();

    /**
     * Find duplicates for given org_openpsa_contacts_person object
     * @param $person org_openpsa_contacts_person object (does not need id)
     * @return array array of possible duplicates
     */
    function find_duplicates_person($person, $threshold = 1)
    {
        $this->p_map = array(); //Make sure this is clean before starting
        $ret = array();
        //Search for all potential duplicates (more detailed checking is done later)
        $qb = org_openpsa_contacts_person::new_query_builder();
        //$qb = new MidgardQueryBuilder('org_openpsa_person');
        $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        if ($person->id)
        {
            $qb->add_constraint('id', '<>', $person->id);
            $qb2 = new MidgardQueryBuilder('midgard_member');
            $qb2->add_constraint('uid', '=', $person->id);
            $memberships = @$qb2->execute();
        }
        // TODO: Avoid persons marked as not_duplicate already in this phase.
        /*
        if ($person->guid)
        {
        }
        */
        $qb->begin_group('OR');
            //All members of groups this person is member of
            /* this particular way causes issues (crashing)
            if (  isset($memberships)
                && is_array($memberships))
            {
                $qb3 = MidgardQueryBuilder('midgard_member');
                $qb3->begin_group('OR');
                foreach ($memberships as $member)
                {
                    $qb3->add_constraint('gid', '=', $member->gid);
                }
                $qb3->end_group();
                $groups_members = @$qb3->execute();
                if (is_array($groups_members))
                {
                    foreach ($groups_members as $member2)
                    {
                        if ($member2->uid == $person->id)
                        {
                            continue;
                        }
                        $qb->add_constraint('id', '=', $member2->uid);
                    }
                }
            }
            */
            /*
            //Shared
            if ($person->)
            {
                $qb->add_constraint('', 'LIKE', $person->);
            }
            */
            //Shared firstname
            if ($person->firstname)
            {
                $qb->add_constraint('firstname', 'LIKE', $person->firstname);
            }
            //Shared lastname
            if ($person->lastname)
            {
                $qb->add_constraint('lastname', 'LIKE', $person->lastname);
            }
            //Shared email
            if ($person->email)
            {
                $qb->add_constraint('email', 'LIKE', $person->email);
            }
            //Shared handphone
            if ($person->handphone)
            {
                $qb->add_constraint('handphone', 'LIKE', $person->handphone);
            }
            //Shared city
            if ($person->city)
            {
                $qb->add_constraint('city', 'LIKE', $person->city);
            }
            //Shared street
            if ($person->street)
            {
                $qb->add_constraint('street', 'LIKE', $person->street);
            }
            //Shared homephone
            if ($person->homephone)
            {
                $qb->add_constraint('homephone', 'LIKE', $person->homephone);
            }
        $qb->end_group();
        //mgd_debug_start();
        $check_persons = $qb->execute();
        //mgd_debug_stop();
        if (!is_array($check_persons))
        {
            return false;
        }
        foreach ($check_persons as $check_person)
        {
            $p_array = $this->p_duplicate_person($person, $check_person);
            $this->p_map[$check_person->guid] = $p_array;
            if ($p_array['p'] >= $threshold)
            {
                $ret[] = $check_person;
            }
        }

        return $ret;
    }

    /**
     * Calculates P for the given two persons being duplicates
     * @param object person1
     * @param object person2
     * @return array with overall P and matched checks
     */
    function p_duplicate_person($person1, $person2)
    {
        $ret['p'] = 0;
        //TODO: read weight values from configuration

        if (   (   !empty($person2->guid)
                && is_object($person1)
                && method_exists($person1, 'parameter')
                && $person1->parameter('org.openpsa.contacts.duplicates:not_duplicate', $person2->guid))
            || (   !empty($person1->guid)
                && is_object($person2)
                && method_exists($person2, 'parameter')
                && $person2->parameter('org.openpsa.contacts.duplicates:not_duplicate', $person1->guid))
            )
        {
            // Not-duplicate parameter found, returning zero probability
            $ret['p'] = 0;
            return $ret;
        }
        $ret['email_match'] = false;
        if (   !empty($person1->email)
            && strtolower($person1->email) == strtolower($person2->email))
        {
            $ret['email_match'] = true;
            $ret['p'] += 1;
        }
        $ret['handphone_match'] = false;
        if (   !empty($person1->handphone)
            && strtolower($person1->handphone) == strtolower($person2->handphone))
        {
            $ret['handphone_match'] = true;
            $ret['p'] += 1;
        }
        $ret['fname_lname_city_match'] = false;
        if (   !empty($person1->firstname)
            && !empty($person1->lastname)
            && !empty($person1->city)
            && strtolower($person1->firstname) == strtolower($person2->firstname)
            && strtolower($person1->lastname) == strtolower($person2->lastname)
            && strtolower($person1->city) == strtolower($person2->city)
            )
        {
            $ret['fname_lname_city_match'] = true;
            $ret['p'] += 0.5;
        }
        $ret['fname_lname_street_match'] = false;
        if (   !empty($person1->firstname)
            && !empty($person1->lastname)
            && !empty($person1->street)
            && strtolower($person1->firstname) == strtolower($person2->firstname)
            && strtolower($person1->lastname) == strtolower($person2->lastname)
            && strtolower($person1->street) == strtolower($person2->street)
            )
        {
            $ret['fname_lname_street_match'] = true;
            $ret['p'] += 0.9;
        }
        $ret['fname_hphone_match'] = false;
        if (   !empty($person1->firstname)
            && !empty($person1->homephone)
            && strtolower($person1->firstname) == strtolower($person2->firstname)
            && strtolower($person1->homephone) == strtolower($person2->homephone)
            )
        {
            $ret['fname_hphone_match'] = true;
            $ret['p'] += 0.7;
        }

        $ret['fname_lname_company_match'] = false;
        // We cannot do this check if person1 hasn't been created yet...
        if (empty($person1->guid))
        {
            return $ret;
        }
        // Get membership maps
        if (   !isset($this->_membership_cache[$person1->guid])
            || !is_array($this->_membership_cache[$person1->guid]))
        {
            $this->_membership_cache[$person1->guid] = array();
            $qb = midcom_baseclasses_database_member::new_query_builder();
            $qb->add_constraint('uid', '=', $person1->id);
            $memberships = $qb->execute();
            foreach($memberships as $member)
            {
                $this->_membership_cache[$person1->guid][$member->gid] = $member->gid;
            }
        }
        $person1_memberships =& $this->_membership_cache[$person1->guid];
        if (   !isset($this->_membership_cache[$person2->guid])
            || !is_array($this->_membership_cache[$person2->guid]))
        {
            $this->_membership_cache[$person2->guid] = array();
            $qb = midcom_baseclasses_database_member::new_query_builder();
            $qb->add_constraint('uid', '=', $person2->id);
            $memberships = $qb->execute();
            foreach($memberships as $member)
            {
                $this->_membership_cache[$person2->guid][$member->gid] = $member->gid;
            }
        }
        $person2_memberships =& $this->_membership_cache[$person2->guid];
        foreach($person1_memberships as $gid)
        {
            if (   isset($person2_memberships[$gid])
                && !empty($person2_memberships[$gid]))
            {
                $ret['fname_lname_company_match'] = true;
                $ret['p'] += 0.5;
                break;
            }
        }

        // All checks done, return
        return $ret;
    }

    /**
     * Find duplicates for given org_openpsa_contacts_group object
     * @param $group org_openpsa_contacts_group object (does not need id)
     * @return array array of possible duplicates
     */
    function find_duplicates_group($group, $threshold = 1)
    {
        $this->p_map = array(); //Make sure this is clean before starting
        $ret = array();
        $qb = org_openpsa_contacts_group::new_query_builder();
        $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        if ($group->id)
        {
            $qb->add_constraint('id', '<>', $group->id);
        }
        $qb->begin_group('OR');
            /*
            //Shared
            if ($group->)
            {
                $qb->add_constraint('', 'LIKE', $group->);
            }
            */
            //Shared official
            if ($group->official)
            {
                $qb->add_constraint('official', 'LIKE', $group->official);
            }
            //Shared street
            if ($group->street)
            {
                $qb->add_constraint('street', 'LIKE', $group->street);
            }
            //Shared phone
            if ($group->phone)
            {
                $qb->add_constraint('phone', 'LIKE', $group->phone);
            }
            //Shared homepage
            if ($group->homepage)
            {
                $qb->add_constraint('homepage', 'LIKE', $group->homepage);
            }
            //Shared city
            if ($group->city)
            {
                $qb->add_constraint('city', 'LIKE', $group->city);
            }
        $qb->end_group();
        //mgd_debug_start();
        $check_groups = $qb->execute();
        //mgd_debug_stop();
        if (!is_array($check_groups))
        {
            return false;
        }
        foreach ($check_groups as $check_group)
        {
            $p_array = $this->p_duplicate_group($group, $check_group);
            $this->p_map[$check_group->guid] = $p_array;
            if ($p_array['p'] >= $threshold)
            {
                $ret[] = $check_group;
            }
        }

        return $ret;
    }

    /**
     * Calculates P for the given two persons being duplicates
     * @param object group1
     * @param object group2
     * @return array with overall P and matched checks
     */
    function p_duplicate_group($group1, $group2)
    {
        $ret['p'] = 0;
        //TODO: read weight values from configuration

        if (   (   !empty($group2->guid)
                && is_object($group1)
                && method_exists($group1, 'parameter')
                && $group1->parameter('org.openpsa.contacts.duplicates:not_duplicate', $group2->guid))
            || (   !empty($group1->guid)
                && is_object($group2)
                && method_exists($group2, 'parameter')
                && $group2->parameter('org.openpsa.contacts.duplicates:not_duplicate', $group1->guid))
            )
        {
            // Not-duplicate parameter found, returning zero probability
            $ret['p'] = 0;
            return $ret;
        }
        $ret['homepage_match'] = false;
        if (   !empty($group1->homepage)
            && strtolower($group1->homepage) == strtolower($group2->homepage))
        {
            $ret['homepage_match'] = true;
            $ret['p'] += 0.2;
        }
        $ret['phone_match'] = false;
        if (   !empty($group1->phone)
            && strtolower($group1->phone) == strtolower($group2->phone))
        {
            $ret['phone_match'] = true;
            $ret['p'] += 0.5;
        }
        $ret['official_match'] = false;
        if (   !empty($group1->official)
            && strtolower($group1->official) == strtolower($group2->official))
        {
            $ret['official_match'] = true;
            $ret['p'] += 0.2;
        }
        $ret['phone_street_match'] = false;
        if (   !empty($group1->phone)
            && !empty($group1->street)
            && strtolower($group1->phone) == strtolower($group2->phone)
            && strtolower($group1->street) == strtolower($group2->street)
            )
        {
            $ret['phone_street_match'] = true;
            $ret['p'] += 1;
        }
        $ret['official_street_match'] = false;
        if (   !empty($group1->official)
            && !empty($group1->street)
            && strtolower($group1->official) == strtolower($group2->official)
            && strtolower($group1->street) == strtolower($group2->street)
            )
        {
            $ret['official_street_match'] = true;
            $ret['p'] += 1;
        }
        $ret['official_city_match'] = false;
        if (   !empty($group1->official)
            && !empty($group1->city)
            && strtolower($group1->official) == strtolower($group2->official)
            && strtolower($group1->city) == strtolower($group2->city)
            )
        {
            $ret['official_city_match'] = true;
            $ret['p'] += 0.5;
        }
        return $ret;
    }

    /**
     * Find duplicates for given all org_openpsa_contacts_person objects in database
     * @return array array of persons with their possible duplicates
     */
    function check_all_persons($threshold = 1)
    {
        //Disable limits
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', 0);

        // PONDER: Can we do this in smaller batches using find_duplicated_person
        /*
          IDEA: Make an AT method for checking single persons duplicates, then another to batch
          register a check for every person in batches of say 500.
        */

        $ret = array();
        $ret['objects'] = array();
        $ret['p_map'] = array();
        $ret['threshold'] =& $threshold;

        $qb = org_openpsa_contacts_person::new_query_builder();
        $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $persons = $qb->execute();
        if (empty($persons))
        {
            return $ret;
        }

        $params = array();
        $params['ret'] =& $ret;
        $params['finder'] =& $this;
        $params['objects'] =& $persons;
        $params['mode'] = 'person';
        array_walk(
            $persons,
            /* array_walk cannot use even static methods, so we create an anonymous function trough a method */
            create_function('&$obj1, $key, &$params', $this->_check_all_arraywalk_code()),
            &$params
        );

        return $ret;
    }

    /**
     * Used by check_all_xxx() -method to walk the QB result and checking each against the rest
     * @access private
     * @return string code usable by create_function()
     */
    function _check_all_arraywalk_code()
    {
        return <<<EOF
        \$finder =& \$params['finder'];
        \$ret =& \$params['ret'];
        \$objects =& \$params['objects'];
        \$p_method = "p_duplicate_{\$params['mode']}";
        if (!method_exists(\$finder, \$p_method))
        {
            debug_add("method {\$p_method} is not valid, invalid mode string ??", MIDCOM_LOG_ERROR);
            return false;
        }

        foreach(\$objects as \$obj2)
        {
            if (\$obj1->guid == \$obj2->guid)
            {
                continue;
            }
            \$p_arr = \$finder->\$p_method(\$obj1, \$obj2);
            if (\$p_arr['p'] < \$ret['threshold'])
            {
                continue;
            }
            \$ret['objects'][\$obj1->guid] = \$obj1;
            \$ret['objects'][\$obj2->guid] = \$obj2;
            if (   !isset(\$ret['p_map'][\$obj1->guid])
                || !is_array(\$ret['p_map'][\$obj1->guid]))
            {
                \$ret['p_map'][\$obj1->guid] = array();
            }
            \$map =& \$ret['p_map'][\$obj1->guid];
            \$map[\$obj2->guid] = \$p_arr;
        }
EOF;
    }

    /**
     * Find duplicates for given all org_openpsa_contacts_group objects in database
     * @return array array of groups with their possible duplicates
     */
    function check_all_groups($threshold = 1)
    {
        //Disable limits
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', 0);

        $ret = array();
        $ret['objects'] = array();
        $ret['p_map'] = array();
        $ret['threshold'] =& $threshold;

        $qb = org_openpsa_contacts_group::new_query_builder();
        $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);

        $groups = $qb->execute();
        if (empty($groups))
        {
            return $ret;
        }

        $params = array();
        $params['ret'] =& $ret;
        $params['finder'] =& $this;
        $params['objects'] =& $groups;
        $params['mode'] = 'group';
        array_walk(
            $groups,
            /* array_walk cannot use even static methods, so we create an anonymous function trough a method */
            create_function('&$obj1, $key, &$params', $this->_check_all_arraywalk_code()),
            &$params
        );

        return $ret;
    }


    function mark_all($output=false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->mark_all_persons($output);
        $this->mark_all_groups($output);
        debug_pop();
    }

    /**
     * Find all duplicate persons and mark them
     */
    function mark_all_persons($output = false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $time_start = time();
        debug_add("Called on {$time_start}");
        if ($output)
        {
            echo "INFO: Starting with persons<br/>\n";
            flush();
        }
        $ret_persons = $this->check_all_persons();
        foreach ($ret_persons['p_map'] as $p1guid => $duplicates)
        {
            $person1 =& $ret_persons['objects'][$p1guid];
            foreach($duplicates as $p2guid => $details)
            {
                $person2 = $ret_persons['objects'][$p2guid];
                $msg = "Marking persons {$p1guid} (#{$person1->id}) and {$p2guid} (#{$person2->id}) as duplicates with P {$details['p']}";
                debug_add($msg, MIDCOM_LOG_INFO);
                $person1->parameter('org.openpsa.contacts.duplicates:possible_duplicate', $p2guid, $details['p']);
                $person2->parameter('org.openpsa.contacts.duplicates:possible_duplicate', $p1guid, $details['p']);
                if ($output)
                {
                    echo "&nbsp;&nbsp;&nbsp;INFO: {$msg}<br/>\n";
                    flush();
                }
            }
        }
        debug_add("Done on " . time() . ", took: " . (time()-$time_start) .  " seconds");
        if ($output)
        {
            echo "INFO: DONE with persons<br/>\n";
            flush();
        }
        debug_pop();
    }

    /**
     * Find all duplicate groups and mark them
     */
    function mark_all_groups($output=false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $time_start = time();
        debug_add("Called on {$time_start}");
        if ($output)
        {
            echo "INFO: Starting with groups<br/>\n";
            flush();
        }

        $ret_groups = $this->check_all_groups();
        foreach ($ret_groups['p_map'] as $g1guid => $duplicates)
        {
            $group1 =& $ret_groups['objects'][$g1guid];
            foreach($duplicates as $g2guid => $details)
            {
                $group2 = $ret_groups['objects'][$g2guid];
                $msg = "Marking groups {$g1guid} (#{$group1->id}) and {$g2guid} (#{$group2->id}) as duplicates with P {$details['p']}";
                debug_add($msg);
                $group1->parameter('org.openpsa.contacts.duplicates:possible_duplicate', $p2guid, $details['p']);
                $group2->parameter('org.openpsa.contacts.duplicates:possible_duplicate', $p1guid, $details['p']);
                if ($output)
                {
                    echo "&nbsp;&nbsp;&nbsp;INFO: {$msg}<br/>\n";
                    flush();
                }
            }
        }

        debug_add("Done on " . time() . ", took: " . (time()-$time_start) .  " seconds");
        if ($output)
        {
            echo "INFO: DONE with groups<br/>\n";
            flush();
        }
        debug_pop();
    }
}

?>