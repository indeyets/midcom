<?php
// $data =& $_MIDCOM->get_custom_context_data('request_data');

if ($data['group']->official)
{
    $title = $data['group']->official;
}
else
{
    $title = $data['group']->name;
}
?>
        <li class="group" id="net_nemein_personnel_group_<?php echo $data['index']; ?>">
            <h3 onDblClick="javascript:alter_title(this);" class="handle">
                <input type="hidden" name="sortable[]" value="group::group_<?php echo $data['group']->guid; ?>::&(title:h);" />
                <span title="<?php echo $data['l10n']->get('double click to edit').' '.$data['l10n']->get('drag and drop to sort'); ?>">&(title:h);</span>
            </h3>
            <ul id="net_nemein_personnel_group_list_<?php echo $data['index']; ?>" class="section">
