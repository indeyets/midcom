<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager Data Type interface.
 *
 *
 * @package midcom_helper_datamanager
 */
interface midcom_helper_datamanager_widget
{
    /**
     * Initializes and configures the widget.
     *
     * @see midcom_helper_datamanager_widget_baseclass::__construct
     */
    public function __construct($name, $config, &$schema, &$type, $namespace);

    /**
     * Set the form reference.
     *
     * @see midcom_helper_datamanager_widget_baseclass::set_form
     */
    function set_form(&$form);
    
    /**
     * This function is called  before the configuration keys are merged into the types
     * configuration.
     *
     * @see midcom_helper_datamanager_widget_baseclass::on_configuring
     */
    private function on_configuring($config);
    
    /**
     * This event handler is called during construction, so passing references to $this to the
     * outside is unsafe at this point.
     *
     * @see midcom_helper_datamanager_widget_baseclass::on_initalize
     */
    protected function on_initialize();

    /**
     * This event handler is called if and only if the Formmanager detects an actual
     * form submission (this is tracked using a hidden form member). 
     * 
     * No Form validation has been done at this point. The event is triggered on all 
     * submissions with the exception of the cancel and previous form events.
     *
     * You should be careful when using this event for data
     * processing therefore. Its main application is the processing of additional buttons
     * placed into the form by the widget.
     *
     * The implementation of this handler is optional.
     *
     * @param Array $results The complete form results, you need to extract all values
     *     relevant for your type yourself.
     *
     * @see midcom_helper_datamanager_widget_baseclass::on_submit
     */
    public function on_submit($results) {}

    /**
     * This function is invoked if the widget should extract the corresponding data
     * from the form results passed in $results. 
     * 
     * Form validation has already been done before, this function will only be called 
     * if and only if the form validation succeeds.
     *
     * @param Array $results The complete form results, you need to extract all values
     *     relevant for your type yourself.
     *
     * @see midcom_helper_datamanager_widget_baseclass::sync_type_with_widget
     */
    public function sync_type_with_widget($results);

    /**
     * This is a shortcut to the translate_schema_string function.
     *
     * @param string $string The string to be translated.
     * @return string The translated string.
     * @see midcom_helper_datamanager_widget_baseclass::translate
     */
    private function translate($string);

    /**
     * This call, which must be overridden by subclasses, adds the necessary form elements
     * to the form passed by reference.
     *
     * This must be overridden in subclasses (honor the reference!).
     *
     * @see midcom_helper_datamanager_widget_baseclass::add_elements_to_form
     */
    public function add_elements_to_form();

    /**
     * Returns the default value for this field as required by HTML_Quickform. 
     * 
     * You may either return a single value for simple types, or an array of form 
     * field name / value pairs in case of composite types. A value of null indicates 
     * no applicable default.
     *
     * This default implementation returns null unconditionally.
     *
     * @see midcom_helper_datamanager_widget_baseclass::get_default
     */
    public function get_default();

    /**
     * When called, this method should display the current data without any
     * editing widget or surrounding braces in plain and simple HTML.
     *
     * The default implementation calls the type's convert_to_html method.
     *
     * @see midcom_helper_datamanager_widget_baseclass::render_content
     */
    public function render_content();
    
    /**
     * Freezes all form elements associated with the widget. 
     * 
     * The default implementation works on the default field name, you don't need to override 
     * this function unless you have multiple widgets in the form.
     *
     * @see midcom_helper_datamanager_widget_baseclass::freeze
     */
    public function freeze();

    /**
     * Unfreezes all form elements associated with the widget. 
     * 
     * The default implementation works on the default field name, you don't need to override 
     * this function unless you have multiple widgets in the form.
     *
     * @see midcom_helper_datamanager_widget_baseclass::unfreeze
     */
    public function unfreeze();

    /**
     * Checks if the widget is frozen. 
     * 
     * The default implementation works on the default field name, usually you don't need to 
     * override this function unless you have some strange form element logic.
     *
     * @see midcom_helper_datamanager_widget_baseclass::is_frozen
     */
    public function is_frozen();
}

/**
 * Datamanager Widget base class.
 *
 * As with all subclasses, the actual initialization is done in the initialize() function,
 * not in the constructor, to allow for error handling.
 *
 * Quick glance at the changes
 *
 * - No more form prefixes, use the field name as a form field name
 * - Now uses class members, which should use initializers (var $name = 'default_value';)
 *   for configuration defaults.
 * - The schema configuration ('widget_config') is merged using the semantics
 *   $widget->$key = $value;
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_widget_baseclass implements midcom_helper_datamanager_widget
{
    /**
     * This is a reference to the type we're based on.
     *
     * @var midcom_helper_datamanager_type
     */
    protected $type = null;
    
    /**
     * The name field holds the name of the field the widget is encapsulating. 
     * 
     * This maps to the schema's field name. You should never have to change them.
     *
     * @var string
     */
    public var $name = '';

    /**
     * The schema (not the schema <i>database!</i>) to use for operation. 
     * 
     * This variable will always contain a parsed representation of the schema, so that 
     * one can swiftly switch between individual schemas of the Database.
     *
     * This member is initialized by-reference.
     *
     * @var Array
     */
    protected var $schema = null;

    /**
     * A reference to the schema field we should draw. 
     * 
     * Description texts etc. are taken from here.
     *
     * @var Array
     */
    protected var $field = null;
    

    /**
     * This is the Namespace to use for all HTML/CSS/JS elements. 
     * 
     * It is deduced by the formmanager and tries to be as smart as possible to work safely with 
     * more then one form on a page.
     *
     * You have to prefix all elements which must be unique using this string (it includes a 
     * trailing underscore).
     *
     * @var string
     */
    private var $namespace = null;

    /**
     * The form we are using.
     */
    protected var $form = null;
    
    /**
     * Initializes and configures the widget.
     *
     * @param string $name The name of the field to which this widget is bound.
     * @param Array $config The configuration data which should be used to customize the widget.
     * @param midcom_helper_datamanager_schema &$schema A reference to the full schema object.
     * @param midcom_helper_datamanager_type $type A reference to the type to which we are bound.
     * @param string $namespace The namespace to use including the trailing underscore.
     * @param boolean $initialize_dependencies Whether to load JS and other dependencies on initialize
     * @return boolean Indicating success. If this is false, the type will be unusable.
     */
    public function __construct($name, $config, &$schema, &$type, $namespace)
    {
        $this->name = $name;
        $this->schema =& $schema;
        $this->field =& $schema->fields[$this->name];
        $this->type =& $type;
        $this->namespace = $namespace;
        $this->initialize_dependencies = $initialize_dependencies;

        // Call the event handler for configuration in case we have some defaults that cannot
        // be covered by the class initializers.
        $this->on_configuring();

        // Assign the configuration values.
        foreach ($config as $key => $value)
        {
            $this->$key = $value;
        }

        if (! $this->on_initialize())
        {
            return false;
        }
        return true;        
    }

    /**
     * Set the form reference.
     *
     */
    public function set_form(&$form)
    {
        $this->form =& $form;
    }
    
    /**
     * This function is called  before the configuration keys are merged into the types
     * configuration.
     */
    private function on_configuring() {}

    /**
     * This event handler is called during construction, so passing references to $this to the
     * outside is unsafe at this point.
     *
     * @return boolean Indicating success, false will abort the type construction sequence.
     */
    protected function on_initialize()
    {
        return true;
    }

    /**
     * This event handler is called if and only if the Formmanager detects an actual
     * form submission (this is tracked using a hidden form member). 
     * 
     * No Form validation has been done at this point. The event is triggered on all 
     * submissions with the exception of the cancel and previous form events.
     *
     * You should be careful when using this event for data
     * processing therefore. Its main application is the processing of additional buttons
     * placed into the form by the widget.
     *
     * The implementation of this handler is optional.
     *
     * @param Array $results The complete form results, you need to extract all values
     *     relevant for your type yourself.
     */
    public function on_submit($results) {}

    /**
     * This call, which must be overridden by subclasses, adds the necessary form elements
     * to the form passed by reference.
     *
     * This must be overridden in subclasses
     */
    public function add_elements_to_form()
    {
        die ('The function ' . __CLASS__ . '::' . __FUNCTION__ . ' must be implemented in subclasses.');
    }

    /**
     * Returns the default value for this field as required by HTML_Quickform. 
     * 
     * You may either return a single value for simple types, or an array of form 
     * field name / value pairs in case of composite types. A value of null indicates 
     * no applicable default.
     *
     * This default implementation returns null unconditionally.
     *
     * @return mixed The default value as outlined above.
     */
    public function get_default()
    {
        return null;
    }

    /**
     * This function is invoked if the widget should extract the corresponding data
     * from the form results passed in $results. 
     * 
     * Form validation has already been done before, this function will only be called 
     * if and only if the form validation succeeds.
     *
     * @param Array $results The complete form results, you need to extract all values
     *     relevant for your type yourself.
     */
    public function sync_type_with_widget($results)
    {
         die ('The function ' . __CLASS__ . '::' . __FUNCTION__ . ' must be implemented in subclasses.');
    }

    /**
     * This is a shortcut to the translate_schema_string function.
     *
     * @param string $string The string to be translated.
     * @return string The translated string.
     * @see midcom_helper_datamanager_schema::translate_schema_string()
     */
    private function translate($string)
    {
        return $this->schema->translate_schema_string($string);
    }

    /**
     * When called, this method should display the current data without any
     * editing widget or surrounding braces in plain and simple HTML.
     *
     * The default implementation calls the type's convert_to_html method.
     */
    public function render_content()
    {
        return $this->type->convert_to_html();
    }
    
    /**
     * Freezes all form elements associated with the widget. 
     * 
     * The default implementation works on the default field name, you don't need to override 
     * this function unless you have multiple widgets in the form.
     */
    public function freeze()
    {
        $element =& $this->form->getElement($this->name);
        if (method_exists($element, 'freeze'))
        {
            $element->freeze();
        }
    }

    /**
     * Unfreezes all form elements associated with the widget. 
     * 
     * The default implementation works on the default field name, you don't need to override 
     * this function unless you have multiple widgets in the form.
     */
    public function unfreeze()
    {
        $element =& $this->form->getElement($this->name);
        $element->unfreeze();
    }

    /**
     * Checks if the widget is frozen. 
     * 
     * The default implementation works on the default field name, usually you don't need to 
     * override this function unless you have some strange form element logic.
     *
     * @return boolean True if the element is frozen, false otherwise.
     */
    public function is_frozen()
    {
        $element =& $this->form->getElement($this->name);
        return $element->isFrozen();
    }
}

?>