<?php
//$midgard = $_MIDCOM->get_midgard();
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$user = $_MIDCOM->auth->user;
?>
<div id="aisfooter">
    <div class="version"><a href="http://www.midgard-project.org/">Midgard CMS <?php echo mgd_version(); ?></a> (MidCOM <?php echo $GLOBALS["midcom_version"]; ?>)</div>
    <div class="user">
    <?php echo $data["l10n_midcom"]->get("user"); ?>: <span class="fn">&(user.name);</span></div>
</div>
