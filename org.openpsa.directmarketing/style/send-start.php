<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
if (!$view_data['message_obj']->test_mode)
{
    midcom_show_style('send-status');
?>
<script language="javascript">
repeater = setInterval('org_openpsa_directmarketing_get_send_status()', 10000);
</script>
<?php
}
else
{
//TODO: Send test mode (or different style ??)
}
?>