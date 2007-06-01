<?php
// Available request keys: resource, datamanager

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['view_resource'];

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="net_nemein_reservations_resource">
    <h1>&(view['title']:h);</h1>
    
    <?php
    if ($data['resource']->owner)
    {
        echo "<div class=\"owner\">\n";
        echo "<h2>" . $data['l10n']->get('person responsible') . "</h2>\n";
        // Display the contact
        $person = new midcom_db_person($data['resource']->owner);
        $contact = new org_openpsa_contactwidget($person);
        $contact->show();
        echo "</div>\n";
    }
    ?>
    
    <table>
        <tbody>
            <tr>
                <th><?php echo $data['l10n']->get('location'); ?></th>
                <td>&(view['location']:h);</td>
            </tr>
            <tr>
                <th><?php echo $data['l10n']->get('capacity'); ?></th>
                <td>&(view['capacity']:h);</td>
            </tr>
        </tbody>
    </table>
    
    &(view['description']:h);