<?php
global $view_topic, $view_config, $view_dm, $view;

$i18n =& $GLOBALS["midcom"]->get_service("i18n");
$l10n =& $i18n->get_l10n("de.linkm.newsticker");
$l10n_midcom =& $i18n->get_l10n("midcom");
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

if (array_key_exists ("de_linkm_newsticker_pperr", $_REQUEST))
    $error = $_REQUEST["de_linkm_newsticker_pperr"];
else
    $error = $view_dm->errstr;
?>

<h1><?php echo $l10n->get("add a new entry"); ?></h1>

<?php if ($error != "") { ?>
<p style="color:red;">&(error:h);</p>
<?php } ?>

<?php $view_dm->display_form(); ?>