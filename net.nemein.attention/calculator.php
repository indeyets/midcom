<?php
/**
 * @package net.nemein.attention
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Attention profile analyzer class.
 *
 * @package net.nemein.attention
 */
class net_nemein_attention_calculator extends midcom_baseclasses_components_purecode
{
    /**
     * Cached array of user's attention nodes
     */
    var $user_nodes = array();
    
    /**
     * User to rate attention for
     */
    var $user = null;
    
    /**
     * Profile to query attention for. By default all profiles are queried
     */
    var $profile = null;

    /**
     * Initializes the class.
     *
     * @param object $user     Midgard Person object to query attention for
     * @param string $profile  Attention profile to use
     */
    function __construct($user = null, $profile = null)
    {
        $this->_component = 'net.nemein.attention';
        parent::__construct();
         
        if (   $user
            && is_object($user)
            && $user->guid)
        {
            $this->user = $user;
        }
        elseif ($_MIDCOM->auth->user)
        {
            $this->user = $_MIDCOM->auth->user->get_storage();
        }
        
        if ($profile)
        {
            $this->profile = $profile;
        }
    }
    
    /**
     * Read user's concepts from database
     */
    private function read_user_concepts()
    {
        if (isset($this->user_nodes['concepts']))
        {
            // We already have the concepts populated
            return;
        }
        
        $this->user_nodes['concepts'] = array();
        
        if (!$this->user)
        {
            // No user, no attention data
            return;
        }
        
        $qb = net_nemein_attention_concept_dba::new_query_builder();
        $qb->add_constraint('person', '=', $this->user->id);
        
        if ($this->profile)
        {
            $qb->add_constraint('profile', '=', $this->profile);
        }
        
        $concepts = $qb->execute();
        foreach ($concepts as $concept)
        {
            $this->user_nodes['concepts'][$concept->concept] = $concept->value;
        }
    }
    
    /**
     * Give attention score for a set of concepts. 0 is neutral attention.
     *
     * @param array $concepts   Array of concepts (keywords) to rate for
     * @return float Combined attention score of the concepts
     */
    function rate_concepts($concepts)
    {
        // Read user's attention concept nodes
        $this->read_user_concepts();
        
        $score = 0;
        
        if (empty($this->user_nodes['concepts']))
        {
            return $score;
        }
        
        foreach ($concepts as $concept)
        {
            if (isset($this->user_nodes['concepts'][$concept]))
            {
                $score += $this->user_nodes['concepts'][$concept];
            }
        }
        
        return $score;
    }

    /**
     * Give attention score for a set of authors. 0 is neutral attention.
     *
     * @param array $authors   Array of authors to rate for
     * @return float Combined attention score of the authors
     */
    function rate_authors($authors)
    {
        return 0;
    }

    /**
     * Give attention score for a source. 0 is neutral attention.
     *
     * @param string $source   Source to rate for
     * @return float Combined attention score of a source
     */ 
    function rate_source($source)
    {
        return 0;
    }

    /**
     * Give attention score for a Midgard object. 0 is neutral attention.
     *
     * This method reads the object for possible concepts, authors and sources
     * and makes a combination rate based on them.
     *
     * @param string $source   Source to rate for
     * @return float Combined attention score of a source
     */ 
    function rate_object($object)
    {
        $score = 0;
        
        // Read object tags as concepts
        $_MIDCOM->load_library('net.nemein.tag');
        $concepts = array();
        $tags = net_nemein_tag_handler::get_tags_by_guid($object->guid);
        foreach ($tags as $tag => $url)
        {
            $concepts[] = $tag;
        }
        
        // Read possible article categories as concepts too
        if (   is_a($object, 'midcom_baseclasses_database_article')
            && strpos($object->extra1, '|') !== false)
        {
            $categories = explode('|', substr($object->extra1, 1, -1));
            foreach ($categories as $category)
            {
                if (empty($category))
                {
                    continue;
                }
                
                $tag = strtolower($category);
                if (in_array($tag, $concepts))
                {
                    // We have this already from tags, skip
                    continue;
                }
                
                $concepts[] = $tag;
            }
        }
        
        // Rate by concepts
        $score += $this->rate_concepts($concepts);
        
        return $score;
    }
}
?>