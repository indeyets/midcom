<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
$deliverable = $data['deliverable'];

$invoices_node = midcom_helper_find_node_by_component('org.openpsa.invoices');
$projects_node = midcom_helper_find_node_by_component('org.openpsa.projects');

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<li class="deliverable subscription">
    <a name="<?php echo $data['deliverable_object']->guid; ?>"></a>
    <div class="tags">&(deliverable['tags']:h);</div>
    <?php
    echo "<h3><a href=\"{$prefix}deliverable/{$data['deliverable_object']->guid}/\">{$data['deliverable_object']->title}</a></h3>\n";
    ?>
    <table class="details">
        <tbody>
            <tr>
                <th><?php echo $data['l10n']->get('supplier'); ?></th>
                <td>&(deliverable['supplier']:h);</td>
            </tr>
            <tr>
                <th><?php echo $data['l10n']->get('subscription starts'); ?></th>
                <td>&(deliverable['start']:h);</td>
            </tr>
            <tr>
                <th><?php echo $data['l10n']->get('subscription ends'); ?></th>
                <td>&(deliverable['end']:h);</td>
            </tr>
            <tr>
                <th><?php echo $data['l10n']->get('continuous subscription'); ?></th>
                <td>&(deliverable['continuous']:h);</td>
            </tr>
            <tr>
                <th><?php echo $data['l10n']->get('price per unit'); ?></th>
                <td>&(deliverable['pricePerUnit']:h); / &(deliverable['unit']:h);</td>
            </tr>
            <tr>
                <th><?php echo $data['l10n']->get('cost per unit'); ?></th>
                <td>&(deliverable['costPerUnit']:h); &(deliverable['costType']:h);</td>
            </tr>
            <tr>
                <th><?php echo $data['l10n']->get('units'); ?></th>
                <td>&(deliverable['units']:h);</td>
            </tr>
            <tr>
                <th><?php echo $data['l10n']->get('total'); ?></th>
                <td>&(deliverable['price']:h);</td>
            </tr>
            <tr>
                <th><?php echo $data['l10n']->get('invoice by actual units'); ?></th>
                <td>&(deliverable['invoiceByActualUnits']:h);</td>
            </tr>
            <tr>
                <th><?php echo $data['l10n']->get('invoice approved only'); ?></th>
                <td>&(deliverable['invoiceApprovedOnly']:h);</td>
            </tr>
            <tr>
                <th><?php echo $data['l10n']->get('total cost'); ?></th>
                <td>&(deliverable['cost']:h);</td>
            </tr>
        </tbody>
    </table>

    <div class="description">
        &(deliverable['description']:h);
    </div>

    <div class="components">
        &(deliverable['components']:h);
    </div>
    <div class="tasks">
        <?php
        if (   $projects_node
            /*&& $data['deliverable_object']->orgOpenpsaObtype == ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_SERVICE*/
            && $data['deliverable_object']->state >= ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_ORDERED)
        {
            $_MIDCOM->dynamic_load($projects_node[MIDCOM_NAV_RELATIVEURL] . "task/list/all/agreement/{$data['deliverable_object']->id}");
            // FIXME: This is a rather ugly hack
            $_MIDCOM->style->enter_context(0);
        }
        ?>
    </div>
    <div class="invoices">
        <?php
        if ($invoices_node
            && (   $data['deliverable_object']->state == ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_STARTED
                || $data['deliverable_object']->state == ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_INVOICED))
        {
            $_MIDCOM->dynamic_load($invoices_node[MIDCOM_NAV_RELATIVEURL] . "list/deliverable/{$data['deliverable_object']->guid}");
            // FIXME: This is a rather ugly hack
            $_MIDCOM->style->enter_context(0);
        }
        ?>
    </div>
    <div class="toolbar">
        <form method="post" action="&(node[MIDCOM_NAV_FULLURL]);deliverable/process/<?php echo $data['deliverable_object']->guid; ?>">
        <?php
        echo $data['deliverable_toolbar'];
        ?>
        </form>
    </div>
</li>