<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $view_data['controller']->datamanager;
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<div class="main">
    <?php 
    // Display sales project metadata
    $view->display_view();
    echo "<div style=\"clear: both;\"></div>\n";
    
    $products = org_openpsa_products_product_dba::list_products();
    if (count($products) > 0)
    {
        ?>
        <div class="salesproject_deliverables">
            <h2><?php echo $view_data['l10n']->get('deliverables'); ?></h2>
            <?php
            if ($view_data['salesproject']->can_do('midgard:create'))
            {
                ?>
                <form method="post" action="&(node[MIDCOM_NAV_FULLURL]);deliverable/add/<?php echo $view_data['salesproject']->guid; ?>">
                    <label>
                        <?php
                        echo $view_data['l10n']->get('add deliverable');
                        ?>
                        <select name="product" id="org_openpsa_sales_salesproject_deliverable_add" onchange="if (this.selectedIndex != 0) { this.form.submit(); }">
                            <option value="0"><?php echo $view_data['l10n']->get('select product'); ?></option>
                            <?php
                            foreach ($products as $product_id => $product)
                            {
                                echo "<option value=\"{$product_id}\">{$product}</option>\n";
                            }
                            ?>
                        </select>
                    </label>
                </form>
                <?php
                }
            if (array_key_exists('deliverables', $view_data))
            {
                ?>
                <ol class="deliverable_list">
                <?php
                foreach ($view_data['deliverables'] as $deliverable_guid => $deliverable)
                {
                    ?>
                    <li class="deliverable">
                        <h3>&(deliverable['title']:h);</h3>
                        <table class="details">
                            <tbody>
                                <tr>
                                    <th><?php echo $view_data['l10n']->get('estimated delivery'); ?></th>
                                    <td>&(deliverable['end']:h);</td>
                                </tr>
                                <tr>
                                    <th><?php echo $view_data['l10n']->get('price per unit'); ?></th>
                                    <td>&(deliverable['pricePerUnit']:h); / &(deliverable['unit']:h);</td>
                                </tr>
                                <tr>
                                    <th><?php echo $view_data['l10n']->get('units'); ?></th>
                                    <td>&(deliverable['units']:h);</td>
                                </tr>
                                <tr>
                                    <th><?php echo $view_data['l10n']->get('total'); ?></th>
                                    <td>&(deliverable['price']:h);</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="description">
                            &(deliverable['description']:h);
                        </div>
    
                        <div class="components">                    
                            &(deliverable['components']:h);
                        </div>                    
                        <div class="toolbar">
                            <form method="post" action="&(node[MIDCOM_NAV_FULLURL]);deliverable/process/&(deliverable_guid);">
                            <?php
                            switch ($view_data['deliverables_objects'][$deliverable_guid]->state)
                            {
                                case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_ORDERED:
                                    echo "<input type=\"submit\" class=\"deliver\" name=\"mark_delivered\" value=\"" . $view_data['l10n']->get('mark delivered') . "\" />\n";
                                    break;
                                case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DELIVERED:
                                    echo "<input type=\"submit\" class=\"invoice\" name=\"mark_invoiced\" value=\"" . $view_data['l10n']->get('invoice') . "\" />\n";
                                    echo "<input type=\"text\" size=\"5\" name=\"invoice\" value=\"{$view_data['deliverables_objects'][$deliverable_guid]->price}\" />\n";
                                    break;
                                case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_INVOICED:
                                    echo sprintf($view_data['l10n']->get('%s invoiced'), $view_data['deliverables_objects'][$deliverable_guid]->invoiced) . ". ";
                                    $invoice_value = $view_data['deliverables_objects'][$deliverable_guid]->price - $view_data['deliverables_objects'][$deliverable_guid]->invoiced;
                                    if ($invoice_value > 0)
                                    {
                                        echo "<input type=\"submit\" class=\"invoice\" name=\"mark_invoiced\" value=\"" . $view_data['l10n']->get('invoice') . "\" />\n";
                                        echo "<input type=\"text\" size=\"5\" name=\"invoice\" value=\"{$invoice_value}\" />\n";
                                    }
                                    break;
                                case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_NEW:
                                case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_PROPOSED:
                                default:
                                    echo "<input type=\"submit\" class=\"order\" name=\"mark_ordered\" value=\"" . $view_data['l10n']->get('mark ordered') . "\" />\n";
                            }
                            ?>
                            </form>
                        </div>
                    </li>
                    <?php
                }
                ?>
                </ol>
                <?php
            }
            ?>
        </div>
        <?php
    }
         
    //TODO: Configure whether to show in/both and reverse vs normal sorting ?
    $_MIDCOM->dynamic_load("{$node[MIDCOM_NAV_RELATIVEURL]}relatedto/render/{$view_data['salesproject']->guid}/both/normal"); 
    ?>
</div>
<div class="sidebar">
</div>
