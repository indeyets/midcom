<?php
/**
 * @package org.maemo.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: categorylister.php 3339 2006-05-02 11:48:56Z torben $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package org.maemo.calendar
 */

class org_maemo_calendar_callbacks_timezones extends midcom_baseclasses_components_purecode
{
    /**
     * The array with the data we're working on.
     *
     * @var array
     * @access private
     */
    var $_data = null;

    /**
     * The callback class instance, a callback matching the signature required for the DM2 select
     * type callbacks.
     *
     * @var object
     * @access private
     */
    var $_callback = null;

    /**
     * Initializes the class to the category listing in the configuration. It does the neccessary
     * postprocessing to move the configuration syntax to the rendering one.
     *
     */
    function org_maemo_calendar_callbacks_timezones($arg)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $this->_component = 'org.maemo.calendar';

        parent::midcom_baseclasses_components_purecode();

        $this->_data = Array();
        
        $timezone_identifiers = timezone_identifiers_list();
        $i = 0;
        foreach ($timezone_identifiers as $zone) {
            $zone = explode('/',$zone);
            if (isset($zone[1]))
            {
                $zones[$i]['continent'] = $zone[0];
                $zones[$i]['city'] = $zone[1];
                $i++;                        
            }
        }
        asort($zones);
        foreach ($zones as $zone) {
            extract($zone);
            if (   $continent == 'Africa'
                || $continent == 'America'
                || $continent == 'Antarctica'
                || $continent == 'Arctic'
                || $continent == 'Asia'
                || $continent == 'Atlantic'
                || $continent == 'Australia'
                || $continent == 'Europe'
                || $continent == 'Indian'
                || $continent == 'Pacific')
            {
                if ($city != '')
                {
                    $key = "{$continent}/{$city}";
                    $this->_data[$key] = $continent . "/" . str_replace('_',' ',$city);
                }
                else
                {
                    $this->_data[$continent] = $continent;
                }
            }
        }        
        
        //debug_print_r('_data: ',$this->_data);
        
        debug_pop();
    }

    /** Ignored. */
    function set_type(&$type) {}

    function get_name_for_key($key)
    {
        $name = $this->_data[$key];
        debug_add("get_name_for_key {$key} = {$name}");
        return $name;
    }

    function key_exists($key)
    {
        debug_add("key_exists {$key}");
        return array_key_exists($key, $this->_data);
    }

    function list_all()
    {
        return $this->_data;
    }

}
?>