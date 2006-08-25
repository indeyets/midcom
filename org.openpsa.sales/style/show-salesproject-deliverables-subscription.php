<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
$deliverable = $view_data['deliverable'];

$invoices_node = midcom_helper_find_node_by_component('org.openpsa.invoices');
?>
<li class="deliverable subscription">
    <h3>&(deliverable['title']:h);</h3>
    <table class="details">
        <tbody>
            <tr>
                <th><?php echo $view_data['l10n']->get('subscription starts'); ?></th>
                <td>&(deliverable['start']:h);</td>
            </tr>
            <tr>
                <th><?php echo $view_data['l10n']->get('subscription ends'); ?></th>
                <td>&(deliverable['end']:h);</td>
            </tr>
            <tr>
                <th><?php echo $view_data['l10n']->get('continuous subscription'); ?></th>
                <td>&(deliverable['continuous']:h);</td>
            </tr>
            <tr>
                <th><?php echo $view_data['l10n']->get('price per unit'); ?></th>
                <td>&(deliverable['pricePerUnit']:h); / &(deliverable['unit']:h);</td>
            </tr>
            <tr>
                <th><?php echo $view_data['l10n']->get('cost per unit'); ?></th>
                <td>&(deliverable['costPerUnit']:h); &(deliverable['costType']:h);</td>
            </tr>
            <tr>
                <th><?php echo $view_data['l10n']->get('units'); ?></th>
                <td>&(deliverable['units']:h);</td>
            </tr>
            <tr>
                <th><?php echo $view_data['l10n']->get('total'); ?></th>
                <td>&(deliverable['price']:h);</td>
            </tr>
            <tr>
                <th><?php echo $view_data['l10n']->get('invoice by actual units'); ?></th>
                <td>&(deliverable['invoiceByActualUnits']:h);</td>
            </tr>                                
            <tr>
                <th><?php echo $view_data['l10n']->get('total cost'); ?></th>
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
    <div class="invoices">
        <?php
        if ($invoices_node
            && (   $view_data['deliverable_object']->state == ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_STARTED
                || $view_data['deliverable_object']->state == ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_INVOICED))
        {
            $_MIDCOM->dynamic_load($invoices_node[MIDCOM_NAV_RELATIVEURL] . "list/deliverable/{$view_data['deliverable_object']->guid}");
            // FIXME: This is a rather ugly hack
            $_MIDCOM->style->enter_context(0);
        }
        ?>
    </div>                 
    <div class="toolbar">
        <form method="post" action="&(node[MIDCOM_NAV_FULLURL]);deliverable/process/<?php echo $view_data['deliverable_object']->guid; ?>">
        <?php
        echo $view_data['deliverable_toolbar'];
        ?>
        </form>
    </div>
</li>