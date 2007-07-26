<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: textarea.php 10966 2007-06-15 07:00:37Z adrenalin $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 Tags widget
 *
 * As with all subclasses, the actual initialization is done in the initialize() function,
 * not in the constructor, to allow for error handling.
 *
 * It can only be bound to a tagselect type (or subclass thereof), and inherits the configuration
 * from there as far as possible.
 *
 * <b>Available configuration options:</b>
 *
 * - <i>integer min_chars:</i> Minimum amount of chars to be inserted before search starts. Default: 1
 * - <i>integer result_limit:</i> Number max Limit the number of items in the select box.
 * Is also sent as a "limit" parameter with a remote request. Default: 10
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_widget_tags extends midcom_helper_datamanager2_widget
{
    /**
     * id of the input element
     *
     * @var String
     * @access private
     */    
    var $_input_element_id = "tags-widget";
    
    /**
     * Array of options that are passed to javascript widget
     *
     * @var Array
     * @access private
     */
    var $_js_widget_options = array();

    var $_input_element = null;
    
    /**
     * The initialization event handler post-processes the maxlength setting.
     *
     * @return bool Indicating Success
     */
    function _on_initialize()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        // if (is_a('midcom_helper_datamanager2_type_tagselect', $this->_type))
        // {
        //     debug_add("Warning, the field {$this->name} is not a tagselect type or subclass thereof, you cannot use the tags widget with it.",
        //         MIDCOM_LOG_WARN);
        //     debug_pop();
        //     return false;
        // }
        
        $_MIDCOM->enable_jquery();

        $_MIDCOM->add_link_head(
            array(
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/tags/jquery.tags_widget.css'
            )
        );
        $_MIDCOM->add_link_head(
            array(
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/tags/widget.css'
            )
        );
        
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/tags/jquery.bgiframe.min.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/jQuery/jquery.dimensions.js');        
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/tags/jquery.tags_widget.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/tags/widget.js');
                
        $this->_input_element_id = "{$this->_namespace}{$this->name}-tags-widget";
        
        $this->_js_widget_options['widget_type_name'] = "'{$this->name}'";
        $this->_js_widget_options['min_chars'] = 1;
        $this->_js_widget_options['result_limit'] = 10;
        $this->_js_widget_options['autofill_enabled'] = "false";
        $this->_js_widget_options['select_first'] = "true";
        $this->_js_widget_options['extra_params'] = "{}";
        $this->_js_widget_options['delay'] = 400;
        $this->_js_widget_options['width'] = 0;
                
        if (isset($this->min_chars))
        {
            $this->_js_widget_options['min_chars'] = $this->min_chars;
        }
        if (isset($this->max_results))
        {
            $this->_js_widget_options['max_results'] = $this->max_results;
        }
        if (isset($this->match_inner))
        {
            if ($this->match_inner)
            {
                $this->_js_widget_options['match_inner'] = "true";
            }
            else
            {
                $this->_js_widget_options['match_inner'] = "false";
            }
        } 
        debug_pop();
        return true;
    }
    
    function _get_key_data($key)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $value = "{";
        
        $name = "Test";
        $color = "4c4c4c";
        
        $value .= "id: '{$key}',";
        $value .= "name: '{$name}',";
        $value .= "color: '{$color}',";
                
        $value .= "}";
        debug_pop();

        return $value;
    }

    /**
     * Adds a simple single-line text form element and place holder for tags.
     */
    function add_elements_to_form()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        // $selections_html = "<div class='tags-widget-selections'></div>";
        // $results_html = "<ul class='tags-widget-results'></ul>";

        $attributes = Array
        (
            'class' => "shorttext",
            'id'    => $this->_input_element_id,
        );

        $this->_form->addElement('text', "{$this->name}", $this->_translate($this->_field['title']), $attributes);
        // $this->_form->addElement('static', $this->name.'_selections', '', $selections_html);
        // $this->_form->addElement('static', $this->name.'_results', '', $results_html);
        
        //$this->_form->applyFilter($this->name, 'trim');

        // Get url to search handler
        $nav = new midcom_helper_nav();
        $root_node = $nav->get_node($nav->get_root_node());
        $this->_handler_url = $root_node[MIDCOM_NAV_FULLURL] . 'midcom-exec-midcom.helper.datamanager2/tags_handler.php';

        $script = "jQuery('#{$this->_input_element_id}').midcom_helper_datamanager2_widget_tags_widget('{$this->_handler_url}', {\n";
        foreach ($this->_js_widget_options as $key => $value)
        {
            $script .= "{$key}: {$value},\n";
        }
        $script .= "});";
        // jQuery('#{$this->_input_element_id}').midcom_helper_datamanager2_widget_tags_result(function(event, data, formatted) {
        //     tags_widget_add_item('{$this->_input_element_id}', data);
        // });        
        $_MIDCOM->add_jquery_state_script($script);

        // Add existing selection
        $existing_elements = $this->_type->selection;
        $ee_script = '';
        foreach ($existing_elements as $key)
        {
            debug_add("Processing key {$key}");
            $data = $this->_get_key_data($key);
            debug_add("Got data: {$data}");
            $ee_script .= "jQuery('#{$this->_input_element_id}').midcom_helper_datamanager2_widget_tags_add_selection_item({$data});\n";
        }
         $_MIDCOM->add_jquery_state_script($ee_script);
         
         debug_pop();
    }
    
    /**
      * The defaults of the widget are mapped to the current selection.
      */
     function get_default()
     {
         $defaults = Array();
         foreach ($this->_type->selection as $key)
         {
             $defaults[$key] = true;
         }
         return Array($this->name => $defaults);
     }
    
    /**
     * Reads the given get/post data and puts to type->selection
     */
    function sync_type_with_widget($results)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        debug_print_r('results:',$results);
        
        $this->_type->selection = Array();
        if (!isset($results["{$this->name}_tag"]))
        {
            return;
        }
        $real_results =& $results["{$this->name}_tag"];
        
        foreach ($real_results as $key => $value)
        {
            $this->_type->selection[] = $key;
        }
        
        debug_print_r('real_results', $real_results);
        
        debug_pop();        
    }

    function freeze()
    {
    }

    /**
     * Unfreezes all form elements associated with the widget. The default implementation
     * works on the default field name, you don't need to override this function unless
     * you have multiple widgets in the form.
     *
     * This maps to the HTML_QuickForm_element::unfreeze()unction.
     */
    function unfreeze()
    {
    }
    
    function render_content()
    {
        echo '<ul>';
        if (count($this->_type->selection) == 0)
        {
            echo '<li>' . $this->_translate('type select: no selection') . '</li>';
        }
        else
        {
            foreach ($this->_type->selection as $key)
            {
                echo '<li>' . $this->_get_key_value($key) . '</li>';
            }
        }
        echo '</ul>';
    }    
}

?>