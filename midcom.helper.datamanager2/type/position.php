<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: blobs.php 11967 2007-09-03 10:34:48Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 position management type.
 *
 * This type allows you to position objects in the Midgard database geographically.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_type_position extends midcom_helper_datamanager2_type
{
    var $location = null;
    var $object = null;
    var $relation = 30; // ORG_ROUTAMC_POSITIONING_RELATION_LOCATED

    function _on_initialize()
    {
        return $_MIDCOM->load_library('org.routamc.positioning');
    }    

    /**
     * This function loads all known attachments from the storage object. It
     * will leave the field empty in case the storage object is null.
     */
    function convert_from_storage($source)
    {
        if ($this->storage->object === null)
        {
            // We don't have a storage object, skip the rest of the operations.
            $this->location = new org_routamc_positioning_location_dba();
            return;
        }
        
        $this->object = new org_routamc_positioning_object($this->storage->object);
        
        $this->location = $this->object->seek_location_object();
        if (is_null($this->location))
        {
            $this->location = new org_routamc_positioning_location_dba();
        }
    }

    function convert_to_storage()
    {
        $this->location->relation = $this->relation;
        if ($this->location->guid)
        {
            $this->location->update();
        }
        else
        {
            $this->location->create();
        }

        return '';
    }

    function convert_from_csv ($source)
    {
        // TODO: Not yet supported
        return '';
    }

    function convert_to_csv()
    {
        return "{$this->location->latitude},{$this->location->longitude},{$this->location->altitude}";
    }

    function convert_to_html()
    {
        $latitude_string = org_routamc_positioning_utils::pretty_print_coordinate($this->location->latitude);
        $latitude_string .= ($this->location->latitude > 0) ? " N" : " S";
        $longitude_string = org_routamc_positioning_utils::pretty_print_coordinate($this->location->longitude);
        $longitude_string .= ($this->location->longitude > 0) ? " E" : " W";
        
        $result  = "<div class=\"geo\">\n";
        $result .= "    <abbr class=\"latitude\" title=\"{$this->location->latitude}\">{$latitude_string}</abbr>\n";
        $result .= "    <abbr class=\"longitude\" title=\"{$this->location->longitude}\">{$longitude_string}</abbr>\n";
        $result .= "</div>\n";
        
        // TODO: Adr Microformat for civic location
        
        return $result;
    }
}

?>