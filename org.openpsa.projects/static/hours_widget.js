
var ooHourWidgetDisplayed = Array();

function ooToggleHourWidgetDisplay(guid)
{
    if (ooHourWidgetDisplayed[guid] != true)
    {
        document.getElementById('hourlist_'+guid).style.display='block';
        ooHourWidgetDisplayed[guid] = true;
        eval('ooAjaxTableFormHandler_'+guid+'.populateData();');
    }
    else
    {
        document.getElementById('hourlist_'+guid).style.display='none';
        ooHourWidgetDisplayed[guid] = false;
    }
}