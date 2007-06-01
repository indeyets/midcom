<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
@include_once('HTML/TreeMenu.php');
if (class_exists('HTML_TreeMenu'))
{
    ?>
    <div class="area">
        <h2><?php echo $data['l10n']->get('folders'); ?></h2>
    <?php
    function org_openpsa_documents_build_treemenu($node_id, &$nap, $nodes_array)
    {
        $node = $nap->get_node($node_id);

        if ($node[MIDCOM_NAV_COMPONENT] != "org.openpsa.documents")
        {
            return false;
        }

        $expanded = false;
        if (array_key_exists($node_id, $nodes_array))
        {
            $expanded = true;
        }

        $node_menu = new HTML_TreeNode(
            Array(
                'text' => $node[MIDCOM_NAV_NAME],
                'link' => $node[MIDCOM_NAV_FULLURL],
                'icon' => 'folder.png',
                'expandedIcon' => 'folder-expanded.png',
                'expanded' => $expanded,
                'cssClass' => 'treemenu',
            )
        );

        $subnodes = $nap->list_nodes($node_id);
        if ($subnodes)
        {
            foreach ($subnodes as $subnode_id)
            {
                $subnode = $nap->get_node($subnode_id);
                $subnode_menu_[$subnode_id] = org_openpsa_documents_build_treemenu($subnode_id, &$nap, $nodes_array);
                if ($subnode_menu_[$subnode_id])
                {
                    $node_menu->additem($subnode_menu_[$subnode_id]);
                }
            }
        }
        return $node_menu;
    }


    $nap = new midcom_helper_nav();
    $menu  = new HTML_TreeMenu();

    // Get current node
    $current_node = $nap->get_current_node();
    if ($current_node)
    {
        $node = $nap->get_node($current_node);

        $nodes = Array();
        $nodes[$current_node] = $node;

        // Read until MidCOM root topic
        while ($node[MIDCOM_NAV_ID] != $nap->get_root_node())
        {
            $uplink = $nap->get_node_uplink($node[MIDCOM_NAV_ID]);
            if (!$uplink)
            {
                break;
            }
            $node = $nap->get_node($uplink);
            $nodes[$node[MIDCOM_NAV_ID]] = true;
        }
    }

    // List Documents nodes
    $toplevel_nodes = $nap->list_nodes($nap->get_root_node());
    if ($toplevel_nodes)
    {
        foreach ($toplevel_nodes as $toplevel_node_id)
        {
            $toplevel_node = $nap->get_node($toplevel_node_id);
            $toplevel_node_menu_[$toplevel_node_id] = org_openpsa_documents_build_treemenu($toplevel_node_id, &$nap, $nodes);
            if ($toplevel_node_menu_[$toplevel_node_id])
            {
                $menu->additem($toplevel_node_menu_[$toplevel_node_id]);
            }
        }
    }

//    $menu->addItem(org_openpsa_documents_build_treemenu($nap->get_root_node(), &$nap));

    // Chose a generator. You can generate DHTML or a Listbox
    $tree = new HTML_TreeMenu_DHTML($menu,
        Array(
            'images' => MIDCOM_STATIC_URL.'/stock-icons/16x16/'
        )
    );
    echo $tree->toHTML();
}
else
{
    // No HTML_TreeMenu installed, fall back to simple navi
    $nap = new midcom_helper_nav();

    // Configure the simple navigation
    $nav_config = array();

    // FIXME: This shouldn't be required
    $nav_config['indent_size'] = '15';
    $nav_config['indent_linewrap'] = '5';

    // Start from first Documents topic
    $node = midcom_helper_find_node_by_component('org.openpsa.documents', $nap->get_root_node(), $nap);
    if ($node)
    {
        $nav_config['node'] = $node;
    }
    $nap->show_simple_nav($nav_config);
}
?>
</div>