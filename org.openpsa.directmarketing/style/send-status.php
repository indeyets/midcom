<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
$msg_guid = $data['message']->guid;
$ajax_url = "{$node[MIDCOM_NAV_FULLURL]}/message/send_status/{$msg_guid}/";
?>
<div id="org_openpsa_directmarketing_send_uimessages">
    <?php echo $data['l10n']->get('messages sent'); ?>: <div id="org_openpsa_directmarketing_send_uimessages_sent" style="display: inline">??</div> / <div id="org_openpsa_directmarketing_send_uimessages_total" style="display: inline">??</div>
</div>
<script type="text/javascript">
function org_openpsa_directmarketing_get_send_status()
{
    div = document.getElementById('org_openpsa_directmarketing_send_uimessages');
    ooAjaxGet('&(ajax_url);', false, div, 'org_openpsa_directmarketing_set_send_status', false);
}

function org_openpsa_directmarketing_set_send_status(resultList, element)
{
    sent_div = document.getElementById(element.id + '_sent');
    total_div = document.getElementById(element.id + '_total');

    results = resultList.getElementsByTagName('result');
    if (   !results
        || results.lenght == 0)
    {
        //No results, do something
        return false;
    }
    result = response.getElementsByTagName('result')[0].firstChild.data;
    if (result != 1)
    {
        //Error from server, do something
        return false;
    }
    sent_results = resultList.getElementsByTagName('receipts');
    if (   !sent_results
        || sent_results.lenght == 0)
    {
        //No results, do something
        return false;
    }
    total_results = resultList.getElementsByTagName('members');
    if (   !total_results
        || total_results.lenght == 0)
    {
        //No results, do something
        return false;
    }

    ooRemoveChildNodes(sent_div);
    ooRemoveChildNodes(total_div);
    sent_div.appendChild(document.createTextNode(sent_results[0].firstChild.data));
    total_div.appendChild(document.createTextNode(total_results[0].firstChild.data));
    return true;
}

org_openpsa_directmarketing_get_send_status();
</script>