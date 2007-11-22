<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: select.php 12663 2007-10-04 17:42:21Z w_i $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 sorting widget.
 *
 * It can only be bound to a select type (or subclass thereof), and inherits the confguration
 * from there as far as possible.
 *
 * <b>Available configuration options:</b>
 *
 * - <i>int height:</i> The height of the select box, applies only for multiselect enabled
 *   boxes, the value is ignored in all other cases. Defaults to 6.
 * - <i>string othertext:</i> The text that is used to separate the main from the
 *   other form element. They are usually displayed in the same line. The value is passed
 *   through the standard schema localization chain.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_widget_sortable extends midcom_helper_datamanager2_widget_select
{
    /**
     * Sortable elements
     * 
     * @access private
     * @var Array
     */
    var $_elements = array();
    
    /**
     * The initialization event handler post-processes the maxlength setting.
     *
     * @return bool Indicating Success
     */
    function _on_initialize()
    {
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/jQuery/ui/ui.mouse.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/jQuery/ui/ui.draggable.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/jQuery/ui/ui.droppable.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/jQuery/ui/ui.sortable.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/jquery.widget_sortable.js');
        
        return parent::_on_initialize();
    }
    
    /**
     * Adds a (multi)select widget to the form, depending on the base type config.
     */
    function add_elements_to_form()
    {
        if ($this->_field['readonly'])
        {
            $this->_all_elements = Array();
            foreach ($this->_type->selection as $key)
            {
                $this->_all_elements[$key] = $this->_type->get_name_for_key($key);
            }
        }
        else
        {
            $this->_all_elements = $this->_type->list_all();
        }
        // Translate
        foreach ($this->_all_elements as $key => $value)
        {
            $this->_all_elements[$key] = $this->_translate($value);
        }
        $select_attributes = Array
        (
            'class' => ($this->_type->allow_multiple) ? 'list' : 'sortable',
            'id'    => "{$this->_namespace}{$this->name}",
        );
        
        // Create the sorting elements
        $this->_create_select_element();
        
        $this->_form->addGroup($this->_elements, $this->name, $this->_translate($this->_field['title']), ' ', false);
    }
    
    /**
     * Create a sortable list
     * 
     * @access private
     */
    function _create_select_element()
    {
        $this->_elements['s_header'] =& HTML_QuickForm::createElement('static', 's_header', '', "<ul id=\"{$this->name}_sortable\" class=\"sortable\">\n");
        
        $readonly = $this->_field['readonly'];
        
        if ($readonly)
        {
            $lock['text'] = ' readonly="readonly"';
            $lock['input'] = ' disabled="disabled"';
        }
        else
        {
            $lock['text'] = '';
            $lock['input'] = '';
        }
        
        if ($this->_type->allow_multiple)
        {
            $input_type = 'checkbox';
            $name_suffix = '[]';
        }
        else
        {
            $input_type = 'radio';
            $name_suffix = '';
        }
        
        $html = '';
        $i = 1;
        
        foreach ($this->_type->list_all() as $key => $value)
        {
            $html .= "    <li>\n";
            $html .= "        <input type=\"{$input_type}\" name=\"{$this->name}{$name_suffix}\" value=\"{$key}\" />\n";
            $html .= "        <input type=\"text\" name=\"{$this->name}_order[{$key}]\" value=\"{$i}\" />\n";
            $html .= "        {$this->_translate($value)}\n";
            $html .= "    </li>\n";
            $i++;
        }
        
        // Add the element HTML to the form
        $this->_elements['s_body'] =& HTML_QuickForm::createElement('static', 's_body', '', $html);
        
        $this->_elements['s_footer'] =& HTML_QuickForm::createElement('static', 's_footer', '', "</ul>\n");
        
        if (!$readonly)
        {
            $html = "<script type=\"text/javascript\">\n";
            $html .= "    // <![CDATA[\n";
            $html .= "        \$j('#{$this->name}_sortable').create_sortable();\n";
            $html .= "    // ]]>\n";
            $html .= "</script>\n";
            
            // Add the JavaScript HTML to the form
            $this->_elements['s_javascript'] =& HTML_QuickForm::createElement('static', 's_body', '', $html);
        }
    }
    
    /**
     * Synchronize the results with the type
     */
    function sync_type_with_widget($results)
    {
        echo "<pre>\n";
        echo "Make me work\n";
        print_r($_POST);
        echo "</pre>\n";
        die();
    }
}
?>