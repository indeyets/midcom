<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** We depend on the PEAR Package HTML_QuickForm. */
require_once "HTML/QuickForm.php";

/**
 * Datamanager 2 Form Manager core class.
 *
 * This class controls all form rendering and basic form data i/o. It works independent
 * of any data storage, getting its defaults from some external controlling instance in
 * the form of a type array (f.x. a datamanager class can provide this). The list of types
 * is taken by-reference.
 *
 * The form rendering is done using the widgets and is based on HTML_QuickForm.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_formmanager extends midcom_baseclasses_components_purecode
{
    /**
     * The schema (not the schema <i>database!</i>) to use for operation. This variable will always contain a parsed
     * representation of the schema, so that one can swiftly switch between individual schemas
     * of the Database.
     *
     * This member is initialized by-reference.
     *
     * @var Array
     * @access protected
     */
    var $_schema = null;

    /**
     * The list of types which should be used for rendering. They must match the schemadb passed
     * to the class.
     *
     * The member is initialized by-reference.
     *
     * @var Array
     * @access protected
     */
    var $_types = null;

    /**
     * A list of widgets, indexed by the field names from the schema, thus matching the type
     * listing.
     *
     * @var Array
     */
    var $widgets = Array();

    /**
     * This is the QuickForm generated out of the schema. This member is set during the
     * initialize call.
     *
     * @var HTML_Quickform
     */
    var $form = null;

    /**
     * This is the renderer that quickform will use.
     *
     * <i>It is set using the set_renderer() or create_renderer calls.</i>
     *
     * If the configuration option 'default_renderer' (and possibly 'default_renderer_src')
     * are set, the class will create instances of these renderers during startup, so that
     * site users can directly use these defaults without further work. The following
     * rules are used when determining which renderer to use:
     *
     * If the default_renderer_src config option is null (the default), the class assumes that
     * a default MidCOM renderer from the renderer subdirectory of this component should be used.
     * In this case, you set only the actual renderer name (e.g. "simple") in default_renderer.
     *
     * On the other hand, if you set the default_renderer_src option, the system first loads
     * the snippet identified by that config option. the default_renderer option then has to
     * hold the name of the renderer class that should be created. It must be default
     * constructible.
     *
     * If you don't want any renderer to kick in, set the (default) renderer to 'none'
     * will stick to the QF Default renderer.
     *
     * <i>Authors note:</i> Be aware that on the long run this rendering system will get some
     * base classes which make building renderers with MidCOM support easier. Right now
     * we simply use the standard run-of-the-mill renderers of QF, but be prepared that the
     * API of this might change a bit (reflected in that new base class) on the long run.
     *
     * @var HTML_Quickform_Renderer subclass
     */
    var $renderer = null;

    /**
     * The namespace of the form. This value is to be considered read only.
     *
     * This is the Namespace to use for all HTML/CSS/JS elements. It is deduced by the formmanager
     * and tries to be as smart as possible to work safely with more then one form on a page.
     *
     * You have to prefix all elements which must be unique using this string (it includes a trailing
     * underscore).
     *
     * @var const string
     * @access public
     */
    var $namespace = '';

    /**
     * Number of open fieldsets. This will be used to close the same amount to prevent open fieldset tags
     *
     * @access private
     * @var integer
     */
    var $_fieldsets = 0;

    /**
     * Initializes the Form manager with a list of types for a given schema.
     *
     * @param midcom_helper_datamanager2_schema &$schema The schema to use for processing. This
     *     variable is taken by reference.
     * @param Array &$types A list of types matching the passed schema, used as a basis for the
     *     form types. This variable is taken by reference.
     */
    function midcom_helper_datamanager2_formmanager(&$schema, &$types)
    {
        $this->_component = 'midcom.helper.datamanager2';
        parent::midcom_baseclasses_components_purecode();

        if (! is_a($schema, 'midcom_helper_datamanager2_schema'))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_type('Got an object of this type:', $schema);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Invalid schema instance passed, cannot startup formmanager');
            // This will exit.
        }

        $this->_schema =& $schema;
        $this->_types =& $types;
    }

    /**
     * This function will create all widget objects for the current schema. It will load class
     * files where necessary (using require_once), and then create a set of instances
     * based on the schema.
     *
     * @param string $name The name of the field for which we should load the widget.
     * @return boolean Indicating success
     * @access protected
     */
    function _load_widget($name)
    {
        $config = $this->_schema->fields[$name];
        $filename = MIDCOM_ROOT . "/midcom/helper/datamanager2/widget/{$config['widget']}.php";
        $classname = "midcom_helper_datamanager2_widget_{$config['widget']}";
        require_once($filename);

        $this->widgets[$name] = new $classname();
        if (! $this->widgets[$name]->initialize($name, $config['widget_config'], $this->_schema, $this->_types[$name], $this->namespace))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to initialize the widget for {$name}, see the debug level log for full details, this field will be skipped.",
                MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
        return true;
    }

    function _load_type_qfrules($fieldname)
    {
        $config = $this->_schema->fields[$fieldname];
        $filename = MIDCOM_ROOT . "/midcom/helper/datamanager2/QuickForm_rules/{$config['type']}.php";
        $classname = "midcom_helper_datamanager2_qfrule_{$config['type']}_manager";
        if (class_exists($classname))
        {
            // We have already initialized rules for this type
            return;
        }
        if (!file_exists($filename))
        {
            // no file for this type found, skip silently
            return;
        }
        include_once($filename);
        $manager = new $classname();
        $manager->register_rules($this->form);
    }

    /**
     * This function fully initializes the class for operation. This is not done during the
     * constructor call, to allow for full reference safety.
     *
     * @param name The name of the form. This defaults to the name of the currently active component, which should
     *     suffice in most cases.
     * @return boolean Indicating success.
     * @todo Refactor into subfunctions for better readability.
     */
    function initialize($name = null)
    {
        if ($name === null)
        {
            $name = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_COMPONENT);
            // Replace the dots in the component name with underscores
            $name = $_MIDCOM->componentloader->path_to_prefix($name);
        }
        if (! $name)
        {
            // Fallback for componentless operation
            $name = 'midcom_helper_datamanager2';
        }

        $this->namespace = "{$name}_";

        $this->form = new HTML_QuickForm($name, 'post', $_SERVER['REQUEST_URI'], '_self', Array('id' => $name), true);
        $defaults = Array();
        $this->widgets = Array();

        // iterate over all widgets so that they can add their piece to the form
        foreach ($this->_schema->fields as $name => $config)
        {
            if (! $this->_is_widget_visible($name, $config))
            {
                // Naturally we should skip invisible objects
                continue;
            }

            if (! $this->_load_widget($name))
            {
                continue;
            }

            // Start the fieldsets if required
            $this->_start_fieldset($name, $config);

            if ($config['static_prepend'] !== null)
            {
                $static_name = "__static_prepend_{$name}";
                $this->form->addElement('static', $static_name, '', $config['static_prepend']);
            }

            $this->widgets[$name]->set_form($this->form);
            $this->widgets[$name]->add_elements_to_form();
            $this->_add_rules_and_filters($name, $config);
            $field_default = $this->widgets[$name]->get_default();
            if ($field_default !== null)
            {
                if (is_object($field_default))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("An object has been passed as widget default value for {$name}, this is not allowed, skipping default.",
                        MIDCOM_LOG_WARN);
                    debug_print_r('Passed object was:', $field_default);
                    debug_pop();
                }
                else if (is_array($field_default))
                {
                    $defaults = array_merge($defaults, $field_default);
                }
                else
                {
                    $defaults[$name] = $field_default;
                }
            }

            if (!is_null($config['default']))
            {
                $schema_default = $config['default'];
                if (is_object($schema_default))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("An object has been passed as schema's default value for {$name}, this is not allowed, skipping default.",
                        MIDCOM_LOG_WARN);
                    debug_print_r('Passed object was:', $schema_default);
                    debug_pop();
                }
                else if (is_array($schema_default))
                {
                    $defaults = array_merge($defaults, $schema_default);
                }
                else
                {
                    $defaults[$name] = $schema_default;
                }
            }

            if ($config['static_append'] !== null)
            {
                $static_name = "__static_append_{$name}";
                $this->form->addElement('static', $static_name, '', $config['static_append']);
            }

            // End the fieldsets if required
            $this->_end_fieldset($name, $config);
        }

        // Set the collected defaults.
        $this->form->setDefaults($defaults);

        // Close the fieldsets left open
        if ($this->_fieldsets > 0)
        {
            $this->_end_fieldset('', array('end_fieldset' => $this->_fieldsets));
        }


        // Add the submit / cancel buttons
        $buttons = Array();
        foreach ($this->_schema->operations as $operation => $label)
        {
            if ($label == '')
            {
                $label = "form submit: {$operation}";
            }
            $buttonname = "midcom_helper_datamanager2_{$operation}";
            $buttonlabel = $this->_schema->translate_schema_string($label);

            if ($operation == 'save')
            {
                $accesskey = 's';
            }
            elseif ($operation == 'cancel')
            {
                $accesskey = 'c';
            }
            else
            {
                $accesskey = '';
            }

            $buttons[] = &HTML_QuickForm::createElement('submit', $buttonname, $buttonlabel, Array('class' => $operation, 'accesskey' => $accesskey));
        }
        $this->form->addGroup($buttons, 'form_toolbar', null, '&nbsp;', false);

        // Add form-wide validation rules
        foreach ($this->_schema->validation as $config)
        {
            if (! is_callable($config['callback']))
            {
                // Try autoload:
                if (array_key_exists('autoload_snippet', $config))
                {
                    mgd_include_snippet_php($config['autoload_snippet']);
                }
                if (array_key_exists('autoload_file', $config))
                {
                    require_once($config['autoload_file']);
                }

                if (! function_exists($config['callback']))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to register the callback {$config['callback']} for validation, the function is not defined.", MIDCOM_LOG_CRIT);
                    debug_pop();
                    continue;
                }
            }
            $this->form->addFormRule($config['callback']);
        }

        // Add form-wide filter rules
        foreach ($this->_schema->filters as $config)
        {
            if (! class_exists($config['callback']))
            {
                // Try autoload:
                if (array_key_exists('autoload_snippet', $config))
                {
                    mgd_include_snippet_php($config['autoload_snippet']);
                }
                if (array_key_exists('autoload_file', $config))
                {
                    require_once($config['autoload_file']);
                }

                if (! class_exists($config['callback']))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to register the callback {$config['callback']} for validation, the class is not defined.", MIDCOM_ERRCRIT);
                    debug_pop();
                    continue;
                }
            }

            // Now create the instance
            if (array_key_exists('constructor_argument', $config))
            {
                $arg = $config['constructor_argument'];
            }
            else
            {
                $arg = null;
            }
            $callback_object = new $config['callback']($this, $arg);
            $callback = Array(&$callback_object, 'execute');

            // Compute the field list.
            if (array_key_exists('fields', $config))
            {
                $fields = $config['fields'];
                if (is_string($fields))
                {
                    $fields = Array($fields);
                }
            }
            else
            {
                $fields = null;
            }

            if (! $fields)
            {
                $fields = $this->_schema->field_order;
            }

            // Now fire away.
            foreach ($fields as $name)
            {
                $callback_object->set_fieldname($name);
                $this->form->applyFilter($name, $callback);
            }
        }

        // Translate the requred note
        $this->form->setRequiredNote
        (
            '<span style="font-size:80%; color:#ff0000;">*</span>' .
            '<span style="font-size:80%;">' .
            $this->_l10n->get('denotes required field') .
            '</span>'
        );

        // Finally create the default renderer specified in the configuration.
        $this->_create_default_renderer();

        return true;
    }

    /**
     * Start a fieldset and set the styles accordingly. Fieldsets can be initialized either
     * by key `start_fieldset` or `start_fieldgroup` to provide backwards compatibility to
     * @see midcom_helper_datamanager_schema
     *
     * @access private
     * @param string $name
     * @param Array $config
     */
    function _start_fieldset($name, $config)
    {
        // Backwards compatibility for midcom.helper.datamanager styled fieldgroups
        if (   isset($config['start_fieldgroup'])
            && !isset($config['start_fieldset']))
        {
            $config['start_fieldset'] = $config['start_fieldgroup'];
        }

        // Return if fieldsets are not requested
        if (!isset($config['start_fieldset']))
        {
            return;
        }

        // Enable multiple fieldset starts in the same schema field
        $fieldsets = array();
        if (isset($config['start_fieldset']['title']))
        {
            $fieldsets[] = $config['start_fieldset'];
        }
        else
        {
            $fieldsets = $config['start_fieldset'];
        }

        // Output the fieldsets
        foreach ($fieldsets as $key => $fieldset)
        {
            if (isset($fieldset['css_group']))
            {
                $class = $fieldset['css_group'];
            }
            else
            {
                $class = $name;
            }

            $html = "<fieldset class=\"fieldset {$class}\">\n";

            if (isset($fieldset['title']))
            {
                if (isset($fieldset['css_title']))
                {
                    $class = " class=\"{$fieldset['css_title']}\"";
                }
                else
                {
                    $class = " class=\"{$name}\"";
                }

                $html .= "    <legend{$class}>\n";
                $html .= "        ". $this->_translate($fieldset['title']) ."\n";
                $html .= "    </legend>\n";
            }

            if (isset($fieldset['description']))
            {
                $html .= "<p>". $this->_translate($fieldset['description']) . "</p>\n";
            }

            $set =& HTML_QuickForm::createElement('static', "__fieldset_start_{$name}_{$key}", $html);
            $this->form->addElement($set);
            $this->_fieldsets++;
        }
    }

    /**
     * End a fieldset. Ends the requested amount of fieldsets when 'end_fieldset' key is
     * defined in the schema field or at least once when the key exists in the field.
     *
     * @access private
     * @param string $name
     * @param Array $config
     */
    function _end_fieldset($name, $config)
    {
        // Backwards compatibility for midcom.helper.datamanager styled fieldgroups
        if (   isset($config['end_fieldgroup'])
            && !isset($config['end_fieldset']))
        {
            $config['end_fieldset'] = $config['end_fieldgroup'];
        }

        if (   !isset($config['end_fieldset'])
            || $this->_fieldsets <= 0)
        {
            return;
        }

        $html = '';

        // Interface for closing the fieldsets
        if (is_numeric($config['end_fieldset']))
        {
            for ($i = 0; $i < $config['end_fieldset']; $i++)
            {
                $html .= "</fieldset>\n";
                $this->_fieldsets--;

                if ($this->_fieldsets <= 0)
                {
                    break;
                }
            }
        }
        else
        {
            $html .= "</fieldset>\n";
            $this->_fieldsets--;
        }

        $set =& HTML_QuickForm::createElement('static', "__fieldset_end_{$name}", $html);
        $this->form->addElement($set);
    }

    /**
     * Sets the form's renderer based on an existing renderer instance.
     *
     * @param mixed &$renderer A prepared HTML_QuickForm_Renderer (or subclass thereof) instance.
     * @return void
     */
    function set_renderer (&$renderer)
    {
        $this->renderer =& $renderer;
    }

    /**
     * Creates a new instance of the renderer specified by $name. It must be a valid renderer
     * name of the renderers defined in the renderer subdirectory of this component. The file
     * loaded is renderer/$name.php, while the class instance created is
     * midcom_helper_datamanager2_renderer_{$name}. The class must be default constructible
     * and will be available in the renderer member of this class.
     *
     * If name is 'none', no renderer instance is created, instead the default QuickForm
     * Renderer is activated.
     *
     * You cannot create custom renderer instances with this function, you need to create the
     * instance manually and set it using set_renderer().
     *
     * @param string $name The renderer to create
     */
    function create_renderer($name)
    {
        if ($name == 'none')
        {
            $this->renderer = 'none';
        }
        else
        {
            require_once("renderer/{$name}.php");
            $classname = "midcom_helper_datamanager2_renderer_{$name}";
            $this->renderer = new $classname();
        }
    }

    /**
     * This helper function checks whether a given widget is visible.
     *
     * @param string $name The name of the widget.
     * @param Array $config Widget configuration.
     * @return boolean Visibility state.
     * @access private
     */
    function _is_widget_visible($name, $config)
    {
        if (   $config['hidden']
            || (   $config['aisonly']
                && $_MIDCOM->get_context_data(MIDCOM_CONTEXT_REQUESTTYPE) == MIDCOM_REQUEST_CONTENTADM))
        {
            return false;
        }

        if ($config['read_privilege'] !== null)
        {
            if (   array_key_exists('group', $config['read_privilege'])
                && ! $_MIDCOM->auth->is_group_member($config['read_privilege']['group']))
            {
                return false;
            }
            if (   array_key_exists('privilege', $config['read_privilege'])
                && ! $this->_types[$name]->can_do($config['read_privilege']['privilege']))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Special form freeze handling.
     */
    function freeze()
    {
        $this->form->freeze();
        foreach ($this->widgets as $id => $copy)
        {
            $this->widgets[$id]->freeze();
        }
    }

    /**
     * Special form freeze handling.
     */
    function unfreeze()
    {
        $this->form->unfreeze();
        foreach ($this->widgets as $id => $copy)
        {
            $this->widgets[$id]->unfreeze();
        }
    }

    /**
     * This helper function adds all rules and filters which are deducable from the schema
     * to the form. It recognizes the following schema options:
     *
     * - required: Adds a required rule to the form, bound to the given element.
     * @param string $name The name of the widget.
     * @param Array $config Widget configuration.
     */
    function _add_rules_and_filters($name, $config)
    {
        $this->_load_type_qfrules($name);
        $widget =& $this->widgets[$name];
        if ($config['readonly'])
        {
            $widget->freeze();
        }
        if ($config['write_privilege'] !== null)
        {
            if (   array_key_exists('group', $config['write_privilege'])
                && ! $_MIDCOM->auth->is_group_member($config['write_privilege']['group']))
            {
                $widget->freeze();
            }
            if (   array_key_exists('privilege', $config['write_privilege'])
                && ! $this->_types[$name]->can_do($config['write_privilege']['privilege']))
            {
                $widget->freeze();
            }
        }

        if ($widget->is_frozen())
        {
            // We skip the rest, as these rules make only sense if an element is
            // not frozen, e.g. editable by the user. It makes no sense having rules
            // for read-only fields
            return;
        }

        if ($config['required'])
        {
            $message = sprintf
            (
                $this->_l10n->get('field %s is required'),
                $this->_schema->translate_schema_string($config['title'])
            );
            $type =& $this->_types[$name];
            $type_class = get_class($type);
            switch (true)
            {
                // Match single image types (image & photo ATM)
                case (   is_a($type, 'midcom_helper_datamanager2_type_image')
                      && !is_a($type, 'midcom_helper_datamanager2_type_images')):
                    // 'required' does not work for uploads -> use 'uploadedfile'
                    // OTOH: Does this mean it requires new upload each time ?? TODO: Test
                    $this->form->addRule($name, $message, 'uploadedfile', '');
                    break;
                // Match all other blobs types (those allow multiple uploads which are kind of hard to validate)
                case (is_a($type, 'midcom_helper_datamanager2_type_blobs')):
                    // PONDER: How will you require-validate N uploads ?? (also see the point about existing files above)
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("types with multiple files cannot have required validation (field name: {$name})", MIDCOM_LOG_ERROR);
                    debug_pop();
                    break;
                // Match select types (have array data, 'required' will choke on them)
                case (   is_a($type, 'midcom_helper_datamanager2_type_select')
                      && $type->allow_multiple):
                    debug_add("Callign this->form->addRule('{$name}', '{$message}', 'requiremultiselect', '');");
                    $stat = $this->form->addRule($name, $message, 'requiremultiselect', '');
                    if (is_a($stat, 'pear_error'))
                    {
                        $msg = $stat->getMessage();
                        debug_push_class(__CLASS__, __FUNCTION__);
                        debug_add("Got PEAR error '{$msg}' from this->form->addRule('{$name}', '{$message}', 'required_multiselect', ''), when adding multiselect required rule", MIDCOM_LOG_WARN);
                        debug_pop();
                    }
                    break;
                // Other types should be fine with the default string validation offered by 'required'
                default:
                    $this->form->addRule($name, $message, 'required', '');
                    break;
            }
        }

        foreach ($config['validation'] as $key => $rule)
        {
            switch ($rule['type'])
            {
                case 'compare':
                    $message = $this->_schema->translate_schema_string($rule['message']);
                    $result = $this->form->addRule(array($rule['compare_with'], $name), $message, $rule['type'], $rule['format']);
                    break;

                default:
                    $message = $this->_schema->translate_schema_string($rule['message']);
                    $this->form->addRule($name, $message, $rule['type'], $rule['format']);
                    break;
            }
        }
    }

    /**
     * Set the value of a formelement.
     * @param string $key the form field name
     * @param string $value the new value to set
     */
    function set_value( $key, $value )
    {
       $element =&   $this->_controller->formmanager->form->getElement( $key);
       $element->setValue( $value );
       return true;
    }


    /**
     * Creates an instance of the renderer set in the system configuration. This is called
     * during the initialize code and will make the renderer available immediately after startup.
     */
    function _create_default_renderer()
    {
        $default = $this->_config->get('default_renderer');
        if ($default == 'none')
        {
            $this->renderer = 'none';
            return;
        }

        $src = $this->_config->get('default_renderer_src');

        if ($src)
        {
            // Ensure that the snippet is only loaded once.
            if (! class_exists($default))
            {
                mgd_include_snippet_php($src);
                if (! class_exists($default))
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                        "The renderer class set in the DM2 configuration does not exist.");
                    // This will exit.
                }
            }
            $this->renderer = new $default();
        }
        else
        {
            $this->create_renderer($default);
        }
    }

    /**
     * This call will render the form.
     */
    function display_form()
    {
        if (   ! $this->renderer
            ||  ( is_string($this->renderer) && $this->renderer == 'none'))
        {
            echo $this->form->toHtml();
        }
        else
        {
            $this->form->accept($this->renderer);
            echo $this->renderer->toHtml();
        }
    }

    /**
     * This function displays a quick view of the record, using some simple div based layout,
     * which can be formatted using CSS.
     *
     * @todo Make this better customizable
     * @todo Factor this out into a separate class This function should be deprecated.
     */
    function display_view()
    {
        // iterate over all widgets so that they can add their piece to the form
        foreach ($this->widgets as $name => $copy)
        {
            echo '<div class="title" style="font-weight: bold;">' . $this->_translate($this->_schema->fields[$name]['title']) . "</div>\n";
            echo '<div class="value" style="margin-left: 5em;">';
            echo $this->widgets[$name]->render_content();
            echo "</div>\n";
        }
    }

    /**
     * This function computes the form result based on the button clicked and on the current
     * validation result.
     *
     * Validation is only checked when save/next has been clicked; if it fails, the exitcode
     * reverts to 'edit'.
     *
     * @return string One of 'editing', 'save', 'next', 'previous' and 'cancel'
     */
    function compute_form_result()
    {
        $results = $this->form->getSubmitValues(true);
        $exitcode = $this->get_clicked_button();

        if (   $exitcode == 'save'
            || $exitcode == 'next')
        {
            // Validate the from.
            if (! $this->form->validate())
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('Failed to validate the form, reverting to edit mode.');
                //debug_print_r('Results were:', $results);
                debug_pop();
                $exitcode = 'edit';
            }
        }
        return $exitcode;
    }
    /**
     * Use this function to get the values of submitted form without going through
     * a storage backend.
     * @return array the submitted values.
     *
     */
    function get_submit_values(  )
    {
        return $this->form->getSubmitValues( true );
    }

    /**
     * Call this before any output is made. It will process the form results, if applicable,
     * and return an according exit code. This indicates which (if any) submit button was pressed.
     * If 'editing' is returned, this means that either there was not data submitted yet, or that
     * form validation has failed.
     *
     * //This call ensures that MidCOM runs uncached.
     *
     * @return string One of 'editing', 'save', 'next', 'previous' and 'cancel'
     */
    function process_form($ajax_mode = false)
    {
        // Make sure we have CSS loaded
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/midcom.helper.datamanager2/legacy.css",
            )
        );
        /* breaks caching on pages that DL simple forms, and with new invalidation shouldn't be necessary
        $_MIDCOM->cache->content->no_cache();
        */
        $results = $this->form->getSubmitValues(true);

        // Get the result (this includes validation)
        $exitcode = $this->compute_form_result();

        // Check if we were really submitted, if yes, trigger the on_submit event
        // on the widgets as well:
        if (   $exitcode != 'cancel'
            && $exitcode != 'previous')
        {
            foreach ($this->widgets as $name => $copy)
            {
                $this->widgets[$name]->on_submit($results);
            }
        }

        if (   $exitcode == 'save'
            || $exitcode == 'next'
            || $exitcode == 'preview')
        {
            // Iterate over the widgets and tell them to re-synchronize with their
            // types.
            foreach ($this->widgets as $name => $copy)
            {
                if ($ajax_mode)
                {
                    if (array_key_exists($name, $results))
                    {
                        $this->widgets[$name]->sync_type_with_widget($results);
                    }
                }
                else
                {
                    if (!array_key_exists($name, $results))
                    {
                        $results[$name] = null;
                    }
                    $this->widgets[$name]->sync_type_with_widget($results);
                }
            }
        }

        return $exitcode;
    }

    /**
     * This is a shortcut to the translate_schema_string function.
     *
     * @param string $string The string to be translated.
     * @return string The translated string.
     * @see midcom_helper_datamanager2_schema::translate_schema_string()
     */
    function _translate($string)
    {
        return $this->_schema->translate_schema_string($string);
    }

    /**
     * This is a shortcut function which can be called statically. It allows
     * the calling application to determine the pre-validation return code of
     * the current form.
     *
     * This function can be called statically and does therefore *not* do any
     * form specific operations. It is primarily geared on shortcutting out of
     * existing processing chains to avoid the expensive controller startup
     * operation in cases where cancel or similar buttons are clicked.
     *
     * @return string One of 'editing', 'save', 'next', 'previous' and 'cancel'
     */
    function get_clicked_button()
    {
        if (array_key_exists('midcom_helper_datamanager2_save', $_REQUEST))
        {
            return 'save';
        }
        else if (array_key_exists('midcom_helper_datamanager2_next', $_REQUEST))
        {
            return 'next';
        }
        else if (array_key_exists('midcom_helper_datamanager2_previous', $_REQUEST))
        {
            return 'previous';
        }
        else if (array_key_exists('midcom_helper_datamanager2_cancel', $_REQUEST))
        {
            return 'cancel';
        }
        else if (array_key_exists('midcom_helper_datamanager2_preview', $_REQUEST))
        {
            return 'preview';
        }
        else
        {
            return 'edit';
        }
    }

}
