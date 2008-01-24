<?php
/**
 * @package net.nemein.attention
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for user attention concepts
 *
 * @package net.nemein.attention
 */
class net_nemein_attention_concept_dba extends __net_nemein_attention_concept_dba
{
    var $_use_rcs = false;
    function __construct($id = null)
    {
        return parent::__construct($id);
    }
    
    function get_concept($concept_key, $person_id, $profile, $explicit = false)
    {
        $qb = net_nemein_attention_concept_dba::new_query_builder();
        $qb->add_constraint('concept', '=', $concept_key);
        $qb->add_constraint('person', '=', (int) $person_id);
        $qb->add_constraint('profile', '=', $profile);
        $qb->add_constraint('explicit', '=', $explicit);
        $concepts = $qb->execute();
        if (count($concepts) == 0)
        {        
            // Create new concept
            $concept = new net_nemein_attention_concept_dba();
            $concept->concept = $concept_key;
            $concept->person = $person_id;
            $concept->profile = $profile;
            $concept->explicit = $explicit;
            if (!$concept->create())
            {
                // TODO: Exception
                return null;
            }
            return $concept;
        }
        
        return $concepts[0];
    }
}
?>