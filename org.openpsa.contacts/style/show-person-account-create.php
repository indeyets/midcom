<?php
global $view;
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<div class="main">
    <form method="post" name="midcom_helper_datamanager__form" action="&(_MIDGARD["uri"]);" class="datamanager">
        <fieldset class="area">
            <legend><?php echo sprintf($data['l10n']->get("user account for %s"), $data['person']->name); ?></legend>
            <label for="org_openpsa_contacts_person_account_username">
                <span class="field_text"><?php echo $data['l10n_midcom']->get("username"); ?></span>
                <input type="text" name="org_openpsa_contacts_person_account_username" id="org_openpsa_contacts_person_account_username" class="shorttext" value="&(data["default_username"]);" />
            </label>
            <label for="org_openpsa_contacts_person_account_password">
                <span class="field_text"><?php echo $data['l10n_midcom']->get("password"); ?></span>
                <input type="text" name="org_openpsa_contacts_person_account_password" id="org_openpsa_contacts_person_account_password" class="shorttext" value="&(data["default_password"]);" maxlength="11" />
            </label>
        </fieldset>
        <div class="form_toolbar">
            <input type="submit" name="midcom_helper_datamanager_submit" class="save" value="<?php echo $data['l10n_midcom']->get("save"); ?>" />
        </div>
    </form>
</div>