<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $view_data['campaign_array'];
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
if (array_key_exists('membership', $view_data))
{
    switch ($view_data['membership']->orgOpenpsaObtype)
    {
        case ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_UNSUBSCRIBED:
            $class = 'unsubscribed';
            break;
        //This is unneccessary for now as we filter testers out earlier but in the future it might be needed
        case ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER:
            $class = 'tester';
            break;
        default:
            $class = 'member';
            break;
    }
}
else
{
    $class = 'campaign';
}
echo "<dt class=\"{$class}\"><a href=\"{$node[MIDCOM_NAV_FULLURL]}campaign/{$view_data['campaign']->guid}/\">{$view['title']}</a></dt>\n";
?>
