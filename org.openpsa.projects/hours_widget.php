<?php
/**
 * @package org.openpsa.projects
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: hours_widget.php,v 1.9 2006/05/12 16:49:50 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.projects hour report editor widget
 *
 * @package org.openpsa.projects
 */
class org_openpsa_projects_hours_widget
{
    var $_datamanager;
    var $_hours_url;
    var $_l10n;
    var $_columns = array();
    var $_hour_reports = array();
    var $_request_data;
    var $_form_prefix = '';

    function org_openpsa_projects_hours_widget(&$task, &$datamanager, $hours_url, &$request_data)
    {
        $this->_datamanager = $datamanager;
        $this->_hours_url = $hours_url;
        $this->_request_data = &$request_data;

        if ($task)
        {
            $this->_request_data['task'] = &$task;
            $this->_form_prefix = "midcom_helper_datamanager_{$this->_request_data['task']->guid}_";
            $this->_datamanager->form_prefix = $this->_form_prefix;
        }

        // Add task resources to the schema
        $resources = Array();
        if ($_MIDGARD['user'] == $this->_request_data['task']->manager)
        {
            // Manager can add multiple people
            foreach ($this->_request_data['task']->resources as $person_id => $enabled)
            {
                $person = new midcom_baseclasses_database_person($person_id);
                $resources[$person->id] = $person->name;
            }
        }
        else
        {
            // User can only add himself
            $user = $_MIDCOM->auth->user->get_storage();
            $resources[$_MIDGARD['user']] = $user->name;
        }
        org_openpsa_helpers_schema_modifier(&$this->_datamanager, 'person', 'widget_select_choices', $resources, 'default', false);

        // Create the JavaScript-populated table
        $this->_get_columns();
        $this->add_headers();
    }

    function add_hour_reports()
    {
        // This is a stub we may later use for populating the initial hour reports inside the document instead
        // of via AJAX GET request
    }

    function _get_columns()
    {
        foreach ($this->_datamanager->_layoutdb['default']['fields'] as $name => $field)
        {
            // Normalize the field definitions here
            if (!array_key_exists('hidden', $field))
            {
                $field['hidden'] = false;
            }
            if (!array_key_exists('description', $field))
            {
                $field['description'] = '';
            }
            $field['description'] = $this->_request_data['l10n']->get($field['description']);
            $this->_columns[$this->_form_prefix.'field_'.$name] = $field;
        }
    }

    function add_headers()
    {

        if (array_key_exists('org_openpsa_projects_hours_widget_headers_sent_'.$this->_request_data['task']->guid, $GLOBALS))
        {
            return false;
        }

        // This component uses Ajax, include the handler javascripts
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/ajaxutils.js");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/ajax_tableform.js");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.projects/hours_widget.js");

        // This is where we make all the settings for the widget
        // Rest of the JS code is in org.openpsa.helpers
        // TODO: Add the midgard:create check, populate to ooAjaxTableFormHandler.allowCreate
        $javascript = "
            /**
             * Instantiate the Ajax Form handler
             */
            var ooAjaxTableFormHandler_{$this->_request_data['task']->guid} = new ooAjaxTableFormHandler('hourlist_{$this->_request_data['task']->guid}', new String('{$this->_form_prefix}field_'));
            ooAjaxTableFormHandler_{$this->_request_data['task']->guid}.instanceName = 'ooAjaxTableFormHandler_{$this->_request_data['task']->guid}';
            ooAjaxTableFormHandler_{$this->_request_data['task']->guid}.focusField = ooAjaxTableFormHandler_{$this->_request_data['task']->guid}.fieldPrefix+'hours';
            ooAjaxTableFormHandler_{$this->_request_data['task']->guid}.URL = '{$this->_hours_url}';
            ooAjaxTableFormHandler_{$this->_request_data['task']->guid}.ajaxResultElement = 'report';
            ooAjaxTableFormHandler_{$this->_request_data['task']->guid}.evenColor = '#EAE8E3';
            ooAjaxTableFormHandler_{$this->_request_data['task']->guid}.convertRowToEditorEventhandler = function(event)
            {
                ooAjaxTableFormHandler_{$this->_request_data['task']->guid}.convertRowToEditor(event);
            }
            ooAjaxTableFormHandler_{$this->_request_data['task']->guid}.convertEditorToRowKeyhandler = function(event)
            {
                var pressedKey;
                if (!event)
                {
                    event = window.event;
                }
                pressedKey = event['keyCode'];
                /* <enter> saving disabled
                if (pressedKey == 13)
                {
                    // Enter pressed, convert to save button press
                    ooAjaxTableFormHandler_{$this->_request_data['task']->guid}.convertEditorToRow();
                }
                */
            }

            /**
             * Create local function for making consistency checks in submitted
             * data
             */
            ooAjaxTableFormHandler_{$this->_request_data['task']->guid}.consistencyChecks = function(values)
            {
                //Convert value to string, we do some magic with it
                valueStr = String(values[ooAjaxTableFormHandler_{$this->_request_data['task']->guid}.fieldPrefix+'hours']);
                //Convert commas to dots (some locales use comma as decimal separator)
                valueStr = valueStr.replace(/,/gi, '.');
                //Convert hours:minutes to decimal
                tmpArr = valueStr.split(':');
                if (tmpArr.length==2)
                {
                    valueStr = String(Number(tmpArr[0]) + (Number(tmpArr[1]) / 60));
                }
                //Round value to two digits
                values[ooAjaxTableFormHandler_{$this->_request_data['task']->guid}.fieldPrefix+'hours'] = Math.round(Number(valueStr)*100)/100;
                if (!values[ooAjaxTableFormHandler_{$this->_request_data['task']->guid}.fieldPrefix+'hours'])
                {
                    ooDisplayMessage('fill in the hours', 'warning');
                    return false;
                }
                else
                {
                    return values;
                }
            }
        ";

        if (   $this->_request_data['task']
            && $_MIDCOM->auth->can_do('midgard:create', $this->_request_data['task']))
        {
            $javascript .= "ooAjaxTableFormHandler_{$this->_request_data['task']->guid}.allowCreate = true;\n";
            org_openpsa_helpers_schema_modifier(&$this->_datamanager, 'invoiceable', 'default', $this->_request_data['task']->hoursInvoiceableDefault, 'default', false);
        }

        $hidden_fields = 0;
        foreach ($this->_columns as $key => $field)
        {

            if ($field['hidden'] == true)
            {
                $javascript .= "ooAjaxTableFormHandler_{$this->_request_data['task']->guid}.hiddenFields[{$hidden_fields}]='{$key}';\n";
            }
            $hidden_fields++;
        }

        if ($_MIDCOM->get_current_context() == 0)
        {
            // We're in main request handler phase, add things to headers
            $_MIDCOM->add_jscript($javascript);

            // Data must be populated when <body /> has been loaded
            $_MIDCOM->add_jsonload("ooAjaxTableFormHandler_{$this->_request_data['task']->guid}.populateData();");
        }
        else
        {
            // This is dynamic load, just echo the javascript
            echo "<script type=\"text/javascript\">{$javascript}</script>\n";
        }

        // Prepare datamanager for the task at hand
        $dummy_hours = new org_openpsa_projects_hour_report();
        $dummy_hours->task = $this->_request_data['task']->id;
        $dummy_hours->date = time();
        $this->_datamanager->init($dummy_hours);
        //$this->_datamanager->_creation = true;
        $this->_datamanager->process_form();

        // Make the hour reports pretty
        $_MIDCOM->add_link_head(array(
            'rel' => 'stylesheet',
            'type' => 'text/css',
            'href' => MIDCOM_STATIC_URL."/org.openpsa.projects/hours_widget.css",
        ));

        $GLOBALS['org_openpsa_projects_hours_widget_headers_sent_'.$this->_request_data['task']->guid] = true;
    }

    function show($visible = true)
    {

        if (!$visible)
        {
            $visibility = ' style="display: none;"';
        }
        else
        {
            $visibility = '';
        }

        echo '
        <div class="org_openpsa_projects_hourlist" id="hourlist_'.$this->_request_data['task']->guid.'"'.$visibility.'>
            <table class="org_openpsa_projects_hourlist_table" id="hourlist_'.$this->_request_data['task']->guid.'_table">
                <thead>
        ';
        foreach ($this->_columns as $key => $field)
        {
            $style = '';
            if ($field['hidden'] == true)
            {
                $style = ' style="display: none;"';
            }
            echo "<th class=\"{$key}\"{$style}>{$field['description']}</th>\n";
        }
        echo '
                </thead>
                <tbody id="hourlist_'.$this->_request_data['task']->guid.'_data">
                </tbody>
                <tfoot>
        ';
        foreach ($this->_columns as $key => $field)
        {
            $style = '';
            if (   array_key_exists('hidden', $field)
                && $field['hidden'] == true)
            {
                $style = ' style="display: none;"';
            }
            if (!array_key_exists('description', $field))
            {
                $field['description'] = '';
            }
            echo "<td class=\"{$key}\"{$style}></td>\n";
        }
        echo '
                </tfoot>
            </table>
        ';
        $this->_datamanager->display_form('hourlist_'.$this->_request_data['task']->guid.'_editor',true,true);
        echo '
            <div class="form_toolbar">
                <input type="button" class="org_openpsa_projects_savebutton" id="hourlist_'.$this->_request_data['task']->guid.'_savebutton" onclick="javascript:ooAjaxTableFormHandler_'.$this->_request_data['task']->guid.'.convertEditorToRow();" value="'.$this->_request_data['l10n_midcom']->get('save').'" />
            </div>
        </div>
        ';
    }
}
?>