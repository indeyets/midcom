<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$dog =& $data['dog'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
echo "<div class='offspring'>\n";
echo "    <a name='offspring'></a><h3>" . $data['l10n']->get('offspring') . "<h3>\n";
$offspring = $dog->get_offspring();
foreach ($offspring as $date => $child_data)
{
    if ($date != 'unknown')
    {
        $date_f = strftime('%x', strtotime($date));
    }
    else
    {
        $date_f = $data['l10n_midcom']->get('unknown');
    }
    foreach ($child_data as $other_parent_id => $children)
    {
        if ($other_parent_id)
        {
            $other_parent_dog = new net_fernmark_pedigree_dog_dba($other_parent_id);
            $other_parent_f = net_fernmark_pedigree_dog_sex_symbol($other_parent_dog) . $other_parent_dog->name_with_kennel;
        }
        else
        {
            $other_parent_f = $data['l10n_midcom']->get('unknown');;
        }
        echo "    <h4 class='date'>{$date_f}: {$other_parent_f}</h4>\n";
        echo "    <ul class='offspring'>\n";
        foreach ($children as $child)
        {
            $link = "{$prefix}dog/{$child->guid}.html";
            echo "        <li class='dog'>\n";
            echo "            <a href='{$link}' target='_BLANK'>" . net_fernmark_pedigree_dog_sex_symbol($child) . "{$child->name_with_kennel}</a>\n";
            if ($child->has_offspring())
            {
                $qb = &$child->get_offsprig_qb();
                $count = $qb->count_unchecked();
                echo "            (<a href='{$link}#offspring' target='_BLANK'>{$count}</a>)\n";
                unset($count, $qb);
            }
            echo "        </li>\n";
        }
    }
    echo "    </ul>\n";
}
echo "</div>\n";
?>