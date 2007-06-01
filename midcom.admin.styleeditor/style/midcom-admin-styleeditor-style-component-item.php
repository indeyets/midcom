<?php
$node = $data['nap']->get_node($data['topic']->id);
?>
        <li><a href="<?php echo $node[MIDCOM_NAV_FULLURL]; ?>"><?php echo $data['topic']->extra; ?></a></li>
