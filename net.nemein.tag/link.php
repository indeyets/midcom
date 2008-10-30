<?php
/**
 * @package net.nemein.tag
 * @author Henri Bergius, http://bergie.iki.fi
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapped class for access to stored queries
 *
 * @package net.nemein.tag
 */
class net_nemein_tag_link_dba extends __net_nemein_tag_link_dba
{
    function __construct($id = null)
    {
        $this->_use_rcs = false;
        return parent::__construct($id);
    }

    function get_parent_guid_uncached()
    {
        if (empty($this->fromGuid))
        {
            return null;
        }
        $class = $this->fromClass;
        if (!class_exists($class))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Class '{$class}' is missing, trying to find it", MIDCOM_LOG_WARN);
            debug_pop();
            if (empty($this->fromComponent))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("\$this->fromComponent is empty, don't know how to load missing class '{$class}'", MIDCOM_LOG_ERROR);
                debug_pop();
                return null;
            }
            if (!$_MIDCOM->componentloader->load_graceful($this->fromComponent))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Could not load component '{$this->fromComponent}' (to load missing class '{$class}')", MIDCOM_LOG_ERROR);
                debug_pop();
                return null;
            }
        }
        $parent = new $class($this->fromGuid);
        if (   !is_object($parent)
            || empty($parent->guid))
        {
            return null;
        }
        return $parent->guid;
    }

    function get_label()
    {
        $mc = net_nemein_tag_tag_dba::new_collector('id', $this->tag);
        $mc->add_value_property('tag');
        $mc->execute();
        $tag_guids = $mc->list_keys();
        foreach ($tag_guids as $guid => $array)
        {
            return net_nemein_tag_handler::tag_link2tagname($mc->get_subkey($guid, 'tag'), $this->value, $this->context);
        }
        return $this->guid;
    }

    function _sanity_check()
    {
        if (   empty($this->fromGuid)
            || empty($this->fromClass)
            || empty($this->tag)
            )
        {
            // All required properties not defined
            return false;
        }
        return true;
    }

    function _on_creating()
    {
        if (!$this->_sanity_check())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Sanity check failed with tag #{$this->tag}", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        if ($this->_check_duplicates() > 0)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Duplicate check failed with tag #{$this->tag}", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        return true;
    }

    function _on_created()
    {
        if ($this->context == 'geo')
        {
            $this->_geotag();
        }

        return parent::_on_created();
    }

    function _on_updating()
    {
        if (!$this->_sanity_check())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Sanity check failed with tag #{$this->tag}", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        if ($this->_check_duplicates() > 0)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Duplicate check failed with tag #{$this->tag}", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        return true;
    }

    function _on_updated()
    {
        if ($this->context == 'geo')
        {
            $this->_geotag();
        }

        return parent::_on_updated();
    }


    function _check_duplicates()
    {
        $qb = net_nemein_tag_link_dba::new_query_builder();
        if ($this->id)
        {
            $qb->add_constraint('id', '<>', $this->id);
        }
        $qb->add_constraint('fromGuid', '=', $this->fromGuid);
        $qb->add_constraint('tag', '=', $this->tag);
        $qb->add_constraint('context', '=', $this->context);

        return $qb->count_unchecked();
    }

    /**
     * Handle storing Flickr-style geo tags to org.routamc.positioning
     * storage should be to org_routamc_positioning_location_dba object
     * with relation ORG_ROUTAMC_POSITIONING_RELATION_IN
     *
     * @return boolean
     */
    function _geotag()
    {
        if (!$GLOBALS['midcom_config']['positioning_enable'])
        {
            return false;
        }

        $_MIDCOM->load_library('org.routamc.positioning');

        // Get all "geo" tags of the object
        $object = $_MIDCOM->dbfactory->get_object_by_guid($this->fromGuid);
        $geotags = net_nemein_tag_handler::get_object_machine_tags_in_context($object, 'geo');

        $position = array
        (
            'longitude' => null,
            'latitude'  => null,
            'altitude'  => null,
        );

        foreach ($geotags as $key => $value)
        {
            switch ($key)
            {
                case 'lon':
                case 'lng':
                case 'long':
                    $position['longitude'] = $value;
                    break;

                case 'lat':
                    $position['latitude'] = $value;
                    break;

                case 'alt':
                    $position['altitide'] = $value;
                    break;
            }
        }

        if (   is_null($position['longitude'])
            || is_null($position['latitude']))
        {
            // Not enough information for positioning, we need both lon and lat
            return false;
        }

        $object_location = new org_routamc_positioning_location_dba();
        $object_location->relation = ORG_ROUTAMC_POSITIONING_RELATION_IN;
        $object_location->parent = $this->fromGuid;
        $object_location->parentclass = $this->fromClass;
        $object_location->parentcomponent = $this->fromComponent;
        $object_location->date = $this->metadata->published;
        $object_location->longitude = $position['longitude'];
        $object_location->latitude = $position['latitude'];
        $object_location->altitude = $position['altitude'];

        return $object_location->create();
    }

    /**
     * By default all authenticated users should be able to do
     * whatever they wish with tag objects, later we can add
     * restrictions on object level as necessary.
     */
    function get_class_magic_default_privileges()
    {
        $privileges = parent::get_class_magic_default_privileges();
        $privileges['USERS']['midgard:create']  = MIDCOM_PRIVILEGE_ALLOW;
        $privileges['USERS']['midgard:update']  = MIDCOM_PRIVILEGE_ALLOW;
        $privileges['USERS']['midgard:read']    = MIDCOM_PRIVILEGE_ALLOW;
        return $privileges;
    }
}
?>