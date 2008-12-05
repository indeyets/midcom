<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager simple text widget
 *
 * As with all subclasses, the actual initialization is done in the initialize() function,
 * not in the constructor, to allow for error handling.
 *
 * This widget supports all types which have a simple, plain text representation accessible
 * though their <i>value</i> member.
 *
 * <b>Available configuration options:</b>
 *
 * - <i>int maxlength:</i> The maximum length of the string allowed for this field.
 *   This includes any newlines, which account as at most two characters, depending
 *   on the OS. If you specify a 0, no maximum length is set. If you specify a -1,
 *   maximum length is inherited from the type, if applicable or unlimited otherwise.
 *   If a maximum length is set, an appropriate validation rule is created implicitly.
 *   A -1 setting is processed during startup and has no effect at a later time.
 * - <i>int size:</i> The size of the input box. Defaulting to 40. Note that this value
 *   might be overridden by CSS.
 * - <i>boolean hideinput:</i> Set this to true if you want to hide the input in the widget,
 *   this usually means that a password HTML element will be used, instead of a regular
 *   text input widget. Defaults to false.
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_widget_text extends midcom_helper_datamanager_widget_baseclass
{
    /**
     * Maximum length of the string encapsulated by this type. 0 means no limit.
     * -1 tries to bind to the types maxlength member, if available.
     *
     * @var int
     */
    public $maxlength = -1;

    /**
     * The size of the input box.
     *
     * @var int
     */
    public $size = 40;

    /**
     * whether the input should be shown in the widget, or not.
     *
     * @var boolean
     */
    public $hideinput = false;
    
    /**
     * The initialization event handler post-processes the maxlength setting.
     *
     * @return boolean Indicating Success
     */
    protected function on_initialize()
    {
        if (   ! array_key_exists('value', $this->type)
            || is_array($this->type->value)
            || is_object($this->type->value))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} does not have a value member or it is an array or object, you cannot use the text widget with it.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        if ($this->maxlength == -1)
        {
            if (array_key_exists('maxlength', $this->type))
            {
                $this->maxlength = $this->type->maxlength;
            }
        }
        if ($this->maxlength < 0)
        {
            $this->maxlength = 0;
        }
        return true;
    }
    
    /**
     * Adds a simple single-line text form element at this time.
     */
    function add_elements_to_form()
    {
        $attributes = Array
        (
            'size' => $this->size,
            'class' => 'shorttext',
            'id'    => "{$this->_namespace}{$this->name}",
        );
        if ($this->maxlength > 0)
        {
            $attributes['maxlength'] = $this->maxlength;
        }

        if ($this->hideinput)
        {
            $this->form->addElement('password', $this->name, $this->translate($this->field['title']), $attributes);
        }
        else
        {
            $this->form->addElement('text', $this->name, $this->translate($this->field['title']), $attributes);
        }
        // $this->form->applyFilter($this->name, 'trim');

        if ($this->maxlength > 0)
        {
            // $errormsg = sprintf('type text: value is longer then %d characters', $this->maxlength);
            // $this->form->addRule($this->name, $errormsg, 'maxlength', $this->maxlength);
        }
    }

    public function get_default()
    {
        return $this->type->value;
    }

    public function sync_type_with_widget($results)
    {
        $this->type->value = $results[$this->name];
    }
    
}

?>