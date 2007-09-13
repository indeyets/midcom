<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: textarea.php 10966 2007-06-15 07:00:37Z adrenalin $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once MIDCOM_ROOT . '/midcom/helper/datamanager2/widget/simpleposition.php';

/**
 * Datamanager 2 Positioning widget
 *
 * As with all subclasses, the actual initialization is done in the initialize() funciton,
 * not in the constructor, to allow for error handling.
 *
 * It can only be bound to a position type (or subclass thereoff), and inherits the configuration
 * from there as far as possible.
 *
 * @package midcom.helper.datamanager2
 */

class midcom_helper_datamanager2_widget_position extends midcom_helper_datamanager2_widget_simpleposition
{
    /**
     * id of the element
     *
     * @var String
     * @access private
     */    
    var $_element_id = "positioning_widget";
    
    /**
     * List of enabled positioning methods
     * Available methods: place, map, coordinates
     * Defaults to all.
     *
     * @var array
     * @access public
     */
    var $enabled_methods = null;

    /**
     * The group of widgets items as QuickForm elements
     */
    var $_widget_elements = array();

    var $_main_group = array();
    
    var $service = null;
    
    /**
     * The initialization event handler post-processes the maxlength setting.
     *
     * @return bool Indicating Success
     */
    function _on_initialize()
    {
        if (is_a('midcom_helper_datamanager2_type_position', $this->_type))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} is not a position type or subclass thereoff, you cannot use the position widget with it.",
                MIDCOM_LOG_WARN);
            debug_pop();
            //return false;
        }
        
        if (is_null($this->enabled_methods))
        {
            $this->enabled_methods = array
            (
                'place',
                'map',
                'coordinates'
            );
        }
        
        if ($this->_type->location != '')
        {
            $this->_set_initial_location();
        }
        
        if (is_null($this->service))
        {
            $this->service = 'geonames';
        }
        
        $_MIDCOM->enable_jquery();

        $_MIDCOM->add_link_head(
            array(
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/position/position_widget.css'
            )
        );
        $_MIDCOM->add_link_head(
            array(
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/position/jquery.tabs.css'
            )
        );
        $_MIDCOM->add_link_head(
            array(
                'condition' => 'lte IE 7',
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/position/jquery.tabs-ie.css',
                'media' => 'projection, screen',
            )
        );
        
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/position/jquery.tabs.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/position/widget.js');
        
        $this->_element_id = "{$this->_namespace}{$this->name}_chooser_widget";
        
        $config = "{
            fxAutoHeight: true,
            fxSpeed: 'fast',
            onShow: function() {
                position_map_to_current('{$this->_element_id}');
            }
        }";

        $script = "jQuery('#{$this->_element_id }').tabs({$config});\n";
        $_MIDCOM->add_jquery_state_script($script);
        
        return true;
    }
    
    /**
     * Creates the tab view for all enabled positioning methods
     * Also adds static options to results.
     */
    function add_elements_to_form()
    {
        // Get url to geocode handler
        $nav = new midcom_helper_nav();
        $root_node = $nav->get_node($nav->get_root_node());
        $this->_handler_url = $root_node[MIDCOM_NAV_FULLURL] . 'midcom-exec-org.routamc.positioning/geocode.php';

        $html = "<div id=\"{$this->_element_id}\" class=\"midcom_helper_datamanager2_widget_position\"><!-- widget starts -->\n";

        $html .= "<input class=\"position_widget_id\" id=\"{$this->_element_id}_id\" name=\"{$this->_element_id}_id\" type=\"hidden\" value=\"{$this->_element_id}\" />";
        $html .= "<input class=\"position_widget_backend_url\" id=\"{$this->_element_id}_backend_url\" name=\"{$this->_element_id}_backend_url\" type=\"hidden\" value=\"{$this->_handler_url}\" />";        
        $html .= "<input class=\"position_widget_backend_service\" id=\"{$this->_element_id}_backend_service\" name=\"{$this->_element_id}_backend_service\" type=\"hidden\" value=\"{$this->service}\" />";
                
        $html .= "    <ul>\n";
        
        foreach ($this->enabled_methods as $method)
        {
            $html .= "        <li><a href=\"#{$this->_element_id}_tab_content_{$method}\"><span>" . $_MIDCOM->i18n->get_string($method, 'org.routamc.positioning') . "</span></a></li>\n";
        }

        $html .= "    </ul>\n";
        $this->_widget_elements[] =& HTML_QuickForm::createElement
        (
            'static',
            "{$this->_element_id}_static_widget_start",
            '',
            $html
        );
        
        foreach ($this->enabled_methods as $method)
        {
            $function = "_add_{$method}_method_elements";
            $this->$function();            
        }
        
        $html = "</div><!-- widget ends -->\n";
        $this->_widget_elements[] =& HTML_QuickForm::createElement
        (
            'static',
            "{$this->_element_id}_static_widget_end",
            '',
            $html
        );
        
        $this->_main_group =& $this->_form->addGroup
        (
            $this->_widget_elements,
            $this->name,
            $this->_translate($this->_field['title']),
            ''
        );
    }
    
    function _add_place_method_elements()
    {
        $html = "\n<div id=\"{$this->_element_id}_tab_content_place\" class=\"position_widget_tab_content_place\"><!-- tab_content_place starts -->\n";        
        
        $html .= "<div class=\"geoclue_button\" id='{$this->_element_id}_geoclue_button'></div>";
        $html .= "<div class=\"indicator\" id='{$this->_element_id}_indicator' style=\"display: none;\"></div>";
        
        // $html .= "<label for='{$this->_element_id}_input_place_name' id='{$this->_element_id}_input_place_name_label'>";
        // $html .= "<span class=\"field_text\">" . $_MIDCOM->i18n->get_string('name', 'org.routamc.positioning') . "</span>";        
        // $html .= "<input size=\"40\" class=\"shorttext position_widget_input position_widget_input_place_name\" id=\"{$this->_element_id}_input_place_name\" name=\"{$this->_element_id}_input_place_name\" type=\"text\" value=\"\" />";
        // $html .= "</label>";
        
        $html .= "<label for='{$this->_element_id}_input_place_country' id='{$this->_element_id}_input_place_country_label'>";
        $html .= "<span class=\"field_text\">" . $_MIDCOM->i18n->get_string('country', 'org.routamc.positioning') . "</span>";        
        $html .= "<input size=\"40\" class=\"shorttext position_widget_input position_widget_input_place_country\" id=\"{$this->_element_id}_input_place_country\" name=\"{$this->_element_id}_input_place_country\" type=\"text\" value=\"\" />";
        $html .= "</label>";

        $html .= "<label for='{$this->_element_id}_input_place_city' id='{$this->_element_id}_input_place_city_label'>";
        $html .= "<span class=\"field_text\">" . $_MIDCOM->i18n->get_string('city', 'org.routamc.positioning') . "</span>";        
        $html .= "<input size=\"40\" class=\"shorttext position_widget_input position_widget_input_place_city\" id=\"{$this->_element_id}_input_place_city\" name=\"{$this->_element_id}_input_place_city\" type=\"text\" value=\"\" />";
        $html .= "</label>";
        
        $html .= "<label for='{$this->_element_id}_input_place_street' id='{$this->_element_id}_input_place_street_label'>";
        $html .= "<span class=\"field_text\">" . $_MIDCOM->i18n->get_string('street', 'org.routamc.positioning') . "</span>";        
        $html .= "<input size=\"40\" class=\"shorttext position_widget_input position_widget_input_place_street\" id=\"{$this->_element_id}_input_place_street\" name=\"{$this->_element_id}_input_place_street\" type=\"text\" value=\"\" />";
        $html .= "</label>";
        
        $html .= "<label for='{$this->_element_id}_input_place_postalcode' id='{$this->_element_id}_input_place_postalcode_label'>";
        $html .= "<span class=\"field_text\">" . $_MIDCOM->i18n->get_string('postalcode', 'org.routamc.positioning') . "</span>";        
        $html .= "<input size=\"40\" class=\"shorttext position_widget_input position_widget_input_place_postalcode\" id=\"{$this->_element_id}_input_place_postalcode\" name=\"{$this->_element_id}_input_place_postalcode\" type=\"text\" value=\"\" />";
        $html .= "</label>";

        $html .= "<div id=\"{$this->_element_id}_status_box\" class=\"status_box\"></div>";
        
        $html .= "\n</div><!-- tab_content_place ends -->\n";

        $this->_widget_elements[] =& HTML_QuickForm::createElement
        (
            'static',
            "{$this->_element_id}_static_place",
            '',
            $html
        );
        
        // $this->_widget_elements[] =& HTML_QuickForm::createElement
        // (
        //     'text',
        //     "{$this->_element_id}_input_place_name",
        //     $_MIDCOM->i18n->get_string('name', 'org.routamc.positioning'),
        //     array
        //     (
        //         'class'         => 'shorttext position_widget_input_place_name',
        //         'id'            => "{$this->_element_id}_input_place_name",
        //     )
        // );
        // 
        // $this->_widget_elements[] =& HTML_QuickForm::createElement
        // (
        //     'text',
        //     "{$this->_element_id}_input_place_country",
        //     $_MIDCOM->i18n->get_string('country', 'org.routamc.positioning'),
        //     array
        //     (
        //         'class'         => 'shorttext position_widget_input_place_country',
        //         'id'            => "{$this->_element_id}_input_place_country",
        //     )
        // );
        // 
        // $this->_widget_elements[] =& HTML_QuickForm::createElement
        // (
        //     'text',
        //     "{$this->_element_id}_input_place_city",
        //     $_MIDCOM->i18n->get_string('city', 'org.routamc.positioning'),
        //     array
        //     (
        //         'class'         => 'shorttext position_widget_input_place_city',
        //         'id'            => "{$this->_element_id}_input_place_city",
        //     )
        // );
        // 
        // $this->_widget_elements[] =& HTML_QuickForm::createElement
        // (
        //     'text',
        //     "{$this->_element_id}_input_place_street",
        //     $_MIDCOM->i18n->get_string('street', 'org.routamc.positioning'),
        //     array
        //     (
        //         'class'         => 'shorttext position_widget_input_place_street',
        //         'id'            => "{$this->_element_id}_input_place_street",
        //     )
        // );

        // $html = "\n</div><!-- tab_content_place ends -->\n";
        // $this->_widget_elements[] =& HTML_QuickForm::createElement
        // (
        //     'static',
        //     "{$this->_element_id}_static_place_end",
        //     '',
        //     $html
        // );
    }
    
    function _add_map_method_elements()
    {
        $html = "\n<div id=\"{$this->_element_id}_tab_content_map\" class=\"position_widget_tab_content_map\"><!-- tab_content_map starts -->\n";        

        $orp_map = new org_routamc_positioning_map("{$this->_element_id}_map");
        //$orp_map->add_object($this->_type->storage);
        $html .= $orp_map->show(420,300,false);

        $html .= "\n</div><!-- tab_content_map ends -->\n";
        
        $this->_widget_elements[] =& HTML_QuickForm::createElement
        (
            'static',
            "{$this->_element_id}_static_map",
            '',
            $html
        );

        $script = "init_position_widget('{$this->_element_id}', mapstraction_{$this->_element_id}_map);";
        $_MIDCOM->add_jquery_state_script($script);
    
        // $html = "\n</div><!-- tab_content_map ends -->\n";
        // $this->_widget_elements[] =& HTML_QuickForm::createElement
        // (
        //     'static',
        //     "{$this->_element_id}_static_map_end",
        //     '',
        //     $html
        // );
    }
    
    function _add_coordinates_method_elements()
    {
        $html = "\n<div id=\"{$this->_element_id}_tab_content_coordinates\" class=\"position_widget_tab_content_coordinates\"><!-- tab_content_coordinates starts -->\n";        

        $html .= "<label for='{$this->_element_id}_input_coordinates_latitude' id='{$this->_element_id}_input_coordinates_latitude_label'>";
        $html .= "<span class=\"field_text\">" . $_MIDCOM->i18n->get_string('latitude', 'org.routamc.positioning') . "</span>";        
        $html .= "<input size=\"40\" class=\"shorttext position_widget_input position_widget_input_coordinates_latitude\" id=\"{$this->_element_id}_input_coordinates_latitude\" name=\"{$this->_element_id}_input_coordinates_latitude\" type=\"text\" value=\"\" />";
        $html .= "</label>";
        
        $html .= "<label for='{$this->_element_id}_input_coordinates_longitude' id='{$this->_element_id}_input_coordinates_longitude_label'>";
        $html .= "<span class=\"field_text\">" . $_MIDCOM->i18n->get_string('longitude', 'org.routamc.positioning') . "</span>";        
        $html .= "<input size=\"40\" class=\"shorttext position_widget_input position_widget_input_coordinates_longitude\" id=\"{$this->_element_id}_input_coordinates_longitude\" name=\"{$this->_element_id}_input_coordinates_longitude\" type=\"text\" value=\"\" />";
        $html .= "</label>";
        
        $html .= "\n</div><!-- tab_content_coordinates ends -->\n";
        
        $this->_widget_elements[] =& HTML_QuickForm::createElement
        (
            'static',
            "{$this->_element_id}_static_coordinates",
            '',
            $html
        );

        // $this->_widget_elements[] =& HTML_QuickForm::createElement
        // (
        //     'text',
        //     "{$this->_element_id}_input_coordinates_latitude",
        //     $_MIDCOM->i18n->get_string('latitude', 'org.routamc.positioning'),
        //     array
        //     (
        //         'class'         => 'shorttext position_widget_input_coordinates_latitude',
        //         'id'            => "{$this->_element_id}_input_coordinates_latitude",
        //     )
        // );
        //     
        // $this->_widget_elements[] =& HTML_QuickForm::createElement
        // (
        //     'text',
        //     "{$this->_element_id}_input_coordinates_longitude",
        //     $_MIDCOM->i18n->get_string('longitude', 'org.routamc.positioning'),
        //     array
        //     (
        //         'class'         => 'shorttext position_widget_input_coordinates_longitude',
        //         'id'            => "{$this->_element_id}_input_coordinates_longitude",
        //     )
        // );
        // 
        // $html = "\n</div><!-- tab_content_coordinates ends -->\n";
        // $this->_widget_elements[] =& HTML_QuickForm::createElement
        // (
        //     'static',
        //     "{$this->_element_id}_static_coordinates_end",
        //     '',
        //     $html
        // );
    }
    
    function _set_initial_location()
    {
        
    }
    
    function is_frozen()
    {
        return $this->_main_group->isFrozen();
    }

}

?>