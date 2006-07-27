<?php
global $view_process;
?>
<div class="process">
<h2>&(view_process.title);</h2>

&(view_process.description:f);

<table>
    <thead>
        <tr>
            <th><?php echo $GLOBALS["view_l10n_midcom"]->get("date"); ?></th>
            <th><?php echo $GLOBALS["view_l10n"]->get("reporter"); ?></th>
            <th><?php echo $GLOBALS["view_l10n_midcom"]->get("description"); ?></th>
            <th class="hours"><?php echo $GLOBALS["view_l10n"]->get("hours"); ?></th>
            <th><?php echo $GLOBALS["view_l10n"]->get("approval"); ?></th>
        </tr>
    </thead>
    <tbody>