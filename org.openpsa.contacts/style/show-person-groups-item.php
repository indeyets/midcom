<?php
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$view_group = $view_data['group'];
$group_guid = $view_data['group']->guid;

if ($_MIDCOM->auth->can_do('midgard:update', $view_data['member']))
{
    $view_title_form = "<input id=\"editable_title_{$group_guid}_ajaxDefault\" value=\"".$view_data['l10n']->get('<title>')."\" type=\"hidden\" />\n";
    $view_title_form .= "<input id=\"editable_title_{$group_guid}_ajaxUrl\" value=\"{$node[MIDCOM_NAV_FULLURL]}group/{$group_guid}/update_member_title/\" type=\"hidden\" />\n";
    $view_title_form .= "<input id=\"editable_title_{$group_guid}\" name=\"member_title[{$view_data['member']->id}]\" class=\"ajax_editable\" style=\"width: 80%;\" onFocus=\"ooAjaxFocus(this)\" onBlur=\"ooAjaxBlur(this)\" value=\"{$view_data['member_title']}\" />\n";
}
else
{
    $view_title_form = $view_data['member_title'];
}

$view_group_name = $view_group->official;
if ($view_group_name == '')
{
    $view_group_name = $view_group->name;
}
?>
<div class="vcard">
    <div class="organization-name">
        <a href="&(node[MIDCOM_NAV_FULLURL]);group/&(view_group.guid);/">&(view_group_name);</a>
    </div>
    <ul>
        <?php
        echo "<li class=\"title\">{$view_title_form}</li>\n";
        if ($view_group->phone)
        {
            echo "<li class=\"tel work\">{$view_group->phone}</li>\n";
        }

        if ($view_group->postalStreet)
        {
            echo "<li>{$view_group->postalStreet}, {$view_group->postalCity}</li>\n";
        }
        elseif ($view_group->street)
        {
            echo "<li>{$view_group->street}, {$view_group->city}</li>\n";
        }
        
        if ($view_group->homepage)
        {
            echo "<li class=\"url\"><a href=\"{$view_group->homepage}\">{$view_group->homepage}</a></li>\n";
        }        
        ?>
    </ul>
</div>