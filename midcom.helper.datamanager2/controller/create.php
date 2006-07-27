<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanger 2 Data Manager object creation controller class.
 *
 * This class replaces the legacy "creation mode" of DM1. It requires a callback that
 * creates the actual object that should be created in a correct way. The implementation
 * contained in this class should be able to avoid ghost object (a common problem in DM1)
 * in almost all cases. The only thing that is not fully catchable here are cases where
 * the type validation fails. But since all type validation rules are mappable to QuickForm
 * rules, this problem should not appear.
 *
 * Temporary objects (as initially created by the nullstorge implementations) will be
 * memorized using a hidden form variable.
 *
 * <b>Creation callback</b>
 *
 * The callback is defined using the $callback_object and $callback_method members of
 * this class. Only the first one is mandatory, the method name defaults to
 * dm2_create_callback.
 *
 * It must return a reference to a freshly created object that should be populated
 * with the validated form data. It receives a reference to the controller instance
 * calling it. Thus, a valid callback would look something like this:
 *
 * <code>
 * function & dm2_create_callback(&$controller)
 * {
 *     // ...
 *     return $object;
 * }
 * </code>
 *
 * If the callback is unable to create an empty object for whatever reason, you should
 * call generate_error. There is no error handling whatsoever on the side of this
 * controller instance. If the function returns, a valid instance is expected.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_controller_create extends midcom_helper_datamanager2_controller
{
    /**
     * The name of the hidden field used to remember the temporary object id.
     *
     * @var string
     * @access private.
     */
    var $_tmpid_fieldname = '__midcom_helper_datamanager2_controller_create_tmpid';

    /**
     * Unique identifier form a creation instance, used mainly in AJAX mode
     *
     * @var string
     * @access public
     */
    var $form_identifier = '';

    /**
     * Whether we're in AJAX mode
     *
     * @var boolean
     * @access public
     */
    var $ajax_mode = false;

    /**
     * The defaults to initialize the form manager with. This array is indexed
     * by field names.
     *
     * @param Array
     * @access public
     */
    var $defaults = Array();

    /**
     * The name of the schema to use. If this is left to null, the first schema
     * from the database is used instead.
     */
    var $schemaname = null;

    /**
     * A reference to the object containing the creation callback. This reference
     * must be set prior initialization.
     *
     * <i>Important note:</i> This member must be set as a reference, not as a copy,
     * do not forget the =& assignment, or you might encounter strange side-effects.
     *
     * @var object
     * @access public
     */
    var $callback_object = null;

    /**
     * The name of the callback method to execute. This defaults to _dm2_create_callback.
     * If this member is changed, it must be done prior initialization.
     *
     * @var object
     * @access public
     */
    var $callback_method = 'dm2_create_callback';

    /**
     * You need to set the schema database before calling this function. Optionally
     * you may set defaults and the schemaname to use as well.
     *
     * @return bool Indicating success.
     */
    function initialize()
    {
        if (count($this->schemadb) == 0)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'You must set a schema database before initializing midcom_helper_datamanager2_controller_create.');
            // This will exit.
        }
        if (! is_object($this->callback_object))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'You must set a valid callback_object prior initialization: Object is undefined.');
            // This will exit.
        }
        if (! method_exists($this->callback_object, $this->callback_method))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "You must set a valid callback_object prior initialization: Method {$this->callback_method} is undefined.");
            // This will exit.
        }

        if ($this->schemaname === null)
        {
            $this->schemaname = array_shift(array_keys($this->schemadb));
        }

        // Prepare the storage backend:
        // We use either a null or a tmp storage backend, depending on current state.
        if (array_key_exists($this->_tmpid_fieldname, $_REQUEST))
        {
            $tmpid = $_REQUEST[$this->_tmpid_fieldname];
            $object = $_MIDCOM->tmp->request_object($tmpid);
            if ($object)
            {
                $storage = new midcom_helper_datamanager2_storage_tmp($this->schemadb[$this->schemaname], $this->defaults, $object);
            }
            else
            {
                $storage = new midcom_helper_datamanager2_storage_null($this->schemadb[$this->schemaname], $this->defaults);
            }
        }
        else
        {
            $storage = new midcom_helper_datamanager2_storage_null($this->schemadb[$this->schemaname], $this->defaults);
        }

        // Prepare the DM itself
        $this->datamanager = new midcom_helper_datamanager2_datamanager($this->schemadb);
        $this->datamanager->set_schema($this->schemaname);
        $this->datamanager->set_storage($storage);

        if ($this->ajax_mode)
        {
            $this->process_ajax();
        }
        else
        {
            $this->formmanager = new midcom_helper_datamanager2_formmanager($this->datamanager->schema, $this->datamanager->types);
        }

        return $this->formmanager->initialize();
    }

    /**
     * Process AJAX-style creation requests
     */
    function process_ajax()
    {
        require_once(MIDCOM_ROOT . "/midcom/helper/datamanager2/formmanager/ajax.php");        
        
        // Add the required JavaScript
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/Pearified/JavaScript/Prototype/prototype.js");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/Pearified/JavaScript/Scriptaculous/scriptaculous.js?effects");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/midcom.helper.datamanager2/ajax.js");
        $_MIDCOM->add_jsonload("var dm2AjaxEditor_{$this->form_identifier} = new dm2AjaxEditor('{$this->form_identifier}', true);");

        $this->formmanager = new midcom_helper_datamanager2_formmanager_ajax($this->datamanager->schema, $this->datamanager->types);

        if (array_key_exists("{$this->form_identifier}_edit", $_REQUEST))
        {
            // User has requested editor
            $this->formmanager->initialize($this->form_identifier . '_qf');
            $this->formmanager->display_form($this->form_identifier);
            $state = 'ajax_editing'; 
            $_MIDCOM->finish();
            exit();    
        }
        elseif (array_key_exists("{$this->form_identifier}_preview", $_REQUEST))
        {
            $this->formmanager->initialize($this->form_identifier . '_qf');
            $this->formmanager->process_form();
            $this->formmanager->display_view($this->form_identifier);
            $state = 'ajax_preview';   
            $_MIDCOM->finish();
            exit();                     
        }        
        elseif (array_key_exists("{$this->form_identifier}_save", $_POST))
        {
            $this->formmanager->initialize($this->form_identifier . '_qf');

            // Pre process check for validation etc, we create a new object at this point if everything
            // looks fine. The change of the storage backend will only be done if we have a clear
            // save/next result from the QF layer.
            $result = $this->formmanager->compute_form_result();
            if ($result == 'save')
            {
                $this->_cast_to_storage_object();
            }            
            
            $exitcode = $this->formmanager->process_form();
            if ($exitcode == 'save')
            {
                $this->datamanager->save();    
                $this->formmanager->display_view($this->form_identifier, "midcom_helper_datamanager2_controller_ajax_{$this->datamanager->storage->object->guid}");
                $state = 'ajax_created';
            }    
            else
            {
                $this->formmanager->display_form($this->form_identifier);
                $state = 'ajax_editing';
            }
            $_MIDCOM->finish();
            exit();
        }
    }

    /**
     * This funciton wraps the form manager processing. If processing is successful and the form is
     * in 'save'ed state, the storage backend is cast to a standard midgard object, any temporary
     * resources are moved to there, and the formdata is saved.
     *
     * There are several possible return values:
     *
     * - <i>save</i> and the variants <i>next</i> and <i>previous</i> (for wizard usage) suggest
     *   successful form processing. The form has already been validated, synchronized with and
     *   saved to the data source.
     * - <i>cancel</i> the user cancelled the form processing, no I/O has been done.
     * - <i>edit</i>, <i>previous</i>, <i>next</i> indicates that the form is not yet successfully
     *   completed. This can mean many things, including validation errors, which the renderer
     *   already outlines in the Form output. No I/O processing has been done.
     *
     * The form will be automatically validated for 'save' and 'next', but not for 'previous'.
     *
     * Normally, all validation should be done during the Form processing, but sometimes this is
     * not possible. These are the cases where type validation rules fail instead of form validation
     * ones. At this time, the integration of type validation is rudimentary and will
     * transparently return edit instead of validation.
     *
     * @return string One of 'save', 'cancel', 'next', 'previous', 'edit', depending on the schema
     *     configuration.
     * @todo Integrate type validation checks cleanly.
     */
    function process_form()
    {
        if ($this->formmanager === null)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'You must initialize a controller class before using it.');
        }

        // Pre process check for validation etc, we create a new object at this point if everything
        // looks fine. The change of the storage backend will only be done if we have a clear
        // save/next result from the QF layer.
        $result = $this->formmanager->compute_form_result();
        if ($result == 'save')
        {
            $this->_cast_to_storage_object();
        }

        // Do the actual I/O
        $result = $this->formmanager->process_form();
        if (   $result == 'save'
            || $result == 'next')
        {
            // Ok, we can save now. At this point we already have a content object.
            if (! $this->datamanager->validate())
            {
                // In case that the type validation fails, we bail with generate_error, until
                // we have a better defined way-of-life here.
                $_MIDOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to save object, type validation failed:\n" . implode("\n", $this->datamanager->validation_errors));
                // This will exit.
            }

            if (   $result == 'save'
                && ! $this->datamanager->save())
            {
                // We seem to have a critical error.
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Failed to save the data to disk, check the debug level log for more information.');
                // This will exit.
            }
        }

        // While editing, we keep any temporary storage object known.
        if (   $result != 'save'
            && $result != 'cancel'
            && $this->datamanager->storage->object)
        {
            // Save temporary object ID.
            $this->formmanager->form->addElement('hidden', $this->_tmpid_fieldname, $this->datamanager->storage->object->id);
        }

        return $result;
    }

    /**
     * cast $storage to a simple midgard storage implementation. the reference should propagate this.
     * use a callback to work.
     */
    function _cast_to_storage_object()
    {
        $object =& $this->callback_object->{$this->callback_method}($this);

        // Process temporary object
        if ($this->datamanager->storage->object)
        {
            $tmp_object = $this->datamanager->storage->object;
            $tmp_object->move_extensions_to_object($object);
            $tmp_object->delete();
        }

        // Store used schema on the storage object
        $object->set_parameter('midcom.helper.datamanager2', 'schema_name', $this->schemaname);

        $storage = new midcom_helper_datamanager2_storage_midgard($this->datamanager->schema, $object);
        $this->datamanager->set_storage($storage);
    }

}

?>