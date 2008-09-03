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
class net_nemein_attention_importer_apml extends net_nemein_attention_importer
{
    var $concepts = array();
    
    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function net_nemein_attention_importer_apml()
    {
         parent::__construct();
    }
    
    /**
     * Read and import concepts from a data SimpleXML object
     */
    function import_concepts($data, $person_id, $profile = '', $explicit = false)
    {
        foreach ($data->Concepts->Concept as $conceptxml)
        {
            $concept_array = array();
            $attributes = $conceptxml->attributes();
            foreach ($attributes as $key => $value)
            {
                switch ($key)
                {
                    case 'value':
                        $concept_array['value'] = (float) $value;
                        break;
                    case 'updated':
                        $concept_array['updated'] = strtotime((string) $value);
                        break;
                    default:
                        $concept_array[$key] = (string) $value;
                        break;
                }
            }
            
            if (!isset($concept_array['key']))
            {
                // Undefined concept
                continue;
            }

            $concept = net_nemein_attention_concept_dba::get_concept($concept_array['key'], $person_id, $profile, $explicit);
            if (is_null($concept))
            {
                // Skip this one
                continue;
            }
            
            foreach ($concept_array as $key => $value)
            {
                switch ($key)
                {
                    case 'value':
                        $concept->value = $value;
                        break;
                    case 'updated':
                        $concept->metadata->published = $value;
                        break;
                    case 'from':
                        $concept->source = $value;
                        break;
                    default:
                        // Fall back to params
                        $concept->parameter('net.nemein.attention', "apml:{$key}", $value);
                        break;
                }
            }
            
            if (!$concept->update())
            {
                continue;
            }
            
            $this->concepts[] = $concept;         
        }
    }

    /**
     * Import APML file
     *
     * @param Array $apml APML file URL or path
     * @param int $person_id ID of person owning the attention profile
     * @return boolean Indicating success.
     */
    function import($apml, $person_id)
    {
        $simplexml = simplexml_load_file($apml);

        if (!isset($simplexml->Body->Profile))
        {
            return false;
        }
        
        $profilexml = $simplexml->Body->Profile;
        $profile_attrs = $profilexml->attributes();
        if (   !isset($profile_attrs['name'])
            || $profile_attrs['name'] == 'default')
        {
            $profile = '';
        }
        else
        {
            $profile = (string) $profile_attrs['name'];
        }

        if (isset($profilexml->ImplicitData))
        {
            $this->import_concepts($profilexml->ImplicitData, $person_id, $profile);
        }
        
        if (isset($profilexml->ExplicitData))
        {
            $this->import_concepts($profilexml->ExplicitData, $person_id, $profile, true);
        }

        return true;
    }
}
?>