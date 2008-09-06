<div id="midcom_admin_folder_order_type_&(data['navigation_type']);">
    <ul id="midcom_admin_folder_order_type_list_&(data['navigation_type']);" class="sortable &(data['navigation_type']);">
<?php
$count = count($data['navigation_items']);

foreach ($data['navigation_items'] as $i => $item)
{
    if ($item[MIDCOM_NAV_GUID])
    {
        $identificator = $item[MIDCOM_NAV_GUID];
    }
    else
    {
        $identificator = $item[MIDCOM_NAV_ID];
    }
    
    $index = $count - $i;
    $style = '';
    
    if (isset($_GET['ajax']))
    {
        $style = ' style="display: none;"';
    }
    
    echo "        <li class=\"sortable {$item[MIDCOM_NAV_TYPE]}\">\n";
    echo "            <input type=\"text\" name=\"sortable[{$item[MIDCOM_NAV_TYPE]}][{$identificator}]\" value=\"{$index}\"{$style} />\n";
    echo "            {$item[MIDCOM_NAV_NAME]}\n";
    echo "        <li>\n";
}
?>
    </ul>
</div>
<script type="text/javascript">
    // <!--
        jQuery('#midcom_admin_folder_order_type_list_&(data['navigation_type']);')
            .sortable({
                containment: '#midcom_admin_folder_order_type_list_&(data['navigation_type']);'
            });
    // -->
</script>
