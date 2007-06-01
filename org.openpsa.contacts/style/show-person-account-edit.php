<?php
global $view;
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<div class="main">
    <form method="post" name="midcom_helper_datamanager__form" action="&(_MIDGARD["uri"]);" class="datamanager">
        <fieldset class="area">
            <legend><?php echo sprintf($data['l10n']->get("edit user account: %s %s"), $data['person']->firstname, $data['person']->lastname); ?></legend>
            <label for="org_openpsa_contacts_person_account_username">
                <span class="field_text"><?php echo $data['l10n_midcom']->get("username"); ?></span>
                <input type="text" name="org_openpsa_contacts_person_account_username" id="org_openpsa_contacts_person_account_username" class="shorttext" value="<?php echo $data['person']->username; ?>" />
            </label>
            <label for="org_openpsa_contacts_person_account_newpassword">
                <span class="field_text"><?php echo $data['l10n_midcom']->get("password"); ?></span>
                <input type="password" name="org_openpsa_contacts_person_account_newpassword" id="org_openpsa_contacts_person_account_newpassword" class="shorttext" maxlength="11" />
            </label>
            <label for="org_openpsa_contacts_person_account_newpassword2">
                <span class="field_text"><?php echo $data['l10n']->get("password repeat"); ?></span>
                <input type="password" name="org_openpsa_contacts_person_account_newpassword2" id="org_openpsa_contacts_person_account_newpassword2" class="shorttext" maxlength="11" />
            </label>
        </fieldset>
        <div class="form_toolbar">
            <input type="submit" name="midcom_helper_datamanager_submit" class="save" value="<?php echo $data['l10n_midcom']->get("save"); ?>" />
        </div>
    </form>
</div>