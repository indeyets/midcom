<?php
/**
 * @package midcom.baseclasses
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:article.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM level replacement for the Midgard Article record with framework support.
 *
 * Note, as with all MidCOM DB layer objects, you should not use the GetBy*
 * operations directly, instead, you have to use the constructor's $id parameter.
 *
 * Also, all QueryBuilder operations need to be done by the factory class
 * obtainable as midcom_application::dbfactory.
 *
 * This class uses a auto-generated base class provided by midcom_services_dbclassloader.
 *
 * <i>Automatic updaets:</i>
 *
 * - The system automatically resets invalid $author members, as they would break
 *   mgd_list_*article* style queries. The member is set to the ID of the current
 *   user or, if that one is not accessible, to 1, which is the Midgard Administrator
 *   user ID.
 *
 * @package midcom.baseclasses
 * @see midcom_services_dbclassloader
 */
class midcom_baseclasses_database_article extends __midcom_baseclasses_database_article
{
    function midcom_baseclasses_database_article($id = null)
    {
        parent::__midcom_baseclasses_database_article($id);
    }
    
    /**
     * Check that article by same name doesn't exist in the topic in
     * Classic Midgard API fashion.
     *
     * @return boolean Whether the name is acceptable
     */
    function _check_name()
    {
        if ($this->name == '')
        {
            return true;
        }

        $mc = midcom_baseclasses_database_article::new_collector('name', $this->name);
        $mc->add_constraint('name', '=', $this->name);

        if ($this->id)
        {
            $mc->add_constraint('id', '<>', $this->id);
        }

        if ($this->up != 0)
        {
            // "Reply article", we care only about the up field
            $mc->add_constraint('up', '=', $this->up);
        }
        else
        {
            // Toplevel article, check topic
            $mc->add_constraint('topic', '=', $this->topic);
        }

        // Run the uniqueness check
        if ($mc->count > 0)
        {
            // This name is already taken
            mgd_set_errno(MGD_ERR_OBJECT_NAME_EXISTS);
            return false;
        }
        return true;
    }

    /**
     * Internal helper function, invoked during create and update which
     * validates the author field to be correct.
     *
     * If the author cannot be found, the current user is used instead.
     *
     * This has mainly been introduced to aid backwards compatibility with
     * legacy Midgard code. If a author id is invalid, the mgd_list_* calls
     * will fail.
     */
    function _check_author()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $author =& $this->get_author();
        if (! $author)
        {
            if (is_null($_MIDCOM->auth->user))
            {
                debug_add("The author {$this->author} is invalid and no user is authenticated, reverting it to the Midgard Admin user",
                    MIDCOM_LOG_INFO);
                $this->author = 1;
            }
            else
            {
                $user = $_MIDCOM->auth->user->get_storage();
                if (! $user)
                {
                    debug_add("The author {$this->author} is invalid and cannot get the storage object of the current user (permissions?), reverting it to the Midgard Admin user",
                        MIDCOM_LOG_INFO);
                    $this->author = 1;
                }
                else
                {
                    debug_add("The author {$this->author} is invalid, reverting it to the current user", MIDCOM_LOG_INFO);
                    $this->author = $user->id;
                }
            }
        }
        debug_pop();
    }

    /**
     * Returns the User-Object representing the author of the record. This is the
     * object behind the $author member of this object.
     *
     * If you need the corresponding Person object, use
     * midcom_core_user::get_storage(), as the full access might be
     * restricted due to privileges.
     *
     * @return midcom_core_user A reference (!) to the user which created the object, or false on failure.
     */
    function & get_author()
    {
        $author =& $_MIDCOM->auth->get_user($this->author);
        return $author;
    }

    /**
     * Returns the Parent of the Article. This can either be another article if we have
     * an reply article, or a topic otherwise.
     *
     * @return MidgardObject Parent Article or topic.
     */
    function get_parent_guid_uncached()
    {
        if (   isset($this->up)
            && $this->up != 0)
        {
            return midcom_baseclasses_database_article::_get_parent_guid_uncached_static_article($this->up);
        }
        return midcom_baseclasses_database_article::_get_parent_guid_uncached_static_topic($this->topic);
    }

    /**
     * Statically callable method to get parent guid when object guid is given
     * 
     * Uses midgard_collector to avoid unneccessary full object loads
     *
     * @param guid $guid guid of topic to get the parent for
     */
    function get_parent_guid_uncached_static($guid)
    {
        if (empty($guid))
        {
            return null;
        }
        $mc_article = midcom_baseclasses_database_article::new_collector('guid', $guid);
        $mc_article->add_value_property('up');
        $mc_article->add_value_property('topic');
        if (!$mc_article->execute())
        {
            // Error
            return null;
        }
        $mc_article_keys = $mc_article->list_keys();
        list ($key, $copy) = each ($mc_article_keys);
        $up = $mc_article->get_subkey($key, 'up');
        if ($up === false)
        {
            // error
            return null;
        }
        if (!empty($up))
        {
            return midcom_baseclasses_database_article::_get_parent_guid_uncached_static_article($up);
        }
        $topic = $mc_article->get_subkey($key, 'topic');
        if ($topic === false)
        {
            // error
            return null;
        }
        return midcom_baseclasses_database_article::_get_parent_guid_uncached_static_topic($topic);
    }

    /**
     * Get topic guid statically
     *
     * used by get_parent_guid_uncached_static
     *
     * @param id $parent_id id of topic to get the guid for
     */
    function _get_parent_guid_uncached_static_topic($parent_id)
    {
        if (empty($parent_id))
        {
            return null;
        }
        $mc_parent = midcom_baseclasses_database_topic::new_collector('id', $parent_id);
        $mc_parent->add_value_property('guid');
        if (!$mc_parent->execute())
        {
            // Error
            return null;
        }
        $mc_parent_keys = $mc_parent->list_keys();
        list ($key, $copy) = each ($mc_parent_keys);
        $parent_guid = $mc_parent->get_subkey($key, 'guid');
        if ($parent_guid === false)
        {
            // Error
            return null;
        }
        return $parent_guid;
    }

    /**
     * Get article guid statically
     *
     * used by get_parent_guid_uncached_static
     *
     * @param id $parent_id id of topic to get the guid for
     */
    function _get_parent_guid_uncached_static_article($parent_id)
    {
        if (empty($parent_id))
        {
            return null;
        }
        $mc_parent = midcom_baseclasses_database_article::new_collector('id', $parent_id);
        $mc_parent->add_value_property('guid');
        if (!$mc_parent->execute())
        {
            // Error
            return null;
        }
        $mc_parent_keys = $mc_parent->list_keys();
        list ($key, $copy) = each ($mc_parent_keys);
        $parent_guid = $mc_parent->get_subkey($key, 'guid');
        if ($parent_guid === false)
        {
            // Error
            return null;
        }
        return $parent_guid;
    }

    function get_dba_parent_class()
    {
        if (   isset($this->up)
            && $this->up != 0)
        {
            return 'midcom_baseclasses_database_article';
        }
        return 'midcom_baseclasses_database_topic';
    }

    /**
     * Pre-Creation hook, which validates the $author field for correctness.
     *
     * @return bool Indicating success.
     */
    function _on_creating()
    {
        $this->_check_author();
        
        if (!$this->_check_name())
        {
            return false;
        }
        
        return true;
    }

    /**
     * Pre-Update hook, which validates the $author field for correctness.
     *
     * @return bool Indicating success.
     */
    function _on_updating()
    {
        $this->_check_author();
        
        if (!$this->_check_name())
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Generates a new URL-safe name.
     *
     * @access protected
     */
    function _on_created()
    {
        midcom_baseclasses_core_dbobject::generate_urlname($this);
        return true;
    }
    
    /**
     * Generates a new URL-safe name.
     *
     * @access protected
     */
    function _on_updated()
    {
        midcom_baseclasses_core_dbobject::generate_urlname($this);
        return true;
    } 
}

?>