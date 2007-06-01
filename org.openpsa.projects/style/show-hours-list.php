<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="" lang="">
    <head>
        <title>Hour reporting test</title>
        <script type="text/javascript" src="http://devel.openpsa.org/midcom-static/org.openpsa.helpers/messages.js"></script>
        <script type="text/javascript" src="http://devel.openpsa.org/midcom-static/org.openpsa.helpers/ajaxutils.js"></script>
        <link rel="stylesheet" type="text/css" href="http://devel.openpsa.org/midcom-static/midcom.helper.datamanager/datamanager.css" />
        <link rel="stylesheet" type="text/css" href="http://devel.openpsa.org/20/style.css" />
        <style type="text/css">
#org_openpsa_projects_hourlist_table {
    display: none;
    border-collapse: collapse;
    border-spacing: 0;
}
#org_openpsa_projects_hourlist_table tr {
    vertical-align: top;
}
#org_openpsa_projects_hourlist_table tfoot {
    border-top: 1px dashed #c0c0c0;
    color: #333333;
}
#org_openpsa_projects_hourlist_editor {
    /* display: none;*/
}
tr.ajax_editable_row:hover
{
    background-color: #EFE0CD;
}
tr.ajax_editable_row {
}
tr.ajax_editable_row_editing {
    color: #E0C39E;
}
#org_openpsa_projects_hourlist_table td {
    padding: 2px;
}
#org_openpsa_projects_hourlist_table th {
    color: #4B6983;
    text-align: left;
}
#org_openpsa_projects_hourlist_table td.hours, #org_openpsa_projects_hourlist_table th.hours {
    text-align: right;
}
#org_openpsa_projects_hourlist_table td.description {
    width: 150px;
    overflow: hidden;
}
        </style>
        <script type="text/javascript">
/**
 * Set the variables telling ooAjax functions which form to use
 */
var viewName = 'org_openpsa_projects_hourlist';
var focusField = 'hours';

/**
 * Populate the table
 * TODO: Get data via AJAX or generate JS via PHP output?
 */
var existingReports = {
    0: {
        'guid':       'bv2p09v9cvb 230vb02bvcc2',
        'date':       '22.6.',
        'hours':      3,
        'description': 'Lorem ipsum',
    },
    1: {
        'guid':       'npvweihvpqbvpqEVVWE',
        'date':       '24.6.',
        'hours':      1,
        'description': 'Blah blah',
    },
};

/**
 * Create local function for making consistency checks in submitted
 * data
 */
function ooAjaxConsistencyChecks(values)
{
    values['hours'] = Number(values['hours']);
    if (!values['hours'])
    {
        ooDisplayMessage('Please fill in the hours', 'warning');
        return false;
    }
    else
    {
        return values;
    }
}

/** After this everything should be generic **/

/**
 * Global variables used by the application
 */
var formId = viewName+'_editor';
var tableId = viewName+'_table';
var tableDataId = viewName+'_data';

var loadedToEditor = false;
var tableShown = false;

/**
 * We have to call this via BODY onLoad because during the header we can't
 * yet getElementById the table
 */
function populateData()
{
    var table = document.getElementById(tableDataId);
    for (reports in existingReports)
    {
        ooAjaxAddTableRow(table, existingReports[reports], 0);
    }
    var form = document.getElementById(formId);
    form.addEventListener('keydown', ooAjaxConvertEditorToRowKeyhandler, false);
    //form.style.display = 'block';
}
        </script>
    </head>
    <body id="org_openpsa_popup"  onLoad="populateData()">
        <div id="org_openpsa_toolbar">
            <div id="org_openpsa_object_metadata">
                <h1>Hour reporting</h1>
            </div>
        </div>
        <div id="org_openpsa_content">
            <div id="org_openpsa_messagearea">
            </div>
        <div class="main wide">
            <div class="area">
            <table id="org_openpsa_projects_hourlist_table">
                <thead>
                    <th style="display: none;">GUID</th>
                    <th>Date</th>
                    <th class="hours">Hrs</th>
                    <th>Description</th>
                </thead>
                <tbody id="org_openpsa_projects_hourlist_data">
                </tbody>
                <tfoot>
                    <td style="display: none;"></td>
                    <td></td>
                    <td class="hours"></td>
                    <td></td>
                </tfoot>
            </table>
            <form id="org_openpsa_projects_hourlist_editor" class="datamanager">
                <input type="hidden" name="guid" value="" />
                <label for="org_openpsa_projects_houreditor_date">
                    Date
                    <input type="text" name="date" id="org_openpsa_projects_houreditor_date" />
                </label>
                <label for="org_openpsa_projects_houreditor_hours">
                    Hours
                    <input type="text" name="hours" id="org_openpsa_projects_houreditor_hours" />
                </label>
                <label for="org_openpsa_projects_houreditor_description">
                    Description
                    <textarea name="description" id="org_openpsa_projects_houreditor_description"></textarea>
                </label>
                <input type="button" id="org_openpsa_projects_houreditor_save" onClick="javascript:ooAjaxConvertEditorToRow();" value="Save" />
            </form>
            </div>
        </div>
    </body>
</html>