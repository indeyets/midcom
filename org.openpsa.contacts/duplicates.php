<?php
/**
 * Search for duplicate persons and groups in database
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_duplicates
{
    var $p_map = array();
    var $config = null;
    
    /**
     * Find duplicates for given org_openpsa_contacts_person object
     * @param $person org_openpsa_contacts_person object (does not need id)
     * @return array array of possible duplicates
     */
    function find_duplicates_person($person, $threshold = 1)
    {
        $ret = array();
        //Search for all potential duplicates (more detailed checking is done later)
        $qb = org_openpsa_contacts_person::new_query_builder();
        //$qb = new MidgardQueryBuilder('org_openpsa_person');
        if ($person->id)
        {
            $qb->add_constraint('id', '<>', $person->id);
            $qb2 = new MidgardQueryBuilder('midgard_member');
            $qb2->add_constraint('uid', '=', $person->id);
            $memberships = @$qb2->execute();
        }
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
        //TODO; check "not duplicate" parameter
        if (   !empty($person1->email)
            && strtolower($person1->email) == strtolower($person2->email))
        {
            $ret['p'] += 1;
        }
        if (   !empty($person1->handphone)
            && strtolower($person1->handphone) == strtolower($person2->handphone))
        {
            $ret['p'] += 1;
        }
        if (   !empty($person1->firstname)
            && !empty($person1->lastname)
            && !empty($person1->city)
            && strtolower($person1->firstname) == strtolower($person2->firstname)
            && strtolower($person1->lastname) == strtolower($person2->lastname)
            && strtolower($person1->city) == strtolower($person2->city)
            )
        {
            $ret['p'] += 0.5;
        }
        if (   !empty($person1->firstname)
            && !empty($person1->lastname)
            && !empty($person1->street)
            && strtolower($person1->firstname) == strtolower($person2->firstname)
            && strtolower($person1->lastname) == strtolower($person2->lastname)
            && strtolower($person1->street) == strtolower($person2->street)
            )
        {
            $ret['p'] += 0.9;
        }
        if (   !empty($person1->firstname)
            && !empty($person1->homephone)
            && strtolower($person1->firstname) == strtolower($person2->firstname)
            && strtolower($person1->homephone) == strtolower($person2->homephone)
            )
        {
            $ret['p'] += 0.7;
        }
        //TODO: firstname,lastname,company (especially: how to handle company if person is not created yet ??)
        
        return $ret;
    }
    
    /**
     * Find duplicates for given org_openpsa_contacts_group object
     * @param $group org_openpsa_contacts_group object (does not need id)
     * @return array array of possible duplicates
     */
    function find_duplicates_group($group, $threshold = 1)
    {
        $ret = array();
        $qb = org_openpsa_contacts_group::new_query_builder();
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
        //TODO; check "not duplicate" parameter
        if (   !empty($group1->homepage)
            && strtolower($group1->homepage) == strtolower($group2->homepage))
        {
            $ret['p'] += 0.2;
        }
        if (   !empty($group1->phone)
            && strtolower($group1->phone) == strtolower($group2->phone))
        {
            $ret['p'] += 0.5;
        }
        if (   !empty($group1->official)
            && strtolower($group1->official) == strtolower($group2->official))
        {
            $ret['p'] += 0.2;
        }
        if (   !empty($group1->phone)
            && !empty($group1->street)
            && strtolower($group1->phone) == strtolower($group2->phone)
            && strtolower($group1->street) == strtolower($group2->street)
            )
        {
            $ret['p'] += 1;
        }
        if (   !empty($group1->official)
            && !empty($group1->street)
            && strtolower($group1->official) == strtolower($group2->official)
            && strtolower($group1->street) == strtolower($group2->street)
            )
        {
            $ret['p'] += 1;
        }
        if (   !empty($group1->official)
            && !empty($group1->city)
            && strtolower($group1->official) == strtolower($group2->official)
            && strtolower($group1->city) == strtolower($group2->city)
            )
        {
            $ret['p'] += 0.5;
        }
        return $ret;
    }
    
    /**
     * Find duplicates for given all org_openpsa_contacts_person objects in database
     * @return array array of persons with their possible duplicates
     */
    function check_all_persons()
    {
        $ret = array();
        
        return $ret;
    }

    /**
     * Find duplicates for given all org_openpsa_contacts_group objects in database
     * @return array array of groups with their possible duplicates
     */
    function check_all_groups()
    {
        $ret = array();
        
        return $ret;
    }
    
}

?>