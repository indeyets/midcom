<?php
$email_parts = array('','');
$yahoo_user = '';
$gmail_user = '';
$hotmail_user = '';

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
            <li class="ay" id="itab_item_yahoo"><a href="#invite_yahoo"></a></li>
            <li class="ay" id="itab_item_gmail"><a href="#invite_gmail"></a></li>
            <li class="ay" id="itab_item_myspace"><a href="#invite_myspace"></a></li>
            <li class="ay" id="itab_item_hotmail"><a href="#invite_hotmail"></a></li>
        </ul>
    </div>
    <div class="tabs_content" id="invite_yahoo">
        <h2>Invite your Yahoo friends</h2>
        <div class="invite_login_form">
            <form name="invite_yahoo" method="POST" onsubmit="return validate_fields(this);" action="">
                <input type="hidden" name="domain" value="yahoo.com" />
                <?php
                if ($email_parts[1] == 'yahoo.com')
                {
                    $yahoo_user = $email_parts[0];
                }
                ?>
                <label>Yahoo ID:</label><input class="text" type="text" name="username" value="&(yahoo_user);" /><label> @yahoo.com</label>
                <div class="clear_fix"></div>
                <label><?php echo $data['l10n']->get('password'); ?>:</label><input class="text" type="text" name="password" value="" /><input type="submit" name="sign_in" value="<?php echo $data['l10n']->get('fetch'); ?>" />
            </form>
        </div>
        <div class="description">
            <?php echo $_MIDCOM->i18n->get_string('no details are stored', 'com.magnettechnologies.contactgrabber'); ?>
        </div>
    </div>
    <div class="tabs_content" id="invite_gmail" style="display: none;">
        <h2>Invite your gMail friends</h2>
        <div class="invite_login_form">
            <form name="invite_gmail" method="POST" onsubmit="return validate_fields(this);" action="">
                <input type="hidden" name="domain" value="gmail.com" />
                <?php
                if ($email_parts[1] == 'gmail.com')
                {
                    $gmail_user = $user_email;
                }
                ?>
                <label><?php echo $data['l10n']->get('username'); ?>:</label><input class="text" type="text" name="username" value="&(gmail_user);" />
                <div class="clear_fix"></div>
                <label><?php echo $data['l10n']->get('password'); ?>:</label><input class="text" type="text" name="password" value="" /><input type="submit" name="sign_in" value="<?php echo $data['l10n']->get('fetch'); ?>" />
            </form>
        </div>
        <div class="description">
            <?php echo $_MIDCOM->i18n->get_string('no details are stored', 'com.magnettechnologies.contactgrabber'); ?>
        </div>
    </div>
    <div class="tabs_content" id="invite_myspace" style="display: none;">
        <h2>Invite your MySpace friends</h2>
        <div class="invite_login_form">
            <form name="invite_myspace" method="POST" onsubmit="return validate_fields(this);" action="">
                <input type="hidden" name="domain" value="myspace.com" />
                <label><?php echo $data['l10n']->get('username'); ?>:</label><input class="text" type="text" name="username" value="" />
                <div class="clear_fix"></div>
                <label><?php echo $data['l10n']->get('password'); ?>:</label><input class="text" type="text" name="password" value="" /><input type="submit" name="sign_in" value="<?php echo $data['l10n']->get('fetch'); ?>" />
            </form>
        </div>
        <div class="description">
            <?php echo $_MIDCOM->i18n->get_string('no details are stored', 'com.magnettechnologies.contactgrabber'); ?>
        </div>
    </div>
    <div class="tabs_content" id="invite_hotmail" style="display: none;">
        <h2>Invite your Hotmail friends</h2>
        <div class="invite_login_form">
            <form name="invite_hotmail" method="POST" onsubmit="return validate_fields(this);" action="">
                <input type="hidden" name="domain" value="hotmail.com" />
                <?php
                if ($email_parts[1] == 'hotmail.com')
                {
                    $hotmail_user = $user_email;
                }
                ?>
                <label><?php echo $data['l10n']->get('username'); ?>:</label><input class="text" type="text" name="username" value="&(hotmail_user);" />
                <div class="clear_fix"></div>
                <label><?php echo $data['l10n']->get('password'); ?>:</label><input class="text" type="text" name="password" value="" /><input type="submit" name="sign_in" value="<?php echo $data['l10n']->get('fetch'); ?>" />
            </form>
        </div>
        <div class="description">
            <?php echo $_MIDCOM->i18n->get_string('no details are stored', 'com.magnettechnologies.contactgrabber'); ?>
        </div>
    </div>
</div>