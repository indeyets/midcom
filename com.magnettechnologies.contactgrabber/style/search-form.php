<?php
$email_parts = array('','');
$yahoo_user = '';
$gmail_user = '';

if ($_MIDCOM->auth->user)
{
    $current_user =& $_MIDCOM->auth->user->get_storage();    
    $user_email = $current_user->email;
    $email_parts = split("@", $user_email, 2);
}
?>
<div id="invite_login_holder">
    <div class="invite_tabs">
        <ul>
            <li class="ag" id="itab_item_gmail"><a href="#invite_gmail"></a></li>
        </ul>
    </div>

    <div class="tabs_content" id="invite_gmail">
        <h2><?php echo $data['l10n']->get('invite your gmail friends'); ?></h2>
        <div class="invite_login_form">
            <form name="invite_gmail" method="POST" onsubmit="return com_magnettechnologies_contactgrabber_validate(this);" action="">
                <input type="hidden" name="domain" value="gmail.com" />
                <?php
                if ($email_parts[1] == 'gmail.com')
                {
                    $gmail_user = $user_email;
                }
                ?>
                <label><?php echo $data['l10n']->get('username'); ?>:</label><input class="text" type="text" name="username" value="&(gmail_user);" />
                <div class="clear_fix"></div>
                <label><?php echo $data['l10n']->get('password'); ?>:</label><input class="text" type="password" name="password" value="" /><input type="submit" name="sign_in" value="<?php echo $data['l10n']->get('fetch'); ?>" />
            </form>
        </div>
        <div class="description">
            <?php echo $data['l10n']->get('no details are stored'); ?>
        </div>
    </div>
</div>