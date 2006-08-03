<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanger 2 Data Manager AJAX controller class.
 *
 * This is an AJAX-enabled controller class intended for usage directly with a storage backend.
 * It has no creation support whatsoever, but the multi-edit loop will work without problems.
 * The form will only be synchronized with the datamanager if validation succeeds. (Naturally,
 * types operating directly on blobs / parameters are exempt of this.)
 *
 * You need to set both datamanager and (thus) schema database before initializing.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_controller_ajax extends midcom_helper_datamanager2_controller
{
    var $form_identifier = '';
    var $window_mode = false;
    var $_editable = null;
    
    /**
     * AJAX controller initialization. Loads required Javascript libraries
     *
     * @return bool Indicating success.
     */
    function initialize()
    {
        if (count($this->schemadb) == 0)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'You must set a schema database before initializing midcom_helper_datamanager2_controller_ajax.');
            // This will exit.
        }
        if ($this->datamanager === null)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'You must set the datamanager member before initializing midcom_helper_datamanager2_controller_ajax.');
            // This will exit.
        }
    }
    
    function _is_ajax_editable()
    {
        if (!is_null($this->_editable))
        {
            return $this->_editable;
        }
        
        // Only first instance of AJAX controller for an object per view is actually editable
        static $usedform_identifiers = Array();
        if (array_key_exists($this->form_identifier, $usedform_identifiers))
        {
            $this->_editable = false;        
            return false;
        }
        
        // Check if user can actually edit the object, otherwise no sense in returning an editable state
        if (!$this->datamanager->storage->object->can_do('midgard:update'))
        {
            $this->_editable = false;        
            return false;
        }
        
        $usedform_identifiers[$this->form_identifier] = true;        
        $this->_editable = true;
        return true;
    }
    
    /**
     * This function wraps AJAX processing completely. If component wishes to do post-processing after an edit, save or preview
     * state in this call it must set the <i>exit</i> parameter to <i>false</i>.
     * <i>view</i> state simply returns processing to component.
     *
     * The return values in this processor are:
     * 
     * - <i>view</i>: user can only view the content, no AJAX functionality available
     * - <i>ajax_editable</i>: the content is editable via AJAX, highlight
     * - <i>ajax_edit</i>: User is currently editing the contents via AJAX
     * - <i>ajax_save</i>: Content has been saved via AJAX
     * - <i>ajax_preview</i>: User is currently previewing contents via AJAX
     *
     * If required, we will here create a form manager instance and save or preview contents
     */
    function process_ajax($exit = true)
    {
        $state = 'view';
        
        $this->form_identifier = "midcom_helper_datamanager2_controller_ajax_{$this->datamanager->storage->object->guid}";

        if (!$this->_is_ajax_editable())
        {
            return $state;
        }

        // Debug helpers
        //$_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/messages.js");
        //$_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/ajaxutils.js");

        // Add the required JavaScript
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/Pearified/JavaScript/Prototype/prototype.js");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/Pearified/JavaScript/Scriptaculous/scriptaculous.js");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/midcom.helper.datamanager2/ajax.js");
        
        if ($this->window_mode)
        {
            $_MIDCOM->add_jsonload("var dm2AjaxEditor_{$this->form_identifier} = new dm2AjaxEditor('{$this->form_identifier}', false, true);");
        }
        else
        {
            $_MIDCOM->add_jsonload("var dm2AjaxEditor_{$this->form_identifier} = new dm2AjaxEditor('{$this->form_identifier}');");
        }

        $_MIDCOM->add_link_head(
            array
            (
                'rel'   => 'stylesheet',
                'type'  => 'text/css',
                'media' => 'screen',
                'href'  => MIDCOM_STATIC_URL."/midcom.helper.datamanager2/ajax.css",
            )
        );    

        if (array_key_exists("{$this->form_identifier}_edit", $_REQUEST))
        {
            // User has requested editor
            require_once(MIDCOM_ROOT . "/midcom/helper/datamanager2/formmanager/ajax.php");
            $this->formmanager = new midcom_helper_datamanager2_formmanager_ajax($this->datamanager->schema, $this->datamanager->types);
            $this->formmanager->initialize($this->form_identifier . '_qf');
            $this->formmanager->display_form($this->form_identifier);
            $state = 'ajax_editing';  
            // TODO: Lock          
        }
        elseif (array_key_exists("{$this->form_identifier}_preview", $_REQUEST))
        {
            // User has requested editor
            require_once(MIDCOM_ROOT . "/midcom/helper/datamanager2/formmanager/ajax.php");
            $this->formmanager = new midcom_helper_datamanager2_formmanager_ajax($this->datamanager->schema, $this->datamanager->types);
            $this->formmanager->initialize($this->form_identifier . '_qf');
            $this->formmanager->process_form();
            $this->formmanager->display_view($this->form_identifier);
            $state = 'ajax_preview';            
        }
        elseif (array_key_exists("{$this->form_identifier}_save", $_REQUEST))
        {        
            // User has requested editor
            require_once(MIDCOM_ROOT . "/midcom/helper/datamanager2/formmanager/ajax.php");
            $this->formmanager = new midcom_helper_datamanager2_formmanager_ajax($this->datamanager->schema, $this->datamanager->types);
            $this->formmanager->initialize($this->form_identifier . '_qf');
            $exitcode = $this->formmanager->process_form();
            if ($exitcode == 'save')
            {
                $this->datamanager->save();            
                $this->formmanager->display_view($this->form_identifier);
                $state = 'ajax_saved';
            }
            else
            {
                $this->formmanager->display_form($this->form_identifier);
                $state = 'ajax_editing';
            }
        }
        elseif (array_key_exists("{$this->form_identifier}_cancel", $_REQUEST))
        {
            // User has cancelled, display view
            require_once(MIDCOM_ROOT . "/midcom/helper/datamanager2/formmanager/ajax.php");
            $this->formmanager = new midcom_helper_datamanager2_formmanager_ajax($this->datamanager->schema, $this->datamanager->types);
            $this->formmanager->initialize($this->form_identifier . '_qf');
            //$this->formmanager->process_form();          
            $this->formmanager->display_view($this->form_identifier);
            $state = 'ajax_cancel';
            // TODO: Unlock
        }    
        elseif (array_key_exists("{$this->form_identifier}_delete", $_REQUEST))
        {
            // User has deleted, try to comply
            $this->datamanager->storage->object->delete();
            $state = 'ajax_delete';
            echo mgd_errstr();
        }      
        else
        {
            // User isn't yet in editing stage. We must however initialize form manager to load JS dependencies etc
            require_once(MIDCOM_ROOT . "/midcom/helper/datamanager2/formmanager/ajax.php");
            $this->formmanager = new midcom_helper_datamanager2_formmanager_ajax($this->datamanager->schema, $this->datamanager->types);
            $this->formmanager->initialize($this->form_identifier . '_qf');
            return $state;
        }
    
        if ($exit)
        {
            $_MIDCOM->finish();
            exit();
        }
        else
        {
            return $state;
            // Calling component must exit instead
        }
    }
    
    /**
     * Get contents of the form in AJAX-editable format
     *
     * @return Array All field values in their HTML representation indexed by their name.
     */
    function get_content_html()
    {
        if (!$this->_is_ajax_editable())
        {
            // Go with the defaults
            return $this->datamanager->get_content_html();
        }
        $result = Array();
        foreach ($this->datamanager->schema->field_order as $name)
        {
            if ($this->datamanager->schema->fields[$name]['type'] == 'composite')
            {
                // Composite type has its own AJAX controller so we don't want to add triggers to this AJAX controller
                $result[$name] = $this->datamanager->types[$name]->convert_to_html();
            }
            else
            {        
                $html_contents = $this->datamanager->types[$name]->convert_to_html();
                if (   $this->datamanager->schema->fields[$name]['required']
                    && $html_contents == '')
                {
                    // Have an identifier people can actually click and edit
                    $html_contents = "&lt;{$name}&gt;";
                }
                $result[$name] = "<span class=\"{$this->form_identifier}\" title=\"".$this->_l10n->get('double click to edit')."\" id=\"{$this->form_identifier}_{$name}\">{$html_contents}</span>\n";
            }
        }
        return $result;
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