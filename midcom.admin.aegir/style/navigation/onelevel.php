<?php 
/*
 * var $class_loaded = boolean.
 * Use this to indicate that we are missing some component.
 * */
$class_loaded = true;
class midcom_admin_content_navigation_onelevel {


    /**
     * pointer to midcom object
     * @var object
     * @access private
     * */
    var $_midcom;

    /**
     * pointer to 
     * view_contentmgr object
     * @var object
     * @access private
     * */
    var $view_contentmgr;
    
    function midcom_admin_content_navigation_onelevel () {
      global $view_contentmgr;
      $this->_midcom = $_MIDCOM;
      $this->_view_contentmgr = $view_contentmgr;
      }

    /**
     *  Output the html of the class.
     *  NOTE: As of now, this will _not_ return a string but 
     *  echo the stuff.
     */
    function to_html() {
        $data =& $this->_view_contentmgr->viewdata;

        $context = $data["context"];
        $nav = new midcom_helper_nav($data["context"]);
        $length = $this->_view_contentmgr->config->get("nav_length");
        $ellipsis = $this->_view_contentmgr->config->get("nav_ellipsis");
        if ($ellipsis === false) {
            $ellipsis = "";
        }
        $datamode = $data["adminmode"] == "data" ? true : false;
    
        $prefix = $this->_midcom->get_context_data($context,MIDCOM_CONTEXT_ANCHORPREFIX);

        $curr_node = $nav->get_current_node();
        $curr_leaf = $nav->get_current_leaf();

        // Display the current node (link if we have a leaf selected)

        $node = $nav->get_node($curr_node);
        if ($length > 0 && strlen($node[MIDCOM_NAV_NAME]) > $length) {
            $node[MIDCOM_NAV_NAME] = substr($node[MIDCOM_NAV_NAME],0,$length);
            $node[MIDCOM_NAV_NAME] .= $ellipsis;
        }
        if ($curr_leaf === false && $datamode) {
            ?><div class="contentadm_nav_curnode_active"><IMG SRC="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/folder.png" width="16" height="16" ALT="">
            <?php echo $node[MIDCOM_NAV_NAME];?></div><?php
        } else { 
            ?><div class="contentadm_nav_curnode_inactive"><a href="<?php echo $data["admintopicprefix"] . "data/"; ?>">
            <IMG SRC="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/folder.png" width="16" height="16" ALT="">
            <?php echo $node[MIDCOM_NAV_NAME];?></a></div><?php                        
        }
        echo "\n";

        // Display the subnodes

        $subnodes = $nav->list_nodes($curr_node, true);
        if (is_array($subnodes) && count($subnodes) > 0) {
            ?><div class="contentadm_nav_subnodes"><?php
            foreach ($subnodes as $subnodeid) {
                $subnode = $nav->get_node($subnodeid);
                if ($length > 0 && strlen($subnode[MIDCOM_NAV_NAME]) > $length) {
                    $subnode[MIDCOM_NAV_NAME] = substr($subnode[MIDCOM_NAV_NAME],0,$length);
                    $subnode[MIDCOM_NAV_NAME] .= $ellipsis;
                }
                ?><div class="contentadm_nav_subnode"><a href="<?php echo $data["adminprefix"] . $subnodeid . "/data/" ; ?>">
                <IMG SRC="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/folder.png" width="16" height="16" ALT="">
                <?php echo $subnode[MIDCOM_NAV_NAME];?></a></div><?php
            }
            echo "</div>\n";
        }

        // Display the leaves

        $leaves = $nav->list_leaves($curr_node, true);
        if (is_array($leaves) && count($leaves) > 0) {
            ?><div class="contentadm_nav_leaves"><?php
            foreach ($leaves as $leafid) {
                $leaf = $nav->get_leaf($leafid);
                if ($length > 0 && strlen($leaf[MIDCOM_NAV_NAME]) > $length) {
                    $leaf[MIDCOM_NAV_NAME] = substr($leaf[MIDCOM_NAV_NAME],0,$length);
                    $leaf[MIDCOM_NAV_NAME] .= $ellipsis;
                }
                if ($curr_leaf !== false && $curr_leaf == $leafid && $datamode) {
                    ?><div class="contentadm_nav_leaf_active"><IMG SRC="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/new-html.png" width="16" height="16" ALT="">
                    <?php echo $leaf[MIDCOM_NAV_NAME]; ?></div><?php
                } else {
                    ?><div class="contentadm_nav_leaf_inactive">
                    <a href="<?php echo $data["admintopicprefix"] . "data/" . $leaf[MIDCOM_NAV_URL]; ?>">
                    <IMG SRC="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/new-html.png" width="16" height="16" ALT="">
                    <?php echo $leaf[MIDCOM_NAV_NAME]; ?></a></div>
                    <?php
                }
            }
            echo "</div>\n";
        }

        // Display Uplink (unless we are at root node)

        if ($curr_node != $nav->get_root_node())
        {
            $nodeid = $nav->get_node_uplink($curr_node);
            $node = $nav->get_node($nodeid);
            if ($length > 0 && strlen($node[MIDCOM_NAV_NAME]) > $length) {
                $node[MIDCOM_NAV_NAME] = substr($node[MIDCOM_NAV_NAME],0,$length);
                $node[MIDCOM_NAV_NAME] .= $ellipsis;
            }
            ?><div class="contentadm_nav_uplink"><a href="<?php echo $data["adminprefix"] . $nodeid . "/data/"?>" ><IMG SRC="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/up-one-dir.png" width="16" height="16" ALT="">
            <?php  echo $node[MIDCOM_NAV_NAME];?> </a></div><?php        
        }
    }
}
    ?>
