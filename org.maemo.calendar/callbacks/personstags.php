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

require_once(MIDCOM_ROOT . '/org/maemo/calendar/common.php');

class org_maemo_calendar_callbacks_personstags extends midcom_baseclasses_components_purecode
{
    /**
     * The array with the data we're working on.
     *
     * @var array
     * @access private
     */
    var $_data = null;

    /**
     * The user who's tags are being listed.
     *
     * @var int
     * @access private
     */
    var $_user = null;

    /**
     * Initializes the class to the category listing in the configuration. It does the neccessary
     * postprocessing to move the configuration syntax to the rendering one.
     *
     * @param int $user The user who's tags to show.
     */
    function org_maemo_calendar_callbacks_personstags($options)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->_component = 'org.maemo.calendar';
        parent::midcom_baseclasses_components_purecode();
        
        $this->_process_options($options);
        
        $this->_data = array();
        $this->_user =& $_MIDCOM->auth->user;
        
        $only_public = false;

        if ($this->_user->guid != $_MIDCOM->auth->user->guid)
        {
            $only_public = true;
        }
        
        $person_tags = org_maemo_calendar_common::fetch_available_user_tags($this->_user->guid, $only_public);
        
        foreach ($person_tags as $i => $tag)
        {
            $this->_data[$tag['id']] = $tag;
        }
        
        debug_print_r('_data: ',$this->_data);
        
        debug_pop();
    }
    
    /**
     * Reads and validates the configuration options. Invalid options are logged
     * and ignored.
     */
    function _process_options($options)
    {
        debug_print_r("got options:",$options);
        
        if (   !is_array($options)
            || empty($options))
        {
            return;
        }
        
        if (array_key_exists('person', $options))
        {
            $this->_user =& new midcom_db_person($options['person_guid']);
        }
    }    

    /** Ignored. */
    function set_type(&$type) {}

    function get_name_for_key($key)
    {
        $name = $this->_data[$key]['name'];
        return $name;
    }
    
    function get_data_for_key($key)
    {
        $data = $this->_data[$key];
        return $data;
    }    

    function key_exists($key)
    {
        return array_key_exists($key, $this->_data);
    }

    function list_all()
    {
        return $this->_data;
    }
    
    function save_values($tags)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_r('got tags',$tags);
        
        foreach ($tags as $tag)
        {
            $data = $this->get_data_for_key($key);
            if (! org_maemo_calendar_common::save_user_tag($tag, $data, $this->_user->guid))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Saving of tag {$tag} failed! See debug log for more details.",
                    MIDCOM_LOG_INFO);
                debug_pop();            
            }
        }
        
        debug_pop();
        return true;
    }

}
?>