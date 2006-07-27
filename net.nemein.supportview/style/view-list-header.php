<?php
global $techsupport;
?>
<div class="net_nemein_supportview">
<table>
    <thead>
        <tr>
            	<?php if (!$techsupport['hidefields']['idstring']) { ?><th><?php echo loc_techs("ID"); ?></th><?php } ?>
            	<?php if (!$techsupport['hidefields']['opened']) { ?><th><?php echo loc_techs("Opened"); ?></th><?php } ?>
            	<?php if (!$techsupport['hidefields']['lastchanged']) { ?><th><?php echo loc_techs("Last action time"); ?></th><?php } ?>
            	<?php if (!$techsupport['hidefields']['crmcompany']) { ?><th><?php echo loc_techs("Company"); ?></th><?php } ?>
            	<th><?php echo loc_techs("Title"); ?></th>
            	<th><?php echo loc_techs("Type"); ?></th>
            	<th><?php echo loc_techs("Target"); ?></th>
            	<th><?php echo loc_techs("Assigned to"); ?></th>
            	<?php if (!$techsupport['hidefields']['email'] && !$techsupport['hidefields']['listEmail']) { ?><th><?php echo loc_techs("E-Mail"); ?></th><?php } ?>
            	<?php if (!$techsupport['hidefields']['fix_to']) { ?><th><?php echo loc_techs("Fix to"); ?></th><?php } ?>
            	<th><?php echo loc_techs("Status"); ?></th>
            	<?php if (!$techsupport['hidefields']['priority']) { ?><th><?php echo loc_techs("Priority"); ?></th><?php } ?>
            	<?php if (!$techsupport['hidefields']['severity']) { ?><th><?php echo loc_techs("Severity"); ?></th><?php } ?>
        </tr>
    </thead>
    <tbody>
