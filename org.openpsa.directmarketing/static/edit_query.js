/**
 * The query editor thingamagick
 * Eero af Heurlin, rambo@iki.fi
 * Henri Bergius, henri.bergius@iki.fi
 */

var org_openpsa_directmarketing_edit_query_handler_store = new Array();

org_openpsa_directmarketing_edit_query_handler = Class.create();
org_openpsa_directmarketing_edit_query_handler.prototype =
{
    initialize: function(rowno, property_map, match_map, gen_localizations)
    {
        this.rowno = rowno;
        this.property_map_full = $H(property_map);
        this.match_map = $H(match_map);
        this.gen_localizations = $H(gen_localizations);
        this.object_select = false;
        this.property_select = false;
        this.param_div = false;
        this.match_select = false;
        this.match_text = false;
        this.fieldset = false;
        this.form_toolbar = $('midcom_helper_datamanager_form_toolbar');
        this.form = $('midcom_helper_datamanager__form');
        this.buttons = false;
        this.cleared = new Array();

    },

    delete_row: function()
    {
        if (this.rowno > 0)
        {
            this.form.removeChild(this.fieldset);
            prev_row = this.rowno - 1;
            $('midcom_helper_datamanager_dummy_field_rowcount').value = prev_row;
            if (org_openpsa_directmarketing_edit_query_handler_store[prev_row])
            {
                org_openpsa_directmarketing_edit_query_handler_store[prev_row].render_addremove_row();
            }
        }
    },

    render_addremove_row: function()
    {
        if (this.buttons)
        {
            /* We already have buttons */
            return;
        }
        div_id = 'org_openpsa_directmarketing_edit_query_handler_row_' + this.rowno + '_buttons';
        html = '<div id="' + div_id + '" class="org_openpsa_directmarketing_edit_query_handler_buttons">';
        if (this.rowno > 0)
        {
            jscall = 'org_openpsa_directmarketing_edit_query_handler_store[' + this.rowno + '].delete_row()';
            /* html += '<input type="button" class="button remove_row" onclick="' + jscall + '" value="&nbsp;" title="' + this.gen_localizations.remove_rule + '" />'; */
            html += '<input type="image" class="button remove_row" onclick="' + jscall + '" value="&nbsp;" title="' + this.gen_localizations.remove_rule + '" src="' + this.gen_localizations.static_url + '/stock-icons/16x16/list-remove.png" />';
        }
        /* html += '<input type="button" class="button add_row" onclick="org_openpsa_directmarketing_edit_query_newrow();" value="&nbsp;" title="' + this.gen_localizations.add_rule + '" />'; */
        html += '<input type="image" class="button add_row" onclick="org_openpsa_directmarketing_edit_query_newrow();" value="&nbsp;" title="' + this.gen_localizations.add_rule + '" src="' + this.gen_localizations.static_url + '/stock-icons/16x16/list-add.png" />';
        html += '</div>';
        /* new Insertion.Bottom(this.fieldset, html); */
        new Insertion.Top(this.fieldset, html);
        this.buttons = $(div_id);
    },

    remove_addremove_row: function()
    {
        if (!this.buttons)
        {
            return;
        }
        this.fieldset.removeChild(this.buttons);
        this.buttons = false;
    },

    create_row: function()
    {
        if (!this.form_toolbar)
        {
            /* TODO: Error message */
            return;
        }
        modulo = this.rowno % 2;
        if (   modulo == 0
            || modulo == 2)
        {
            cssclass = 'even';
        }
        else
        {
            cssclass = 'odd';
        }
        fieldset_id = 'org_openpsa_directmarketing_edit_query_handler_row_' + this.rowno;
        html = '<fieldset id="' + fieldset_id + '" class="row ' + cssclass + '"></fieldset>';
        new Insertion.Before(this.form_toolbar, html);
        this.fieldset = $(fieldset_id);
        if (!this.fieldset)
        {
            /* Could not fetch */
        }
        prev_row = this.rowno - 1;
        if (org_openpsa_directmarketing_edit_query_handler_store[prev_row])
        {
            org_openpsa_directmarketing_edit_query_handler_store[prev_row].remove_addremove_row();
        }
        $('midcom_helper_datamanager_dummy_field_rowcount').value = this.rowno;
        this.render_addremove_row();
        this.render_object_select();
    },

    render_object_select: function()
    {
        if (!this.fieldset)
        {
            /* TODO: Error message */
            return;
        }
        html = '';
        input_id = 'org_openpsa_directmarketing_edit_query_handler_row_' + this.rowno + '_object';
        input_name = 'midcom_helper_datamanager_dummy_field_row[' + this.rowno + '][object]';
        jscall = 'org_openpsa_directmarketing_edit_query_handler_store[' + this.rowno + '].object_select_onchange(this)';
        html += '<select class="select" name="' + input_name + '" id="' + input_id + '" onchange="' + jscall +'">';
        html += '<option value=""></option>';
        this.property_map_full.each(function(optiondata)
        {
            html += '<option value="' + optiondata.key + '">' + optiondata.value.localized + '</option>';
        });
        html += '</select>';
        new Insertion.Bottom(this.fieldset, html);
        this.object_select = $(input_id);
    },

    clear_subinputs: function()
    {
        this.cleared = new Array();
        if (param_div = $('org_openpsa_directmarketing_edit_query_handler_row_' + this.rowno + '_parameters'))
        {
            this.fieldset.removeChild(param_div);
            this.param_div = false;
            this.match_select = false;
            this.match_text = false;
        }
        if (match_text = $('org_openpsa_directmarketing_edit_query_handler_row_' + this.rowno + '_value'))
        {
            this.fieldset.removeChild(match_text);
            this.match_text = false;
        }
        if (match_select = $('org_openpsa_directmarketing_edit_query_handler_row_' + this.rowno + '_match'))
        {
            this.fieldset.removeChild(match_select);
            this.match_select = false;
        }
        if (property_select = $('org_openpsa_directmarketing_edit_query_handler_row_' + this.rowno + '_property'))
        {
            this.fieldset.removeChild(property_select);
            this.property_select = false;
        }
    },

    object_select_onchange: function(select)
    {
        /* Render next inputs based on value */
        this.clear_subinputs();
        selected = $F(select);
        if (!selected)
        {
            /* The selected value is empty, abort creating new subinputs */
            return;
        }
        this.property_map_full.each(function(optiondata)
        {
            if (optiondata.key == selected)
            {
                properties = optiondata.value.properties
                parameters = optiondata.value.parameters
            }
        });
        if (properties)
        {
            this.render_properties_select(properties);
        }
        else if (parameters)
        {
            this.render_parameters_div();
        }
        else
        {
            /* Error with key */
            alert (selected + ' could not be resolved to property or parameter');
        }
    },

    clear_once: function(id)
    {
        element = $(id);
        if (this.cleared[id])
        {
            //alert ('id "' + id + '" is already cleared');
            return;
        }
        if (!element)
        {
            return;
        }
        //alert ('clearing id "' + id + '"');
        element.value = '';
        this.cleared[id] = true;
    },

    render_parameters_div: function()
    {
        div_id = 'org_openpsa_directmarketing_edit_query_handler_row_' + this.rowno + '_parameters';
        html = '<div id="' + div_id + '" style="display: inline">';
        input_id = 'org_openpsa_directmarketing_edit_query_handler_row_' + this.rowno + '_parameter_domain';
        input_name = 'midcom_helper_datamanager_dummy_field_row[' + this.rowno + '][parameter_domain]';
        jscall = 'org_openpsa_directmarketing_edit_query_handler_store[' + this.rowno + '].clear_once(this.id)';
        html += '<input type="text" class="shorttext" name="' + input_name + '" id="' + input_id + '" value="<' + this.gen_localizations.in_domain + '>" onfocus="' + jscall + '" />';
        input_id2 = 'org_openpsa_directmarketing_edit_query_handler_row_' + this.rowno + '_parameter_name';
        input_name2 = 'midcom_helper_datamanager_dummy_field_row[' + this.rowno + '][parameter_name]';
        html += '<input type="text" class="shorttext" name="' + input_name2 + '" id="' + input_id2 + '"  value="<' + this.gen_localizations.with_name + '>" onfocus="' + jscall + '" />';
        html += '</div>';
        new Insertion.After(this.object_select, html);
        this.param_div = $(div_id);
        this.render_match_selectinput($(input_id2));
    },

    render_properties_select: function(properties)
    {
        html = '';
        input_id = 'org_openpsa_directmarketing_edit_query_handler_row_' + this.rowno + '_property';
        input_name = 'midcom_helper_datamanager_dummy_field_row[' + this.rowno + '][property]';
        jscall = 'org_openpsa_directmarketing_edit_query_handler_store[' + this.rowno + '].property_select_onchange(this)';
        html += '<select class="select" name="' + input_name + '" id="' + input_id + '" onchange="' + jscall +'">';
        html += '<option value=""></option>';
        $H(properties).each(function(optiondata)
        {
            html += '<option value="' + optiondata.key + '">' + optiondata.value + '</option>';
        });
        html += '</select>';
        new Insertion.After(this.object_select, html);
        this.property_select = $(input_id);
    },

    property_select_onchange: function(select)
    {
        /* PONDER: some processing maybe ?? */
        if (!this.match_select)
        {
            this.render_match_selectinput(this.property_select);
        }
    },

    render_match_selectinput: function(after)
    {
        html = '';
        input_id = 'org_openpsa_directmarketing_edit_query_handler_row_' + this.rowno + '_match';
        input_name = 'midcom_helper_datamanager_dummy_field_row[' + this.rowno + '][match]';
        html += '<select class="select" name="' + input_name + '" id="' + input_id + '">';
        this.match_map.each(function(optiondata)
        {
            html += '<option value="' + optiondata.key + '">' + optiondata.value + '</option>';
        });
        html += '</select>';
        input_id2 = 'org_openpsa_directmarketing_edit_query_handler_row_' + this.rowno + '_value';
        input_name2 = 'midcom_helper_datamanager_dummy_field_row[' + this.rowno + '][value]';
        html += '<input type="text" class="shorttext" name="' + input_name2 + '" id="' + input_id2 + '">';
        new Insertion.After(after, html);
        this.match_select = $(input_id);
        this.match_text = $(input_id2);
    },

    set_object: function(value)
    {
        this.set_select_value(this.object_select, value);
        this.object_select_onchange(this.object_select);
    },

    set_property: function(value)
    {
        this.set_select_value(this.property_select, value);
        this.property_select_onchange(this.property_select);
    },

    set_match: function(value)
    {
        this.set_select_value(this.match_select, value);
    },

    set_value: function(value)
    {
        this.match_text.value = value;
    },

    set_domain: function(value)
    {
        id = 'org_openpsa_directmarketing_edit_query_handler_row_' + this.rowno + '_parameter_domain';
        this.cleared[id] = true;
        $(id).value = value;
    },

    set_name: function(value)
    {
        id = 'org_openpsa_directmarketing_edit_query_handler_row_' + this.rowno + '_parameter_name';
        this.cleared[id] = true;
        $(id).value = value;
    },

    set_select_value: function(select, value)
    {
        len = select.options.length;
        i=0;
        do
        {
            if (select.options[i].value == value)
            {
                select.selectedIndex = i;
                break;
            }
            i++;
        }
        while (i < len);
    }
}

function org_openpsa_directmarketing_edit_query_newrow()
{
    count = $F('midcom_helper_datamanager_dummy_field_rowcount');
    count++;
    org_openpsa_directmarketing_edit_query_handler_store[count] = new org_openpsa_directmarketing_edit_query_handler(count, org_openpsa_directmarketing_edit_query_property_map, org_openpsa_directmarketing_edit_query_match_map, org_openpsa_directmarketing_edit_query_l10n_map);
    org_openpsa_directmarketing_edit_query_handler_store[count].create_row();
}