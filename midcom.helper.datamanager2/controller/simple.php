<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanger 2 Data Manager simple controller class.
 *
 * This is a very simple controller class intended for usage directly with a storage backend.
 * It has no creation support whatsoever, but the multi-edit loop will work without problems.
 * The form will only be synchronized with the datamanager if validation succeeds. (Naturally,
 * types operating directly on blobs / parameters are exempt of this.)
 *
 * You need to set both datamanager and (thus) schema database before initializing.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_controller_simple extends midcom_helper_datamanager2_controller
{
    /**
     * Empty default implementation, this calls won't do much.
     *
     * @return bool Indicating success.
     */
    function initialize()
    {
        if (count($this->schemadb) == 0)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'You must set a schema database before initializing midcom_helper_datamanager2_controller_simple.');
            // This will exit.
        }
        if ($this->datamanager === null)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'You must set the datamanager member before initializing midcom_helper_datamanager2_controller_simple.');
            // This will exit.
        }

        $this->formmanager = new midcom_helper_datamanager2_formmanager($this->datamanager->schema, $this->datamanager->types);
        return $this->formmanager->initialize();
    }

    /**
     * This funciton wraps the form manager processing. Ifprocessing is successful, (that is,
     * only 'save'). If editing was successful, the form is frozen in case you want
     * to display it again (usually you want to redirect to the view target).
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
     * If you want to have save the data for example even during 'next', you need to call
     * datamanager->save after this function returned the according return code.
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

        $result = $this->formmanager->process_form();
        
        // Handle successful save explicitly.
        if (   $result == 'save'
            || $result == 'next')
        {
            // Ok, we can save now. At this point we already have a content object.
            if (! $this->datamanager->validate())
            {
                // In case that the type validation fails, we bail with generate_error, until
                // we have a better defined way-of-life here.
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to save object, type validation failed:\n" . implode("\n", $this->datamanager->validation_errors));
                // This will exit.
            }

            if ($result == 'save')
            {
                if (! $this->datamanager->save())
                {
                    if (count($this->datamanager->validation_errors) > 0)
                    {
                        debug_push_class(__CLASS__, __FUNCTION__);
                        debug_add('Type validation failed. Reverting to edit mode transparently.');
                        debug_print_r('Validation error listing:', $this->datamanager->validation_errors);
                        debug_pop();
                        $result = 'edit';
                    }
                    else
                    {
                        // It seems to be a critical error.
                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                            'Failed to save the data to disk, check the debug level log for more information.');
                        // This will exit.
                    }
                }
            }
        }
        // all others stay untouched.

        return $result;
    }

}

?>