<div id="aislocation">
  <a href="#" onClick="javascript:handleAisNavigationDisplay(this);" > <img style="cursor: pointer" src="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/stock_right.png" alt="<?php echo $GLOBALS["view_l10n"]->get("display navigation"); ?>" /> <?php

echo "Navigation</a><br/>";
$nav = new midcom_helper_nav($GLOBALS["view_contentmgr"]->viewdata["context"]);
$prefix = $GLOBALS["view_contentmgr"]->viewdata["adminprefix"];
$separator = " &gt; ";

$curr_leaf = $nav->get_current_leaf();
$curr_node = $nav->get_current_node();
        
$node = $nav->get_node($curr_node);

if ($curr_leaf !== false) {
    $leaf = $nav->get_leaf($curr_leaf);
    $result = htmlspecialchars($leaf[MIDCOM_NAV_NAME]);
    $result = "<a href=\"" . $prefix . $node[MIDCOM_NAV_ID] . "/data/\">"
      . htmlspecialchars($node[MIDCOM_NAV_NAME])
      . "</a>" . $separator . $result;
} else {
    $result = htmlspecialchars($node[MIDCOM_NAV_NAME]);
}





while ( ($curr_node = $nav->get_node_uplink($curr_node)) != -1)
{
    if (!$curr_node)
    {
        break;
    }
    $node = $nav->get_node($curr_node);
    $result = "<a href=\"" . $prefix . $node[MIDCOM_NAV_ID] . "/data/\">"
      . htmlspecialchars($node[MIDCOM_NAV_NAME])
      . "</a>" . $separator . $result;
}

echo $result; 

?>

</div>
