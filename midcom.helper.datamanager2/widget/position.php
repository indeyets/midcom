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
 * Example:
 'location' => Array
 (
     'title' => 'location',
     'storage' => null,
     'type' => 'position',
     'widget' => 'position',
     'widget_config' => Array 
     ( 
         'service' => 'geonames', //Possible values are city, geonames, yahoo
     ),
 ),
 *
 * @package midcom.helper.datamanager2
 */

class midcom_helper_datamanager2_widget_position extends midcom_helper_datamanager2_widget
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
     * The service backend to use for searches. Defaults to geonames
     */
    var $service = null;

    /**
     * The group of widgets items as QuickForm elements
     */
    var $_widget_elements = array();
    var $_main_group = array();    
    var $_countrylist = array();    
    var $_other_xep_keys = array();
    
    /**
     * Options to pass to the javascript widget.
     * Possible values:
     * - (int) maxRows : Maximum amount of results returned. If this is set greater than 1,
     *   the widget will show alternative results and lets user to choose the best match.
     *   Defaults to: 20
     * - (int) radius : Radius of the area we search for alternatives. (in Kilometers)
     *   Defaults to: 5
     */
     
    var $js_maxRows = null;
    var $js_radius = null;
    
    var $js_options = array();
    var $js_options_str = '';
    
    /**
     * The initialization event handler post-processes the maxlength setting.
     *
     * @return bool Indicating Success
     */
    function _on_initialize()
    {
        if (version_compare(phpversion(), "5.0.0", "<"))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The position widget used on field {$this->name} currently requires PHP5.",
                MIDCOM_LOG_ERROR);
            debug_pop();
        }
        
        if (is_a('midcom_helper_datamanager2_type_position', $this->_type))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} is not a position type or subclass thereoff, you cannot use the position widget with it.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
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
                //dm2_pw_position_map_to_current('{$this->_element_id}');
                jQuery('#{$this->_element_id}').dm2_pw_position_map_to_current();
            }
        }";

        $script = "jQuery('#{$this->_element_id }').tabs({$config});\n";
        $_MIDCOM->add_jquery_state_script($script);
        
        $this->_get_country_list();
        $this->_init_widgets_js_options();
        
        $this->_other_xep_keys = array(
            'area',
            'building',
            'description',
            'floor',
            'region',
            'room',
            'text',
            'uri',
        );
        
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
        $html = "\n<div id=\"{$this->_element_id}_tab_content_place\" class=\"position_widget_tab_content position_widget_tab_content_place\"><!-- tab_content_place starts -->\n";        
        
        $html .= "<div class=\"geodata_btn\" id='{$this->_element_id}_geodata_btn'></div>";
        $html .= "<div class=\"indicator\" id='{$this->_element_id}_indicator' style=\"display: none;\"></div>";
        
        // $html .= "<div class=\"add_xep_keys_menu\" id='{$this->_element_id}_add_xep_keys_menu_holder'>";
        // $html .= "<select class=\"dropdown\" id=\"{$this->_element_id}_add_xep_keys_menu\" name=\"{$this->_element_id}_add_xep_keys_menu\">";
        // $html .= "<option value=\"\">" . $_MIDCOM->i18n->get_string('add xep keys', 'org.routamc.positioning') . "</option>";
        // 
        // foreach ($this->_other_xep_keys as $xep_key)
        // {
        //     $html .= "<option value=\"{$xep_key}\">{$xep_key}</option>";            
        // }        
        // $html .= "</select>";
        // $html .= "</div>";
        
        $html .= $this->_render_country_list($this->_type->location->country);

        $city_name = '';
        $city = new org_routamc_positioning_city_dba($this->_type->location->city);
        if ($city)
        {
            $city_name = $city->city;
        }

        $html .= "<label for='{$this->_element_id}_input_place_city' id='{$this->_element_id}_input_place_city_label'>";
        $html .= "<span class=\"field_text\">" . $_MIDCOM->i18n->get_string('city', 'org.routamc.positioning') . "</span>";        
        $html .= "<input size=\"40\" class=\"shorttext position_widget_input position_widget_input_place_city\" id=\"{$this->_element_id}_input_place_city\" name=\"{$this->_element_id}_input_place_city\" type=\"text\" value=\"{$city_name}\" />";
        $html .= "</label>";
        
        $html .= "<label for='{$this->_element_id}_input_place_region' id='{$this->_element_id}_input_place_region_label'>";
        $html .= "<span class=\"field_text\">" . $_MIDCOM->i18n->get_string('region', 'org.routamc.positioning') . "</span>";        
        $html .= "<input size=\"40\" class=\"shorttext position_widget_input position_widget_input_place_region\" id=\"{$this->_element_id}_input_place_region\" name=\"{$this->_element_id}_input_place_region\" type=\"text\" value=\"{$this->_type->location->region}\" />";
        $html .= "</label>";
        
        $html .= "<label for='{$this->_element_id}_input_place_street' id='{$this->_element_id}_input_place_street_label'>";
        $html .= "<span class=\"field_text\">" . $_MIDCOM->i18n->get_string('street', 'org.routamc.positioning') . "</span>";        
        $html .= "<input size=\"40\" class=\"shorttext position_widget_input position_widget_input_place_street\" id=\"{$this->_element_id}_input_place_street\" name=\"{$this->_element_id}_input_place_street\" type=\"text\" value=\"{$this->_type->location->street}\" />";
        $html .= "</label>";
        
        $html .= "<label for='{$this->_element_id}_input_place_postalcode' id='{$this->_element_id}_input_place_postalcode_label'>";
        $html .= "<span class=\"field_text\">" . $_MIDCOM->i18n->get_string('postalcode', 'org.routamc.positioning') . "</span>";        
        $html .= "<input size=\"40\" class=\"shorttext position_widget_input position_widget_input_place_postalcode\" id=\"{$this->_element_id}_input_place_postalcode\" name=\"{$this->_element_id}_input_place_postalcode\" type=\"text\" value=\"{$this->_type->location->postalcode}\" />";
        $html .= "</label>";
        
        foreach ($this->_other_xep_keys as $xep_key)
        {
            if ($this->_type->location->$xep_key != '')
            {
                $html .= "<label for='{$this->_element_id}_input_place_{$xep_key}' id='{$this->_element_id}_input_place_{$xep_key}_label'>";
                $html .= "<span class=\"field_text\">" . $_MIDCOM->i18n->get_string($xep_key, 'org.routamc.positioning') . "</span>";        
                $html .= "<input size=\"40\" class=\"shorttext position_widget_input position_widget_input_place_{$xep_key}\" id=\"{$this->_element_id}_input_place_{$xep_key}\" name=\"{$this->_element_id}_input_place_{$xep_key}\" type=\"text\" value=\"{$this->_type->location->$xep_key}\" />";
                $html .= "</label>";                
            }
        }
        
        $html .= "<div id=\"{$this->_element_id}_status_box\" class=\"status_box\"></div>";
        
        $html .= "\n</div><!-- tab_content_place ends -->\n";

        $this->_widget_elements[] =& HTML_QuickForm::createElement
        (
            'static',
            "{$this->_element_id}_static_place",
            '',
            $html
        );
    }
    
    function _add_map_method_elements()
    {
        $html = "\n<div id=\"{$this->_element_id}_tab_content_map\" class=\"position_widget_tab_content position_widget_tab_content_map\"><!-- tab_content_map starts -->\n";        
        
        $html .= "\n<div class=\"position_widget_actions\">\n";
        $html .= "\n<div id=\"{$this->_element_id}_position_widget_action_cam\">[ Clear alternatives ]</div> \n";
        $html .= "\n</div>\n";
        
        $orp_map = new org_routamc_positioning_map("{$this->_element_id}_map"/*, 'google'*/);
        $html .= $orp_map->show(420,300,false);

        $html .= "\n</div><!-- tab_content_map ends -->\n";
        
        $this->_widget_elements[] =& HTML_QuickForm::createElement
        (
            'static',
            "{$this->_element_id}_static_map",
            '',
            $html
        );

        $script = "init_position_widget('{$this->_element_id}', mapstraction_{$this->_element_id}_map, {$this->js_options_str});";
        $script = "jQuery('#{$this->_element_id}').dm2_position_widget(mapstraction_{$this->_element_id}_map, {$this->js_options_str});";        
        $_MIDCOM->add_jquery_state_script($script);
    }
    
    function _add_coordinates_method_elements()
    {
        $html = "\n<div id=\"{$this->_element_id}_tab_content_coordinates\" class=\"position_widget_tab_content position_widget_tab_content_coordinates\"><!-- tab_content_coordinates starts -->\n";        

        $html .= "<div class=\"geodata_btn\" id='{$this->_element_id}_revgeodata_btn'></div>";
        $html .= "<div class=\"indicator\" id='{$this->_element_id}_revindicator' style=\"display: none;\"></div>";

        $html .= "<label for='{$this->_element_id}_input_coordinates_latitude' id='{$this->_element_id}_input_coordinates_latitude_label'>";
        $html .= "<span class=\"field_text\">" . $_MIDCOM->i18n->get_string('latitude', 'org.routamc.positioning') . "</span>";        
        $html .= "<input size=\"20\" class=\"shorttext position_widget_input position_widget_input_coordinates_latitude\" id=\"{$this->_element_id}_input_coordinates_latitude\" name=\"{$this->_element_id}_input_coordinates_latitude\" type=\"text\" value=\"{$this->_type->location->latitude}\" />";
        $html .= "</label>";
        
        $html .= "<label for='{$this->_element_id}_input_coordinates_longitude' id='{$this->_element_id}_input_coordinates_longitude_label'>";
        $html .= "<span class=\"field_text\">" . $_MIDCOM->i18n->get_string('longitude', 'org.routamc.positioning') . "</span>";        
        $html .= "<input size=\"20\" class=\"shorttext position_widget_input position_widget_input_coordinates_longitude\" id=\"{$this->_element_id}_input_coordinates_longitude\" name=\"{$this->_element_id}_input_coordinates_longitude\" type=\"text\" value=\"{$this->_type->location->longitude}\" />";
        $html .= "</label>";
        
        $html .= "\n</div><!-- tab_content_coordinates ends -->\n";
        
        $this->_widget_elements[] =& HTML_QuickForm::createElement
        (
            'static',
            "{$this->_element_id}_static_coordinates",
            '',
            $html
        );
    }
    
    function _get_country_list()
    {
        $this->_countrylist = array
        (
            '' => $this->_l10n_midcom->get('select your country'),
        );
        
        $qb = org_routamc_positioning_country_dba::new_query_builder();
        $qb->add_constraint('code', '<>', '');
        $qb->add_order('name', 'ASC');
        $countries = $qb->execute_unchecked();

        if (count($countries) == 0)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Cannot render country list: No countries found. You have to use org.routamc.positioning to import countries to database.');
            debug_pop();
        }
        
        foreach ($countries as $country)
        {
            $this->_countrylist[$country->code] = $country->name;
        }
    }
    
    function _render_country_list($current='')
    {
        $html = '';
        
        if (   empty($this->_countrylist)
            || count($this->_countrylist) == 1)
        {
            $html .= "<label for='{$this->_element_id}_input_place_country' id='{$this->_element_id}_input_place_country_label'>";
            $html .= "<span class=\"field_text\">" . $_MIDCOM->i18n->get_string('country', 'org.routamc.positioning') . "</span>";
            $html .= "<input size=\"30\" class=\"shorttext position_widget_input position_widget_input_place_country\" id=\"{$this->_element_id}_input_place_country\" name=\"{$this->_element_id}_input_place_country\" type=\"text\" value=\"{$current}\" />";
            $html .= "</label>";
                    
            return $html;
        }
        
        $html .= "<label for='{$this->_element_id}_input_place_country' id='{$this->_element_id}_input_place_country_label'>";
        $html .= "<span class=\"field_text\">" . $_MIDCOM->i18n->get_string('country', 'org.routamc.positioning') . "</span>";
        $html .= "<select class=\"dropdown position_widget_input position_widget_input_place_country\" id=\"{$this->_element_id}_input_place_country\" name=\"{$this->_element_id}_input_place_country\">";

        foreach ($this->_countrylist as $code => $name)
        {
            $selected = '';
            if ($code == $current)
            {
                $selected = 'selected="selected"';
            }
            $html .= "<option value=\"{$code}\" {$selected}>{$name}</option>";
        }
        
        $html .= "</select>";
        $html .= "</label>";
        
        return $html;
    }

    function _init_widgets_js_options()
    {
        $this->js_options['maxRows'] = 20;
        $this->js_options['radius'] = 5;
        
        if (   !is_null($this->js_maxRows)
            && $this->js_maxRows > 0)
        {
            $this->js_options['maxRows'] = $this->js_maxRows;
        }
        if (   !is_null($this->js_radius)
            && $this->js_radius > 0)
        {
            $this->js_options['radius'] = $this->js_radius;
        }
        
        $this->js_options_str = "{ ";
        if (! empty($this->js_options))
        {
            $opt_cnt = count($this->js_options);
            $i = 0;
            foreach ($this->js_options as $key => $value)
            {
                $i++;
                $this->js_options_str .= "{$key}: {$value}";
                if ($i < $opt_cnt)
                {
                    $this->js_options_str .= ", ";
                }
            }
        }
        $this->js_options_str .= " }";
    }
    
    function get_default()
    {        
        $city_name = '';
        $city = new org_routamc_positioning_city_dba($this->_type->location->city);
        if ($city)
        {
            $city_name = $city->city;
        }
        
        //$script = "dm2_pw_init_current_pos('{$this->_element_id}',{$this->_type->location->latitude},{$this->_type->location->longitude});\n";
        $script = "jQuery('#{$this->_element_id}').dm2_pw_init_current_pos({$this->_type->location->latitude},{$this->_type->location->longitude});";
        $_MIDCOM->add_jquery_state_script($script);
        
        return Array
        (
            "{$this->_element_id}_input_place_country" => $this->_type->location->country,
            "{$this->_element_id}_input_place_city" => $city_name,
            "{$this->_element_id}_input_place_street" => $this->_type->location->street,
            "{$this->_element_id}_input_place_postalcode" => $this->_type->location->postalcode,
            "{$this->_element_id}_input_coordinates_latitude" => $this->_type->location->latitude,
            "{$this->_element_id}_input_coordinates_longitude" => $this->_type->location->longitude,
        );
    }

    function sync_type_with_widget($results)
    {
        if (isset($results["{$this->_element_id}_input_place_country"]))
        {
            $this->_type->location->country = $results["{$this->_element_id}_input_place_country"];
        }
        if (isset($results["{$this->_element_id}_input_place_city"]))
        {
            $city_id = 0;
            $city = org_routamc_positioning_city_dba::get_by_name($results["{$this->_element_id}_input_place_city"]);
            if ($city)
            {
                $city_id = $city->id;
            }
            $this->_type->location->city = $city_id;
        }
        if (isset($results["{$this->_element_id}_input_place_street"]))
        {
            $this->_type->location->street = $results["{$this->_element_id}_input_place_street"];
        }
        if (isset($results["{$this->_element_id}_input_place_region"]))
        {
            $this->_type->location->region = $results["{$this->_element_id}_input_place_region"];
        }
        if (isset($results["{$this->_element_id}_input_place_postalcode"]))
        {
            $this->_type->location->postalcode = $results["{$this->_element_id}_input_place_postalcode"];
        }
        
        if (   isset($results["{$this->_element_id}_input_coordinates_latitude"])
            && $results["{$this->_element_id}_input_coordinates_latitude"] != '')
        {
            $this->_type->location->latitude = $results["{$this->_element_id}_input_coordinates_latitude"];
        }
        if (   isset($results["{$this->_element_id}_input_coordinates_longitude"])
            && $results["{$this->_element_id}_input_coordinates_longitude"] != '')
        {
            $this->_type->location->longitude = $results["{$this->_element_id}_input_coordinates_longitude"];
        }
    }    
    
    function is_frozen()
    {
        return $this->_main_group->isFrozen();
    }

}

?>