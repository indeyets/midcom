<?php
/**
 * Class for rendering maemo calendar panels
 *
 * @package org.maemo.calendarpanel
 * @author Jerry Jalava, http://protoblogr.net
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link http://www.microformats.org/wiki/hcalendar hCalendar microformat
 */

/**
 * @package org.maemo.calendarpanel 
 */
class org_maemo_calendarpanel_leaf extends midcom_baseclasses_components_purecode
{
    var $name = '';
    var $title = '';
    
    /**
     * Initializes the class
     *
     */
    function org_maemo_calendarpanel_leaf()
    {
        $this->_component = 'org.maemo.calendarpanel';        
        parent::midcom_baseclasses_components_purecode();
    }

    /**
     * Leafs should override this!
     */
    function generate_content() {return;}

    /**
     * Leafs should override this!
     */    
    function _render_menu() {return;}

}