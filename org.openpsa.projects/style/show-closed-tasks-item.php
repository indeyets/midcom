<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
    <dt>
        <a class="closed" href="<?php echo $node[MIDCOM_NAV_FULLURL]; ?>task/<?php echo $data['task']->guid; ?>/"><?php echo $data['task']->title; ?></a>
        <?php
        if ($data['task']->up)
        {
            $parent = $data['task']->get_parent();
            if ($parent->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_PROJECT)
            {
                $parent_url = "{$node[MIDCOM_NAV_FULLURL]}project/{$parent->guid}/";
            }
            else
            {
                $parent_url = "{$node[MIDCOM_NAV_FULLURL]}task/{$parent->guid}/";
            }
            echo " <span class=\"metadata\">(<a href=\"{$parent_url}\">{$parent->title}</a>)</span>";
        }
        ?>
    </dt>
    <dd>
    </dd>