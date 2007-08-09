<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:_basicnav.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This class is a helper for undeleting objects and their children
 *
 * Example usage:
 *
 *    $undeleter = new midcom_helper_undeleter('midgard_topic', 'ded2437586408cd03cd4582ba45b91f2');
 *    $undeleter->undelete_tree();
 *
 * @package midcom
 */
class midcom_helper_undeleter
{
    var $class = '';
    var $guid = '';
    var $object = null;
    
    /**
     * Constructor
     */
    function midcom_helper_undeleter($class, $guid)
    {
        $this->class = $class;
        $this->guid = $guid;
    }
    
    /**
     * Undelete only the object and its attachments and parameters
     */
    function undelete()
    {
        if (!class_exists($this->class))
        {
            // Exception
        }
        
        // TODO: We can get rid of this once Piotras moves the undelete method to 
        switch ($this->class)
        {
            case 'midgard_topic':
                if (!midgard_topic::undelete($guid))
                {
                    return false;
                }
                break;
            case 'midgard_article':
                if (!midgard_article::undelete($guid))
                {
                    return false;
                }
                break;
            default:
                return false;
        }
        
        $class = $this->class;
        $this->object = new $class($guid);
        
        // Undelete generic children
        $this->undelete_parameters();
        $this->undelete_attachments();
        
        return true;
    }

    /**
     * Undelete only object and all its children
     */
    function undelete_tree()
    {
        if (!$this->undelete())
        {
            return false;
        }
        
        // Undelete type-specific children
        switch ($this->class)
        {
            case 'midgard_topic':
                $this->undelete_midgard_topic();
                break;
        }
        // FIXME: We can really do this via reflection once it moves to a proper library from m.a.asgard
        
        return true;
    }
    
    function undelete_parameters()
    {
        $qb = new midgard_query_builder('midgard_parameter');
        $qb->include_deleted();
        $qb->add_constraint('parentguid', '=', $this->guid);
        $qb->add_constraint('metadata.deleted', '=', true);
        $params = $qb->execute();
        foreach ($params as $param)
        {
            midgard_parameter::undelete($param->guid);
        }
    }
    
    function undelete_attachments()
    {
        $qb = new midgard_query_builder('midgard_attachment');
        $qb->include_deleted();
        $qb->add_constraint('parentguid', '=', $this->guid);
        $qb->add_constraint('metadata.deleted', '=', true);
        $atts = $qb->execute();
        foreach ($atts as $att)
        {
            midgard_parameter::undelete($att->guid);
        }
    }
    
    function undelete_midgard_topic()
    {
        $qb = new midgard_query_builder('midgard_topic');
        $qb->include_deleted();
        $qb->add_constraint('up', '=', $this->object->id);
        $qb->add_constraint('metadata.deleted', '=', true);
        $topics = $qb->execute();
        foreach ($topics as $topic)
        {
            $subundeleter = new midcom_helper_undeleter('midgard_topic', $topic->guid);
            $subundeleter->undelete_tree();
        }
        
        $art_qb = new midgard_query_builder('midgard_article');
        $art_qb->include_deleted();
        $art_qb->add_constraint('topic', '=', $this->object->id);
        $art_qb->add_constraint('metadata.deleted', '=', true);
        $articles = $art_qb->execute();
        foreach ($articles as $article)
        {
            $subundeleter = new midcom_helper_undeleter('midgard_article', $article->guid);
            $subundeleter->undelete_tree();
        }
    }
}
?>