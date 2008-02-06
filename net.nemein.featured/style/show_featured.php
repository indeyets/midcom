<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$featured_objects =& $data['featured_objects'];
$featured_groups =& $data['featured_groups'];

foreach ($featured_groups as $key => $group)
{
    $title = $group['title'];
    $groups_objects =& $featured_objects[$key];
    ?>
    <h1>&(title);</h1>

    <?php
    if (!empty($groups_objects))
    {
        foreach ($groups_objects as $key => $item)
        {
            $item->load_featured_item();
        }
    }
    else
    {
        ?>
        <p><?php $data['l10n']->show('no items found.');?></p>
        <?php
    }
}
?>