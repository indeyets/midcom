<?php
/**
 * @package net.nemein.alphabeticalindex
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: mail.php 11482 2007-08-06 09:59:38Z w_i $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Alphabetical index
 *
 * @package net.nemein.alphabeticalindex
 */
class net_nemein_alphabeticalindex_item extends __net_nemein_alphabeticalindex_item
{
    var $internal = false;
    
    function net_nemein_alphabeticalindex_item($id = null)
    {
        parent::__net_nemein_alphabeticalindex_item($id);
    }

    /**
     * Human-readable label for cases like Asgard navigation
     */
    function get_label()
    {
        if ($this->title)
        {
            return $this->title;
        }
        return "item #{$this->id}";
    }

    function _on_loaded()
    {
        if (!empty($this->objectGuid))
        {
            $this->internal = true;
            // Make sure we can actually see the internal object
            $object = $_MIDCOM->dbfactory->get_object_by_guid($this->objectGuid);
            if (   !$object
                || !isset($object->guid)
                || empty($object->guid))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Could not get object {$this->objectGuid}, errstr: " . mgd_errstr(), MIDCOM_LOG_WARN);
                debug_pop();
                return false;
            }
        }

        return true;
    }

    function resolve_url($echo=false)
    {
        $url = $this->url;
        
        if (mgd_is_guid($this->url))
        {
            $url = $_MIDCOM->permalinks->create_permalink($this->objectGuid);
        }
        else if (!empty($this->cachedUrl))
        {
            $url = $this->cachedUrl;
        }
        
        if (!$echo)
        {
            return $url;
        }
        echo $url;
    }

}

?>