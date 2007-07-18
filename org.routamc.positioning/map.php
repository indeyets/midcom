<?php
/**
 * @package org.routamc.positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: aerodrome.php 6154 2007-06-02 23:12:11Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for map display via the mapstraction library
 *
 * Example usage:
 *
 * $map = new org_routamc_positioning_map('my_example_map');
 * $map->add_object($article);
 * $map->add_object($another_article);
 * $map->show();
 *
 * @package org.routamc.positioning
 */
class org_routamc_positioning_map extends midcom_baseclasses_components_purecode
{
    /**
     * ID of the map
     * @access private
     */
    var $id = '';
    
    /**
     * Type of the map to use
     * @access private
     */
    var $type = 'google';
    
    /**
     * API key to use with the mapping service, if needed
     * @access private
     */
    var $api_key = '';
    
    /**
     * Markers to display on the map
     * @access private
     */
    var $markers = array();

    /**
     * Constructor
     *
     * @param string $id    Id string for the map
     */
    function org_routamc_positioning_map($id)
    {
        $this->id = $id;
        $this->_component = 'org.routamc.positioning';
        parent::midcom_baseclasses_components_purecode();
        
        $this->type = $this->_config->get('map_provider');
        $this->api_key = $this->_config->get('map_api_key');
    }
    
    /**
     * Add an object to the map
     *
     * @return boolean
     */
    function add_object($object)
    {
        $object_position = new org_routamc_positioning_object($object);
        $coordinates = $object_position->get_coordinates();
        if (is_null($coordinates))
        {
            return false;
        }
        
        $marker = array();
        $marker['coordinates'] = $coordinates;
        
        // TODO: Use reflection to get the label property
        if (isset($object->title))
        {
            $marker['title'] = $object->title;
        }
        elseif (isset($object->name))
        {
            $marker['title'] = $object->name;
        }
        else
        {
            $marker['title'] = $object->guid;
        }
        
        if (isset($object->abstract))
        {
            $marker['abstract'] = $object->abstract;
        }
        
        return $this->add_marker($marker);
    }
    
    /**
     * Add a marker to the map
     *
     * Marker array should contain the following:
     *
     * - coordinates array with latitude, longitude (and possibly altitude)
     * - title string
     *
     * In addition it may contain:
     *
     * - abstract string containing HTML to be shown in the infobubble
     * - icon string URL to image file
     *
     * @param array $marker Marker array
     *Ê@return boolean Whether the operation was successfull
     */
    function add_marker($marker)
    {
        // Perform sanity checks
        if (   !isset($marker['coordinates'])
            || !is_array($marker['coordinates'])
            || !isset($marker['coordinates']['latitude'])
            || !isset($marker['coordinates']['longitude']))
        {
            return false;
        }
        
        if (   !isset($marker['title'])
            || empty($marker['title']))
        {
            return false;
        }
        
        $this->markers[] = $marker;
        return true;
    }

    /**
     * Include the javascript files and code needed for map display
     */
    function add_jsfiles()
    {
        static $added = false;
        if ($added)
        {
            return false;
        }
        
        echo "<script type=\"text/javascript\" src=\"" . MIDCOM_STATIC_URL . "/org.routamc.positioning/mapstraction.js\"></script>\n";
        
        // TODO: We can remove this once mapstraction does the includes by itself
        switch ($this->type)
        {
            case 'microsoft':
                echo "<script type=\"text/javascript\" src=\"http://dev.virtualearth.net/mapcontrol/v3/mapcontrol.js\"></script>\n";
                break;
            case 'yahoo':
                echo "<script type=\"text/javascript\" src=\"http://api.maps.yahoo.com/ajaxymap?v=3.4&amp;appid=YellowMasp4R\"></script>\n";
                break;
            case 'google':
            // TODO: As soon as mapstraction supports openlayers OSM will be the default
            case 'openstreetmap':
            default:
                echo "<script type=\"text/javascript\" src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key={$this->api_key}\"></script>\n";
                break;
        }
        
        $added = true;
        return true;
    }
    
    /**
     * Display the map
     */
    function show($width = 300, $height = 200)
    {
        $this->add_jsfiles();
        
        // Show the map div
        echo "<div class=\"org_routamc_positioning_map\" id=\"{$this->id}\"";
        if (   !is_null($width)
            && !is_null($height))
        {
            echo " style=\"width: {$width}px; height: {$height}px\"";
        }
        echo "></div>\n";
        
        // Start mapstraction
        echo "<script type=\"text/javascript\">\n";
        echo "var mapstraction_{$this->id} = new Mapstraction('{$this->id}','{$this->type}');\n";
        
        if ($this->type == 'google')
        {
            // Workaround, Google requires you to start with a center
            echo "mapstraction_{$this->id}.setCenter(new LatLonPoint(0, 0));\n";
        }
        
        foreach ($this->markers as $marker)
        {
            $marker_instance = $this->create_js_marker($marker);
            echo "mapstraction_{$this->id}.addMarker({$marker_instance});\n";
        }
        echo "mapstraction_{$this->id}.addSmallControls();\n";
        echo "mapstraction_{$this->id}.autoCenterAndZoom();\n";
        
        echo "</script>\n";
    }
    
    /**
     * Create a marker javascript object and return its name
     */
    function create_js_marker($marker)
    {
        static $i = 0;
        $i++;

		// Just in case.. cast lat/lon to 'dot' delimited numbers
		$lat = number_format($marker['coordinates']['latitude'],6);
		$lon = number_format($marker['coordinates']['longitude'],6);

        echo "var marker_{$i} = new Marker(new LatLonPoint({$lat}, {$lon}))\n";
        
        echo "marker_{$i}.setLabel('{$marker['title']}');\n";
        
        if (isset($marker['abstract']))
        {
            echo "marker_{$i}.setInfoBubble('{$marker['abstract']}');\n";
        }
        
        // TODO: Set other marker properties
        
        return "marker_{$i}";
    }
}
?>