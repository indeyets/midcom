/**
 * renders project prospects list
 * Henri Bergius, henri.bergius@iki.fi
 *
 */
var project_prospects_renderer = Class.create();

project_prospects_renderer.prototype = {

    initialize: function(container, base_url, task_guid)
    {
        this.element = $(container);
        this.base_url = base_url;
        this.task_guid = task_guid;
        this.seen_prospects = new Array();
        this.ajax_request = false;
        this.ahah_requests = new Array();
    },

    get_prospect_list: function()
    {
        url = this.base_url + 'task/resourcing/prospects/' + this.task_guid + '.html';
        Element.removeClassName(this.element, 'project_prospects_renderer_search_ok');
        Element.removeClassName(this.element, 'project_prospects_renderer_search_fail');
        /* Set the class which should give us a "loading" icon */
        Element.addClassName(this.element, 'project_prospects_renderer_searching');
        this.ajax_request = new Ajax.Request(url, {
            method: 'get',
            onSuccess: this.ajax_success.bind(this),
            onFailure: this.ajax_failure.bind(this),
            onException: this.ajax_exception.bind(this),
        });
    },

    ajax_success: function(request)
    {
        //alert('ajax_success called');
        Element.removeClassName(this.element, 'project_prospects_renderer_searching');
        Element.addClassName(this.element, 'project_prospects_renderer_search_ok');
        /* Display lines in result */
        results = request.responseXML.getElementsByTagName('person');
        if (results.length < 1)
        {
            return;
        }
        for (var i=0; i < results.length; i++)
        {
            line = results[i];
            /* TODO: Make more robust */
            person = line.getElementsByTagName('guid')[0].firstChild.data;
            prospect = line.getElementsByTagName('prospect')[0].firstChild.data;
            label = line.getElementsByTagName('label')[0].firstChild.data;
            this.add_result(prospect, label);
        }
    },

    add_result: function(prospect, label)
    {
        if (this.seen_prospects[prospect])
        {
            return;
        }
        url = this.base_url + 'task/resourcing/prospect/' + prospect + '.html';
        new Insertion.Bottom(this.element, '<li id="prospect_' + prospect + '" class="project_prospects_renderer_searching">' + label + '</li>');
        /* new Insertion.Bottom(this.element, '<li id="prospect_' + prospect + '" class="project_prospects_renderer_searching" style="display: none;">' + label + '</li>'); */
        /* todo: use blinddown, etc to make the ui less jumpy */
        this.ahah_requests[prospect] = new Ajax.Updater(
            'prospect_' + prospect,
            url,
            {
                method    : 'get'
            });
        prospect_element = $('prospect_' + prospect);
        Element.removeClassName(prospect_element, 'project_prospects_renderer_searching');
        Element.addClassName(prospect_element, 'project_prospects_renderer_search_ok');
    },

    ajax_failure: function(request)
    {
        /* This is called on xmlHttpRequest level failure,
           MidCOM level errors are reported via the XML returned */
        Element.removeClassName(this.element, 'project_prospects_renderer_searching');
        Element.addClassName(this.element, 'project_prospects_renderer_search_fail');
        new protoGrowl({type: 'error', title: 'Project prospects', message: 'Ajax request level failure'})
        /* TODO: Some kind of error handling ?? */
        return true;
    },

    ajax_exception: function(request, exception)
    {
        /* This is called on xmlHttpRequest level exception */
        /* TODO: Some kind of exception handling ? */
        //alert('ajax_exception called');
        new protoGrowl({type: 'error', title: 'Project prospects', message: exception});
        return this.ajax_failure(request);
    },

    ajax_checkerror: function(request)
    {
        statuses = request.responseXML.getElementsByTagName('status');
        if (   statuses.length < 1
            || !statuses[0])
        {
            new protoGrowl({type: 'error', title: 'Project prospects', message: 'Status tag not found'})
            return true;
        }
        messages = request.responseXML.getElementsByTagName('errstr');
        status_value = statuses[0].firstChild.data
        message_str = '';

        if (   messages.length > 0
            && messages[0]
            && messages[0].firstChild)
        {
            message_str = messages[0].firstChild.data;
        }
        if (message_str)
        {
            new protoGrowl({type: 'error', title: 'Project prospects', message: message_str});
        }
        if (status_value > 0)
        {
            /* No error, so we return false */
            return false;
        }
        /* Default to returning true (yes, there was an error) */
        return true;
    }
};


function project_prospects_slot_changed(id)
{
    slot_cb = $(id + '_checkbox');
    if (slot_cb.checked)
    {
        project_prospects_choose_slot(id);
    }
    else
    {
        project_prospects_unchoose_slot(id);
    }
}

function project_prospects_choose_slot(id)
{
    /* alert('project_prospects_choose_slot called:' + id); */
    /* todo: do something more useful */
    slot_container = $(id);
    Element.addClassName(slot_container, 'selected');
}

function project_prospects_unchoose_slot(id)
{
    /* alert('project_prospects_unchoose_slot called:' + id); */
    /* todo: do something more useful */
    slot_container = $(id);
    Element.removeClassName(slot_container, 'selected');
}