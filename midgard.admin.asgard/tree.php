<?php
/**
 * @package midgard.admin.asgard
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: acl_editor.php 5538 2007-03-20 13:22:41Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Copy/delete tree branch viewer
 * @package midgard.admin.asgard
 */
class midgard_admin_asgard_copytree extends midgard_admin_asgard_navigation
{
    /**
     * Switch to determine if the whole tree should be copied
     * 
     * @access public
     * @var boolean
     */
    var $copy_tree = false;
    
    /**
     * Switch to determine the visibility of inputs
     * 
     * @access public
     * @var boolean
     */
    var $inputs = true;
    
    /**
     * Choose the target type
     * 
     * @access public
     * @var String
     */
    var $input_type;
    
    /**
     * Choose the target name for the form
     * 
     * @access public
     * @var String
     */
    var $input_name;
    
    /**
     * Show the link to view the object
     * 
     * @access public
     * @var boolean
     */
    var $view_link = false;
    
    /**
     * Show the link to view the object
     * 
     * @access public
     * @var boolean
     */
    var $edit_link = false;
    
    /**
     * Page prefix
     * 
     * @var String
     * @access public
     */
    var $page_prefix = '';
    
    /**
     * Mark untranslated
     * 
     * @var boolean
     * @access public
     */
    var $mark_untranslated = true;
    
    /**
     * Language ID of the root object
     * 
     * @var int
     * @access private
     */
    var $_root_object_language = 0;
    
    /**
     * Constructor, connect to the parent class constructor.
     * 
     * @static
     * @access public
     * @param mixed $object
     * @param Array $request_data
     */
    function __construct($object, &$request_data)
    {
        parent::__construct($object, $request_data);
        $this->page_prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        
        // Get the root object language
        $this->_root_object_language = $_MIDCOM->i18n->get_midgard_language();
    }
    
    /**
     * List the child elements and print out navigation-like tree for selecting the parts that should be copied
     * 
     * @access public
     * @param mixed $object      MgdSchema object
     * @param string $prefix     Indent for the output
     * @param int $level         Level of depth
     */
    function list_children($object, $prefix, $level)
    {
        $siblings = midcom_helper_reflector_tree::get_child_objects($object);
        if (count($siblings) === 0)
        {
            return false;
        }
        
        echo "{$prefix}<ul class=\"midgard_admin_asgard_object_copytree level_{$level}\">\n";
        
        foreach ($siblings as $type => $children)
        {
            foreach ($children as $child)
            {
                // Skip the objects already shown
                if (isset($shown_objects[$child->guid]))
                {
                    continue;
                }
            }
        }
        
        echo "{$prefix}</ul>\n";
    }
    
    /**
     * List the child elements
     * 
     * @access private
     * @param mixed $object     MgdSchema object
     * @param string $prefix     Indent for the output
     * @param int $level         Level of depth
     */
    function _list_child_elements($object, $prefix = '    ', $level = 0)
    {
        if ($level > 25)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Recursion level 25 exceeded, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }
        $siblings = midcom_helper_reflector_tree::get_child_objects($object);
        if (   is_array($siblings)
            && count($siblings) > 0)
        {
            echo "{$prefix}<ul>\n";
            foreach ($siblings as $type => $children)
            {
                foreach ($children as $child)
                {
                    if (isset($this->shown_objects[$child->guid]))
                    {
                        continue;
                    }

                    $ref =& $this->_get_reflector($child);

                    $span_class = '';
                    $selected = $this->_is_selected($child);
                    $css_class = $type;
                    $this->_common_css_classes($child, $ref, $css_class);
                    $this->shown_objects[$child->guid] = true;
                    
                    // Add the untranslated
                    if (   $this->mark_untranslated
                        && $this->_root_object_language)
                    {
                        if (   !isset($child->lang)
                            || $child->lang !== $this->_root_object_language)
                        {
                            $span_class = ' untranslated';
                        }
                    }

                    echo "{$prefix}    <li class=\"{$css_class}\">\n";

                    $label = htmlspecialchars($ref->get_object_label($child));
                    $icon = $ref->get_object_icon($child);
                    if (empty($label))
                    {
                        $label = "#{$child->id}";
                    }
                    
                    if ($this->copy_tree)
                    {
                        $checked = ' checked="checked"';
                    }
                    else
                    {
                        $checked = '';
                    }
                    
                    if ($this->inputs)
                    {
                        // This value is used for compiling the exlusion list: if the object is found from this list, but not from the selection list,
                        // it means that the selection did not include the object GUID
                        echo "{$prefix}        <input type=\"hidden\" name=\"all_objects[]\" value=\"{$child->guid}\" />\n";
                        
                        echo "{$prefix}        <label for=\"item_{$child->guid}\">\n";
                        echo "{$prefix}        <input id=\"item_{$child->guid}\" type=\"{$this->input_type}\" name=\"{$this->input_name}\" value=\"{$child->guid}\"{$checked} />\n";
                    }
                    
                    echo "{$prefix}            <span class=\"title{$span_class}\">{$icon}{$label}</span>\n";
                    
                    // Show the link to the object
                    if ($this->view_link)
                    {
                        echo "{$prefix}            <a href=\"{$this->page_prefix}__mfa/asgard/object/view/{$child->guid}/\" class=\"thickbox\" target=\"_blank\" title=\"" . sprintf($_MIDCOM->i18n->get_string('%s (%s)', 'midgard.admin.asgard'), $label, $ref->get_class_label()) . "\">\n";
                        echo "{$prefix}                <img src=\"" . MIDCOM_STATIC_URL . "/stock-icons/16x16/view.png\" alt=\"" . $_MIDCOM->i18n->get_string('view object', 'midgard.admin.asgard') . "\" />\n";
                        echo "{$prefix}            </a>\n";
                    }
                    
                    // Show the link to the object
                    if ($this->edit_link)
                    {
                        echo "{$prefix}            <a href=\"{$this->page_prefix}__mfa/asgard/object/edit/{$child->guid}/\" target=\"_blank\" title=\"" . sprintf($_MIDCOM->i18n->get_string('%s (%s)', 'midgard.admin.asgard'), $label, $ref->get_class_label()) . "\">\n";
                        echo "{$prefix}                <img src=\"" . MIDCOM_STATIC_URL . "/stock-icons/16x16/edit.png\" alt=\"" . $_MIDCOM->i18n->get_string('edit object', 'midgard.admin.asgard') . "\" />\n";
                        echo "{$prefix}            </a>\n";
                    }
                    
                    if ($this->inputs)
                    {
                        echo "{$prefix}        </label>\n";
                    }
                    
                    // List the child elements
                    $this->_list_child_elements($child, "{$prefix}        ", $level + 1);

                    echo "{$prefix}    </li>\n";
                }
            }
            echo "{$prefix}</ul>\n";
        }
    }
    
    /**
     * Draw the tree selector
     * 
     * @access public
     */
    function draw()
    {
        if (!$this->input_type)
        {
            $this->input_type = 'checkbox';
        }
        
        if (!$this->input_name)
        {
            if ($this->input_type === 'checkbox')
            {
                $this->input_name = 'selected[]';
            }
            else
            {
                $this->input_name = 'target';
            }
        }
        
        $this->_root_object =& $this->_object;
        $this->_list_child_elements($this->_root_object);
    }
}
?>