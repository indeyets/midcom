<?php
/**
 * Class for rendering maemo calendar panel widget
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
class org_maemo_calendarpanel extends midcom_baseclasses_components_purecode
{
    
    var $leafs = array();
    
    /**
     * Initializes the class
     *
     */
    function org_maemo_calendarpanel()
    {
        $this->_component = 'org.maemo.calendarpanel';
        parent::midcom_baseclasses_components_purecode();

        // Make the calendar pretty
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.maemo.calendarpanel/styles/panel.css",
            )
        );

        // Load required Javascript files
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/org.maemo.calendarpanel/js/panel.js');

        //$script = 'jQuery(\'#main-panel-content\').animate({ height: \'toggle\' }, \'fast\');';
        $script = 'jQuery(\'#main-panel-content\').toggle();';
        $_MIDCOM->add_jquery_state_script($script);
    }
    
    function show($load_as_invicible=false)
    {
        $style = "";
        if ($load_as_invicible)
        {
            $style = 'display: none;';
        }
        
        echo '<div id="main-panel" class="panel" style="' . $style . '">'."\n";
        
        //$onclick_action = 'jQuery(\'#main-panel-content\').BlindToggleVertically(300, null, \'bounceout\');return false;';
        //$onclick_action = 'jQuery(\'#main-panel-content\').animate({ height: \'toggle\' }, \'fast\');';
        $onclick_action = 'jQuery(\'#main-panel-content\').toggle();';
        echo '   <div id="main-panel-header" class="panel-header" onclick="' . $onclick_action . '">'."\n";
        echo '      <span>' . $this->_l10n->get('toggle panel') . '</span>'."\n";
        echo '   </div>'."\n";
        
        echo '<div id="main-panel-content" class="panel-content">'."\n";
        
        $this->_render_accordion();
        
        echo '</div>'."\n";
        
        echo '</div>'."\n\n";


    }
    
    function _render_accordion()
    {
        echo '<div id="main-panel-accordion" class="accordion">'."\n";
                
        if (!empty($this->leafs))
        {
            foreach ($this->leafs as $name => $leaf)
            {
                $content = $leaf->generate_content();
                $this->_render_accordion_leaf($leaf->name,$leaf->title,$content);
            }           
        }
        
        echo '</div>'."\n";
    }
    
    function _render_accordion_leaf($name, $title, &$content)
    {
        echo '<div class="accordion-leaf-header" id="accordion-leaf-' . $name . '">' . $title . '</div>';
        echo '<div class="accordion-leaf-body">';
        echo $content;
        echo '</div>';
    }
    
    function add_leaf($name, &$object)
    {
        if (isset($this->leafs[$name]))
        {
            return false;
        }
        
        $this->leafs[$name] = $object;
        
        return true;
    }
    
}