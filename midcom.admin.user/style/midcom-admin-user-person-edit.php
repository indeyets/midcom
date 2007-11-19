<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1><?php echo $data['view_title']; ?></h1>
<div id="midcom_admin_user_passwords">
    <a href="&(prefix);__mfa/asgard_midcom.admin.user/password/" target="_blank"><?php echo $data['l10n']->get('generate passwords'); ?></a>
</div>
<?php $data['controller']->display_form(); ?>
<script type="text/javascript">
    // <![CDATA[
        $j('#midcom_admin_user_passwords a')
            .attr('href', '#')
            .attr('target', '_self')
            .click(function()
            {
                $j(this.parentNode).load('&(prefix);__mfa/asgard_midcom.admin.user/password/?ajax&timestamp=<?php echo time(); ?>');
            });
    // ]]>
</script>
