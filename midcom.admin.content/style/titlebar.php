<div id="aistitlebar">
<h1><?php 
    echo $GLOBALS['view_l10n_midcom']->get('midcom administration'); 
?>: <?php 
    $context = $GLOBALS['view_contentmgr']->viewdata['context'];
    $topic = $GLOBALS['midcom']->get_context_data($context, MIDCOM_CONTEXT_CONTENTTOPIC);
	$component = $GLOBALS['midcom']->get_context_data($context, MIDCOM_CONTEXT_COMPONENT);
	$i18n =& $GLOBALS['midcom']->get_service('i18n');
	$l10n = $i18n->get_l10n($component);
	$component_name = $l10n->get($component);
    echo "{$topic->extra} (<abbr title='{$component}'>{$component_name}</abbr>)";
?></h1>
</div>
