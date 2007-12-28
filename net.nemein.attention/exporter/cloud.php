<?php
/**
 * @package net.nemein.attention
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Importer for APML files
 *
 * @package net.nemein.attention
 */
class net_nemein_attention_exporter_cloud extends net_nemein_attention_exporter
{   
    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function net_nemein_attention_exporter_cloud()
    {
         parent::net_nemein_attention_exporter();
    }

    function prepare_cloud($user_id, $profile = null)
    {
        $cloud = '';
        $qb = net_nemein_attention_concept_dba::new_query_builder();
        $qb->add_constraint('person', '=', $user_id);
        //$qb->add_constraint('explicit', '=', false);
        $qb->add_order('concept');
        
        if ($profile)
        {
            $qb->add_constraint('profile', '=', $profile);
        }
        
        $concepts = $qb->execute();
        $cloud .= "<ul class=\"cloud\">\n";
        foreach ($concepts as $concept)
        {
            $key = $concept->concept;
            $percentage = round($concept->value * 100);
            
            $vals = (int) ($concept->value * 100) / 20;
            
            if ($vals > 0)
            {
                while ($vals > 0)
                {
                    $vals--;
                    $key = "<em>{$key}</em>";
                }
            }
            else
            {
                while ($vals < 0)
                {
                    $vals++;
                    $key = "<small>{$key}</small>";
                }
            }
        
            $cloud .= "    <li title=\"{$concept->value} ({$percentage}%) score for {$concept->concept}\">{$key}</li>\n";
        }
        $cloud .= "</ul>\n";

        return $cloud;
    }
    
    function export($user, $profile = null)
    {
        echo $this->prepare_cloud($user->id, $profile);
    }
}
?>