<?php
// Available request keys: person, controller
// $data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h1><?php echo $data['topic']->extra; ?></h1>
<h2><?php echo $data['l10n']->get('edit user account'); ?>: <?php echo $data['person']->name; ?></h2>
<?php
if (count($data['errors']) > 0)
{
    echo "<ul class=\"messages\">\n";
    foreach ($data['errors'] as $error)
    {
        echo "    <li>" . $data['l10n']->get($error) . "</li>\n";
    }
    echo "</ul>\n";
}
?>
<p>
    <a href="&(prefix);passwords/" target="_blank"><?php echo $data['l10n']->get('generate random passwords'); ?></a>
</p>
<form method="post" action="&(_MIDGARD['uri']:h);" id="net_nemein_personnel_user_account" class="datamanager2">
    <div class="form">
        <label for="username">
            <?php echo $data['l10n']->get('username'); ?> (<?php echo $data['l10n']->get('use only characters'); ?> <code>a-z</code>, <code>A-Z</code>, <code>0-9</code>, <code>.</code>, <code>,</code>)
        </label>
        <input type="text" name="f_username" id="username" value="<?php echo $data['person']->username; ?>" />
        <label for="password_1st">
            <?php echo $data['l10n']->get('password'); ?>
        </label>
        <input type="password" name="f_password[0]" id="password_1st" value="" />
        <label for="password_1st">
            <?php echo $data['l10n']->get('retype password'); ?>
        </label>
        <input type="password" name="f_password[1]" id="password_1st" value="" />
        <label for="send_email">
            <input type="checkbox" name="send_email" id="send_email" value="1" />
            <?php echo $data['l10n']->get('send password by email to'); ?>
            <input type="text" name="email" value="<?php echo $data['person']->email; ?>" />
        </label>
        <br /><br />
    </div>
    <div class="form_toolbar">
        <input type="submit" name="f_submit" class="save" value="<?php echo $data['l10n_midcom']->get('save'); ?>" />
        <input type="submit" name="f_cancel" class="cancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
    </div>
</form>
