<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1>&(data['page_title']:h);</h1>
<table class="report">
    <thead>
        <tr>
            <th><?php echo $data['l10n']->get('date'); ?></th>
            <th><?php echo $data['l10n']->get('person'); ?></th>
            <th><?php echo $data['l10n']->get('vehicle'); ?></th>
            <th><?php echo $data['l10n']->get('hours'); ?></th>
            <th><?php echo $data['l10n']->get('mileage'); ?></th>
            <th><?php echo $data['l10n']->get('cargos'); ?></th>
            <th><?php echo $data['l10n']->get('siirto1120m'); ?></th>
            <th><?php echo $data['l10n']->get('siirto2130m'); ?></th>
            <th><?php echo $data['l10n']->get('siirto3140m'); ?></th>
            <th><?php echo $data['l10n']->get('siirto4150m'); ?></th>
            <th><?php echo $data['l10n']->get('siirto51plus'); ?></th>
            <th><?php echo $data['l10n']->get('sakki150m'); ?></th>
            <th><?php echo $data['l10n']->get('f140lastia'); ?></th>
            <th><?php echo $data['l10n']->get('f240lastia'); ?></th>
            <th><?php echo $data['l10n']->get('f300lastia'); ?></th>
            <th><?php echo $data['l10n']->get('f600lastia'); ?></th>
            <th><?php echo $data['l10n']->get('netweight'); ?></th>
            <th><?php echo $data['l10n']->get('velocity per weight'); ?></th>
            <th><?php echo $data['l10n']->get('containers'); ?></th>
            <th><?php echo $data['l10n']->get('containers per km'); ?></th>
            <th><?php echo $data['l10n']->get('containers per hour'); ?></th>
            <th><?php echo $data['l10n']->get('cost'); ?></th>
        </tr>
    </thead>
    <tbody>
