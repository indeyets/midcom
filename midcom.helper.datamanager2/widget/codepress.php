<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: textarea.php 10966 2007-06-15 07:00:37Z adrenalin $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 CodePress widget
 *
 * As with all subclasses, the actual initialization is done in the initialize() function,
 * not in the constructor, to allow for error handling.
 *
 * This widget supports all types which have a simple, plain text representation accessible
 * though their <i>value</i> member.
 *
 * <b>Available configuration options:</b>
 *
 * - <i>string language:</i> Programming language being edited by CodePress, for example php or javascript
 * - <i>int width:</i> The number of columns of the textarea, this defaults to 50.
 *   Note that this value might be overridden by CSS.
 * - <i>int height:</i> The number of rows of the textearea, this defaults to 6.
 *   Note that this value might be overridden by CSS.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_widget_codepress extends midcom_helper_datamanager2_widget
{
    var $language = 'php';

    /**
     * Width of the textbox.
     *
     * @var int
     * @access public
     */
    var $width = 50;

    /**
     * Height of the textbox.
     *
     * @var int
     * @access public
     */
    var $height = 6;
    
    /**
     * The initialization event handler post-processes the maxlength setting.
     *
     * @return bool Indicating Success
     */
    function _on_initialize()
    {
        if (   ! array_key_exists('value', $this->_type)
            || is_array($this->_type->value)
            || is_object($this->_type->value))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} does not have a value member or it is an array or object, you cannot use the text widget with it.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/codepress/codepress.js');
        
        return true;
    }

    /**
     * Adds a simple single-line text form element at this time.
     */
    function add_elements_to_form()
    {
        $attributes = Array
        (
            'rows' => $this->height,
            'cols' => $this->width,
            'class' => "codepress {$this->language}",
            'id'    => "{$this->_namespace}{$this->name}",
        );

        $this->_form->addElement('textarea', $this->name, $this->_translate($this->_field['title']), $attributes);
        $this->_form->applyFilter($this->name, 'trim');
        
        $this->_form->setAttribute('onsubmit', $this->_form->getAttribute('onsubmit') . "{$this->_namespace}{$this->name}.toggleEditor()");
    }

    function get_default()
    {
        return $this->_type->value;
    }

    function sync_type_with_widget($results)
    {
        $this->_type->value = $results[$this->name];
    }
}

?>