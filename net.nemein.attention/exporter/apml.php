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
class net_nemein_attention_exporter_apml extends net_nemein_attention_exporter
{   
    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function net_nemein_attention_exporter_apml()
    {
         parent::net_nemein_attention_exporter();
    }

    function prepare_data_element($apml, $profile, $explicit = false)
    {
        static $profiles = array();
        if (!isset($profiles[$profile]))
        {
            $profiles[$profile] = $apml->Body->addChild('Profile');
            $profiles[$profile]->addAttribute('name', $profile);
        }
    
        $data_attribute = 'ImplicitData';
        if ($explicit)
        {
            $data_attribute = 'ExplicitData';
        }
        if (!isset($profiles[$profile]->$data_attribute))
        {
            $profiles[$profile]->addChild($data_attribute);
        }
        $data =& $profiles[$profile]->$data_attribute;
    
        return $data;
    }
    
    function read_concepts($user_id, $apml, $profile = null)
    {
        // List concepts
        $qb = net_nemein_attention_concept_dba::new_query_builder();
        $qb->add_constraint('person', '=', $user_id);
        $qb->add_order('profile');
        $qb->add_order('explicit', 'ASC');
        if ($profile)
        {
            $qb->add_constraint('profile', '=', $profile);
        }
        $concepts = $qb->execute();
        foreach ($concepts as $concept)
        {
            // Prepare the XML data
            if (!$concept->profile)
            {
                $concept->profile = 'default';
            }
            $data =& $this->prepare_data_element(&$apml, $concept->profile, $concept->explicit);
            if (!isset($data->Concepts))
            {
                $data->addChild('Concepts');
            }
            
            // And then actually add the concept
            $concept_element = $data->Concepts->addChild('Concept');
            $concept_element->addAttribute('key', $concept->concept);
            $concept_element->addAttribute('value', $concept->value);
            $concept_element->addAttribute('from', $concept->source);
            $concept_element->addAttribute('updated', date('c', $concept->metadata->published));
        }
    }
    
    function read_sources($user_id, $apml, $profile = null)
    {
        // List sources
        $qb = net_nemein_attention_source_dba::new_query_builder();
        $qb->add_constraint('person', '=', $user_id);
        $qb->add_order('profile');
        $qb->add_order('explicit', 'ASC');
        if ($profile)
        {
            $qb->add_constraint('profile', '=', $profile);
        }
        $sources = $qb->execute();
        foreach ($sources as $source)
        {
            // Prepare the XML data
            if (!$source->profile)
            {
                $source->profile = 'default';
            }
            $data =& $this->prepare_data_element(&$apml, $source->profile, $source->explicit);
            if (!isset($data->Sources))
            {
                $data->addChild('Sources');
            }
            
            // And then actually add the source
            $source_element = $data->Sources->addChild('Source');
            $source_element->addAttribute('key', $source->url);
            $source_element->addAttribute('value', $source->value);
            $source_element->addAttribute('from', $source->source);
            
            if ($source->type)
            {
                $source_element->addAttribute('type', $source->type);
            }
            
            if ($source->title)
            {
                $source_element->addAttribute('name', $source->title);
            }
            
            $source_element->addAttribute('updated', date('c', $source->metadata->published));
        }
    }
    
    function prepare_apml($user, $profile = null)
    {
        // Prepare APML
        $apml = new SimpleXMLElement('<APML></APML>');
        $apml->addAttribute('xmlns', 'http://www.apml.org/apml-0.6');
        $apml->addAttribute('version', '0.6');
    
        // APML headers
        $head = $apml->addChild('Head');
        $head->addChild('Title', "APML for {$user->name}");
        $head->addChild('Generator', "Midgard/" . mgd_version() . ' MidCOM/' . $GLOBALS['midcom_version'] . ' PHP/' . phpversion());
        $head->addChild('DateCreated', date('c'));

        // APML content
        $body = $apml->addChild('Body');
        
        // Read concepts from DB
        $this->read_concepts($user->id, &$apml, $profile);
        
        // Read sources from DB
        $this->read_sources($user->id, &$apml, $profile);
        
        return $apml;
    }
    
    function export($user, $profile = null)
    {
        // Correct content type
        $_MIDCOM->header('content-type: text/xml');

        $apml = $this->prepare_apml($user, $profile);
        
        echo $apml->asXml();
    }
}
?>