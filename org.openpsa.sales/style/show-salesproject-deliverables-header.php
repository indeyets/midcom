<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<div class="deliverables">
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
                    foreach ($view_data['products'] as $product_id => $product)
                    {
                        echo "<option value=\"{$product_id}\">{$product}</option>\n";
                    }
                    ?>
                </select>
            </label>
        </form>
        <?php
    }
    ?>
    <ol class="deliverable_list">
