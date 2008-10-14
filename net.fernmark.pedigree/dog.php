<?php
/**
 * @package net.fernmark.pedigree
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: aerodrome.php 3630 2006-06-19 10:03:59Z bergius $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for dog objects
 *
 * @package net.fernmark.pedigree
 */
class net_fernmark_pedigree_dog_dba extends __net_fernmark_pedigree_dog_dba
{
    var $name_with_kennel = '';

    function __construct($src = null)
    {
        parent::__construct($src);
    }

    function _on_loaded()
    {
        if (empty($this->name))
        {
            $this->name = "unnamed #{$this->id}";
        }
        if (empty($this->name_with_kennel))
        {
            $this->name_with_kennel = $this->_resolve_name_with_kennel();
        }
        return true;
    }

    function _resolve_name_with_kennel()
    {
        $ret = $this->name;
        $kennel = false;
        $breeder = false;
        if (empty($this->kennel))
        {
            $GLOBALS['org_openpsa_contacts_group_autoload_members'] = false;
            $kennel = new org_openpsa_contacts_group_dba($this->kennel);
            unset($GLOBALS['org_openpsa_contacts_group_autoload_members']);
        }
        switch (true)
        {
            case (   $kennel
                  && $breeder
                  && strpos($this->name, $kennel->official) === false):
                $ret = "{$kennel->official}: {$this->name} ({$breeder->name})";
                break;
            case (   $kennel
                  && strpos($this->name, $kennel->official) === false):
                $ret = "{$kennel->official}: {$this->name}";
                break;
            case ($breeder):
                $ret = "{$this->name} ({$breeder->name})";
                break;
        }
        return $ret;
    }

    /** 
     * Prepares a QB for offspring queries
     *
     * @return midgard_query_builder ready for other constraints
     */
    function &get_offspring_qb()
    {
        $qb = net_fernmark_pedigree_dog_dba::new_query_builder();
        switch ($this->sex)
        {
            case NET_FERMARK_PEDIGREE_SEX_MALE:
                    $qb->add_constraint('sire', '=', (int)$this->id);
                break;
            case NET_FERMARK_PEDIGREE_SEX_FEMALE:
                    $qb->add_constraint('dam', '=', (int)$this->id);
                break;
            default:
                // We should not this fallback, but lets be safe
                $qb->begin_group('OR');
                    $qb->add_constraint('sire', '=', (int)$this->id);
                    $qb->add_constraint('dam', '=', (int)$this->id);
                $qb->end_group();
        }
        return $qb;
    }

    /**
     * Whether current dog has any offspring
     *
     * @return boolean indicating true/false
     */
    function has_offspring()
    {
        $qb =& $this->get_offspring_qb();
        $qb->set_limit(1);
        $results = $qb->count_unchecked();
        if (!empty($results))
        {
            return true;
        }
        return false;
    }

    /**
     * Gets the children of this dog
     *
     * @return array multidimensional, keyed by birthdate value of which is md array keyed by other parent value of which is array of dogs (or false on critical failure)
     */
    function get_offspring()
    {
        $ret = array();
        $qb =& $this->get_offspring_qb();
        $qb->add_order('dob', 'ASC');
        $qb->add_order('name', 'ASC');
        $children = $qb->execute();
        if (!is_array($children))
        {
            return false;
        }
        foreach ($children as $child)
        {
            if ($child->dob)
            {            
                $dob_ts = strtotime($child->dob);
                $dob_hr = date('Y-m-d', $dob_ts);
            }
            else
            {
                $dob_hr = 'unknown';
            }
            if (!isset($ret[$dob_hr]))
            {
                $ret[$dob_hr] = array();
            }
            if ($child->sire == $this->id)
            {
                $other_parent = $child->dam;
            }
            else
            {
                $other_parent = $child->sire;
            }
            if (!isset($ret[$dob_hr][$other_parent]))
            {
                $ret[$dob_hr][$other_parent] = array();
            }
            $ret[$dob_hr][$other_parent][] = $child;
            unset($child);
        }
        unset($children);
        return $ret;
    }

    /** 
     * Prepares a QB for result queries
     *
     * @return midgard_query_builder ready for other constraints
     */
    function &get_result_qb()
    {
        $qb = net_fernmark_pedigree_dog_result_dba::new_query_builder();
        $qb->add_constraint('dog', '=', (int)$this->id);
        return $qb;
    }

    /**
     * Whether current dog has any results
     *
     * @return boolean indicating true/false
     */
    function has_results()
    {
        $qb =& $this->get_result_qb();
        $qb->set_limit(1);
        $results = $qb->count_unchecked();
        if (!empty($results))
        {
            return true;
        }
        return false;
    }

    /**
     * Gets the children of this dog
     *
     * @return array of result objects
     */
    function get_results()
    {
        $qb =& $this->get_result_qb();
        $qb->add_order('date', 'ASC');
        $qb->add_order('eventname', 'ASC');
        $results = $qb->execute();
        if (!is_array($results))
        {
            return false;
        }
        return $results;
    }    
}

?>