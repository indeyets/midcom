<?php
global $view;
global $techsupport;
$tguid = $view->guid();
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<tr<?php
if ($GLOBALS["view_even"])
{
    echo " class=\"even\"";
}
?>>
    <?php if (!$techsupport['hidefields']['idstring']) { ?><td><a href="&(prefix);&(tguid);.html">&(view.idstring);</a></td><?php } ?>
    <?php if (!$techsupport['hidefields']['opened']) { ?><td><?php echo date($techsupport['TT']['timeformat'], $view->opened); ?></td><?php } ?>
    <?php if (!$techsupport['hidefields']['lastchanged']) { ?><td><?php echo date($techsupport['TT']['timeformat'], $view->lastaction['stamp']); ?></td><?php } ?>
    <?php if (!$techsupport['hidefields']['crmcompany']) { ?><td><? if ($view->CRMCompany) { $company=mgd_get_object_by_guid($view->CRMCompany); if ($company->name!="") { ?>&(company.official);<? } else { ?><?php echo loc_techs("Not Found"); ?><? } } else { ?><?php echo loc_techs("Not selected"); ?><? } ?></td><?php } ?>
    <td><?php if (!$view->title) $view->title=loc_techs("Untitled"); ?><a href="&(prefix);&(tguid);.html">&(view.title);</a></td>
    <td><? echo $view->type_cmp; ?></td>
    <td><? if ($view->target == "-1") { $tStr=$view->extended['other']['target']; } else { $tStr=$techsupport['TT']['target'][$view->target]; } if (!$tStr) $tStr=loc_techs("Not defined"); ?>&(tStr);</td>
    <td><? if ($view->assignee) { $assignee=mgd_get_object_by_guid($view->assignee); if ($assignee->name!="") { ?>&(assignee.name);<? } else { ?><?php echo loc_techs("Not Found"); ?><? } } else { ?><?php echo loc_techs("Not assigned"); ?><? } ?></td>
    <?php if (!$techsupport['hidefields']['email'] && !$techsupport['hidefields']['listEmail']) { ?><td><?php echo $view->contacts['email']; if (!$view->contacts['email']) echo "&nbsp;"; ?></td><?php } ?>
    <?php if (!$techsupport['hidefields']['fix_to']) { ?><td><? if (!$view->fix_to) { echo loc_techs("Not set"); } else { echo $view->fix_to; } ?></td><?php } ?>
    <td><? echo $techsupport['TT']['status'][$view->status]; ?></td>
    <?php if (!$techsupport['hidefields']['priority']) { ?><td><? echo $techsupport['TT']['priority'][$view->priority]; ?></td><?php } ?>
    <?php if (!$techsupport['hidefields']['severity']) { ?><td><? echo $techsupport['TT']['severity'][$view->severity]; ?></td><?php } ?>
</tr>
