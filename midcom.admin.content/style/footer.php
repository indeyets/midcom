<?php
//$midgard = $GLOBALS["midcom"]->get_midgard();
$user = mgd_get_person($_MIDGARD['user']);
?>
<div id="aisfooter">
    <div class="version"><a href="http://www.midgard-project.org/">Midgard CMS <?php echo mgd_version(); ?></a> (MidCOM <?php echo $GLOBALS["midcom_version"]; ?>)</div>
    <div class="user"><?php echo $GLOBALS["view_l10n_midcom"]->get("user"); ?>: <span class="fn">&(user.name);</span></div>
</div>
