<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $view_data['controller']->datamanager;
?>
<div class="main salesproject">
    <div class="contacts">
        <?php
        $customer = new midcom_db_group($view_data['salesproject']->customer);
        echo "<h2>{$customer->official}</h2>\n";
        
        foreach ($view_data['salesproject']->contacts as $contact_id => $active)
        {
            $person = new midcom_db_person($contact_id);
            $person_card = new org_openpsa_contactwidget($person);
            $person_card->show();
        }
        ?>
    </div>
    <div class="info">
        <?php 
        // Display sales project metadata
        $view->display_view();
        ?>
    </div
    <div style="clear: both;"></div>    