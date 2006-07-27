<?php
header("Content-type: text/xml; charset=UTF-8");
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<!DOCTYPE uicomponent PUBLIC  "UIComponent DTD" "localization/s.dtd">
<texts>
<common>
<text id="loading"><?php echo $GLOBALS["view_l10n"]->get("loading"); ?></text>
<text id="add"><?php echo $GLOBALS["view_l10n"]->get("create note"); ?></text>
<text id="delete"><?php echo $GLOBALS["view_l10n"]->get("delete selected note"); ?></text>
<text id="yes"><?php echo $GLOBALS["view_l10n_midcom"]->get("yes"); ?></text>
<text id="no"><?php echo $GLOBALS["view_l10n_midcom"]->get("no"); ?></text>
<text id="cancel"><?php echo $GLOBALS["view_l10n_midcom"]->get("cancel"); ?></text>
<text id="dragme"><?php echo $GLOBALS["view_l10n"]->get("drag into place"); ?></text>
<text id="add_object"><?php echo $GLOBALS["view_l10n"]->get("create note"); ?></text>
<text id="remove_headline"><?php echo $GLOBALS["view_l10n"]->get("delete note"); ?></text>
<text id="remove_help"><?php echo $GLOBALS["view_l10n"]->get("delete selected note"); ?></text>
<text id="remove_text"><?php echo $GLOBALS["view_l10n"]->get("are you sure you want to delete note"); ?></text>
<text id="save"><?php echo $GLOBALS["view_l10n_midcom"]->get("save"); ?></text>
<text id="set_info"><?php echo $GLOBALS["view_l10n"]->get("update note text"); ?></text>
<text id="save_info"><?php echo $GLOBALS["view_l10n"]->get("save notes"); ?></text>
</common>
</texts>
<?php
exit();
?>