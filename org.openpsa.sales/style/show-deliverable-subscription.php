<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view =& $data['view_deliverable'];

$status = $data['deliverable']->get_status();

$invoices_node = midcom_helper_find_node_by_component('org.openpsa.invoices');
$projects_node = midcom_helper_find_node_by_component('org.openpsa.projects');
?>
<div class="org_openpsa_sales_salesproject_deliverable &(status);">
    <div class="main">
        <div class="tags">&(view['tags']:h);</div>
        <?php
        echo "<h1>" . $data['l10n']->get('subscription') . ": {$data['deliverable']->title}</h1>\n";
        ?>
        &(view['description']:h);
        <table class="agreement">
            <tbody>
                <tr>
                    <th><?php echo $data['l10n']->get('status'); ?></th>
                    <td><?php echo $data['l10n']->get($status); ?></td>
                </tr>
                <tr>
                    <th><?php echo $data['l10n']->get('subscription begins'); ?></th>
                    <td>&(view['start']:h);</td>
                </tr>
                <?php
                if (!$data['deliverable']->continuous)
                {
                    ?>
                    <tr>
                        <th><?php echo $data['l10n']->get('subscription ends'); ?></th>
                        <td>&(view['end']:h);</td>
                    </tr>
                    <?php
                }
                else
                {
                    ?>
                    <tr>
                        <td colspan="2">
                            <ul>
                                <li><?php echo $data['l10n']->get('continuous subscription'); ?></li>
                            </ul>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <th><?php echo $data['l10n']->get('invoicing period'); ?></th>
                    <td>&(view['unit']:h);</td>
                </tr>
                </tr>
                <?php
                if ($data['deliverable']->supplier)
                {
                    ?>
                    <tr>
                        <th><?php echo $data['l10n']->get('supplier'); ?></th>
                        <td>&(view['supplier']:h);</td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <th colspan="2" class="area"><?php echo $data['l10n']->get('pricing information'); ?></th>
                </tr>
                <tr>
                    <th><?php echo $data['l10n']->get('pricing'); ?></th>
                    <td>&(view['pricePerUnit']:h); <?php echo $data['l10n']->get('per unit'); ?></td>
                </tr>
                <tr>
                    <th><?php echo $data['l10n']->get('cost structure'); ?></th>
                    <td>&(view['costPerUnit']:h); &(view['costType']:h);</td>
                </tr>
                <tr>
                    <th><?php echo $data['l10n']->get('units'); ?></th>
                    <td>&(view['units']:h);<?php
                        if ($data['deliverable']->plannedUnits)
                        {
                            echo ' (' . sprintf($data['l10n']->get('%s planned'), $view['plannedUnits']) . ')';
                        }
                        ?></td>
                </tr>
                <?php
                if ($data['deliverable']->invoiceByActualUnits)
                {
                    ?>
                    <tr>
                        <td colspan="2">
                            <ul>
                                <li><?php echo $data['l10n']->get('invoice by actual units'); ?></li>
                                <?php
                                if ($data['deliverable']->invoiceApprovedOnly)
                                {
                                    echo "<li>" . $data['l10n']->get('invoice approved only') . "</li>\n";
                                }
                                ?>
                            </ul>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <th colspan="2" class="area"><?php echo $data['l10n']->get('invoicing information'); ?></th>
                </tr>
                <tr>
                    <th><?php echo $data['l10n']->get('price'); ?></th>
                    <td>&(view['price']:h);</td>
                </tr>
                <tr>
                    <th><?php echo $data['l10n']->get('cost'); ?></th>
                    <td>&(view['cost']:h);</td>
                </tr>
                <?php
                if ($data['deliverable']->invoiced > 0)
                {
                    ?>
                    <tr>
                        <th><?php echo $data['l10n']->get('invoiced'); ?></th>
                        <td><?php echo $data['deliverable']->invoiced; ?></td>
                    </tr>
                    <?php
                }
                ?>
            <tbody>
        </table>
    </div>
    <div class="sidebar">
        <div class="contacts area">
            <?php
            $customer = new midcom_db_group($data['salesproject']->customer);
            echo "<h2>" . $data['l10n']->get('customer') . ": {$customer->official}</h2>\n";

            foreach ($data['salesproject']->contacts as $contact_id => $active)
            {
                $person = new midcom_db_person($contact_id);
                $person_card = new org_openpsa_contactwidget($person);
                $person_card->show();
            }
            ?>
        </div>

        <div class="at area">
        <?php
        $at_entries = $data['deliverable']->get_at_entries();
        if (count($at_entries) > 0)
        {
            echo "<h2>" . $_MIDCOM->i18n->get_string('scheduled operations', 'midcom.services.at') . "</h2>\n";
            echo "<table>\n";
            echo "    <thead>\n";
            echo "        <tr>\n";
            echo "            <th>" . $_MIDCOM->i18n->get_string('time', 'midcom.services.at') . "</th>\n";
            echo "            <th>" . $_MIDCOM->i18n->get_string('status', 'midcom.services.at') . "</th>\n";
            echo "        </tr>\n";
            echo "    </thead>\n";
            echo "    <tbody>\n";
            foreach ($at_entries as $entry)
            {
                echo "        <tr>\n";
                echo "            <td>" . strftime('%x %X', $entry->start) . "</td>\n";

                echo "            <td>";
                switch ($entry->status)
                {
                    case MIDCOM_SERVICES_AT_STATUS_SCHEDULED:
                        echo $_MIDCOM->i18n->get_string('scheduled', 'midcom.services.at');
                        break;
                    case MIDCOM_SERVICES_AT_STATUS_RUNNING:
                        echo $_MIDCOM->i18n->get_string('running', 'midcom.services.at');
                        break;
                    case MIDCOM_SERVICES_AT_STATUS_FAILED:
                        echo $_MIDCOM->i18n->get_string('failed', 'midcom.services.at');
                        break;
                }
                echo "</td>\n";

                echo "        </tr>\n";
            }
            echo "    </tbody>\n";
            echo "</table>\n";
        }
        ?>
        </div>
    </div>
    <div class="wide">
        &(view['components']:h);

        <div class="tasks">
            <?php
            if (   $projects_node
                && $data['deliverable']->orgOpenpsaObtype == ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_SERVICE
                && $data['deliverable']->state >= ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_ORDERED)
            {
                $_MIDCOM->dynamic_load($projects_node[MIDCOM_NAV_RELATIVEURL] . "task/list/all/agreement/{$data['deliverable']->id}");
                // FIXME: This is a rather ugly hack
                $_MIDCOM->style->enter_context(0);
            }
            ?>
        </div>
        <div class="invoices">
            <?php
            if (   $invoices_node
                && $data['deliverable']->invoiced > 0)
            {
                $_MIDCOM->dynamic_load($invoices_node[MIDCOM_NAV_RELATIVEURL] . "list/deliverable/{$data['deliverable']->guid}");
                // FIXME: This is a rather ugly hack
                $_MIDCOM->style->enter_context(0);
            }
            ?>
        </div>
    </div>
</div>