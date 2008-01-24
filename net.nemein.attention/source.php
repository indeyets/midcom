<?php
/**
 * @package net.nemein.attention
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for user attention sources
 *
 * @package net.nemein.attention
 */
class net_nemein_attention_source_dba extends __net_nemein_attention_source_dba
{
    var $_use_rcs = false;
    function __construct($id = null)
    {
        return parent::__construct($id);
    }
    
    function get_source($source_key, $person_id, $profile, $explicit = false)
    {
        $qb = net_nemein_attention_source_dba::new_query_builder();
        $qb->add_constraint('url', '=', $source_key);
        $qb->add_constraint('person', '=', (int) $person_id);
        $qb->add_constraint('profile', '=', $profile);
        $qb->add_constraint('explicit', '=', $explicit);
        $sources = $qb->execute();
        if (count($sources) == 0)
        {        
            // Create new source
            $source = new net_nemein_attention_source_dba();
            $source->url = $source_key;
            $source->person = $person_id;
            $source->profile = $profile;
            $source->explicit = $explicit;
            if (!$source->create())
            {
                // TODO: Exception
                return null;
            }
            return $source;
        }
        
        return $sources[0];
    }
}
?>