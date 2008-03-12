<?php
/**
 * @package no.odindata.quickform
 * @author Tarjei Huse, tarjei@nu.no
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * midcom_helper_datamanager_getvar
 *
 * this class extends DM and only implements one function that, when it is perfect
 * should be ported back to dm.
 * @package no.odindata.quickform
 */
class midcom_helper_datamanager_getvar extends midcom_helper_datamanager
{
    /**
     * Possible file attachments indexed by field name
     */
    var $attachments = array();

    function process_form_to_array ()
    {
        //global $midcom_errstr;
        $success = true;
        $this->errstr = "";

        debug_push ('midcom_helper_datamanager::process_form_to_array');


        /*** EDIT FORM: CANCEL ***/
        if (array_key_exists ($this->form_prefix . 'cancel', $_REQUEST))
        {
            debug_add('CANCEL: Editing aborted.');
            $this->_processing_result = MIDCOM_DATAMGR_CANCELLED;

            return $this->_processing_result;
        }

        /*** EDIT FORM: SUBMIT ***/
        if (array_key_exists ($this->form_prefix . 'submit', $_REQUEST))
        {
            $this->_processing_result = MIDCOM_DATAMGR_SAVED;
            foreach ($this->_fields as $name => $field)
            {
                switch (get_class($this->_datatypes[$name]))
                {
                    case 'midcom_helper_datamanager_datatype_blob':
                        if (is_uploaded_file($_FILES["midcom_helper_datamanager_field_{$name}"]['tmp_name']))
                        {
                            $this->attachments[$name] = array
                            (
                                'name'     => basename($_FILES["midcom_helper_datamanager_field_{$name}"]['name']),
                                'mimetype' => $_FILES["midcom_helper_datamanager_field_{$name}"]['type'],
                                'content'  => file_get_contents($_FILES["midcom_helper_datamanager_field_{$name}"]['tmp_name']),
                            );
                        }
                        break;

                    default:
                        if (array_key_exists("midcom_helper_datamanager_field_{$name}", $_POST))
                        {
                            $this->data[$name] = $_REQUEST['midcom_helper_datamanager_field_' .$name];
                        }
                        break;
                }
            }
            foreach ($this->_fields as $name => $field)
            {
                if (array_key_exists("midcom_helper_datamanager_field_{$name}", $_POST))
                {
                    $this->data[$name] = $_REQUEST["midcom_helper_datamanager_field_{$name}"];
                }
            }
            /**
             * First, synchronize all data and check for required fields.
             * Note, that this place could be used for validation as well.
             * For readonly/hidden fields, do the opposite, resync the widget
             * with the datatype, just to be on the safe side.
             */
            $this->_missing_required_fields = Array();

            foreach ($this->_fields as $name => $field)
            {
                $object =& $this->_datatypes[$name];
                if (   $field['readonly'] === true
                    || $field['hidden'] === true
                    || $field['aisonly'] === true
                    && !array_key_exists('view_contentmgr', $GLOBALS))
                {
                    $object->sync_widget_with_data();
                }
                else
                {
                    $object->sync_data_with_widget();
                }

                if (   $field['required'] === true
                    && $object->is_empty() == true)
                {
                    $this->_missing_required_fields[] = $name;
                    $msg = sprintf($this->_l10n->get('required field missing'), $field['description']);
                    $this->append_error("<p class=\"error\">{$msg}</p>\n");
                    $success = false;
                }

                /* input validation  */
                if (   $success
                    && array_key_exists('validation', $field))
                {
                    if (!is_object($this->_rule_registry))
                    {
                        /* if quickform is not installed, return true.  */
                        if (include_once ('HTML/QuickForm/RuleRegistry.php'))
                        {
                            $this->_rule_registry =& HTML_QuickForm_RuleRegistry::singleton();
                        }
                        else
                        {
                            debug_pop();
                            return true;
                        }
                    }
                    $success = $object->validate($field['description'], $field['validation'],$name);
                }
            }

            if (!$success)
            {
                $this->_processing_result = MIDCOM_DATAMGR_EDITING;
            }

            debug_add ('Form submitted.', MIDCOM_LOG_WARN);
            return $this->_processing_result;
        }

        // nothing to do, how nice :)
        debug_add ('Form opened:.', MIDCOM_LOG_WARN);
        $this->_processing_result = MIDCOM_DATAMGR_EDITING;

        return $this->_processing_result;
    }
}
?>