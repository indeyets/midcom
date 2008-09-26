<h1><?php echo sprintf($data['l10n']->get('delete host %s'), "{$data['host']->name}{$data['host']->prefix}"); ?></h1>
<form method="post" action="&(_MIDGARD['uri']);">
    <fieldset>
        <legend><?php echo $data['l10n']->get('confirm'); ?></legend>
        <?php
        $data['datamanager']->display_view();
        ?>
        <p>
            <input type="submit" name="f_delete" value="<?php echo $data['l10n_midcom']->get('delete'); ?>" />
            <input type="submit" name="f_cancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
        </P>
    </fieldset>
</form>