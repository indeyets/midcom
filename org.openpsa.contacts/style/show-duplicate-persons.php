<?php
// TODO: cache this!
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
//$data =& $_MIDCOM->get_custom_context_data('request_data');

// Display the persons
$contact1 = new org_openpsa_contactwidget($data['person1']);
$contact1->link = "{$node[MIDCOM_NAV_FULLURL]}person/{$data['person1']->guid}/";
$contact1->show_groups = false;

$contact2 = new org_openpsa_contactwidget($data['person2']);
$contact2->link = "{$node[MIDCOM_NAV_FULLURL]}person/{$data['person2']->guid}/";
$contact2->show_groups = false;

?>
<h1><?php echo $data['l10n']->get('merge persons'); ?></h1>
<p><?php echo $data['l10n']->get('choose the person to keep'); ?></p>
<form method="post" class="org_openpsa_contacts_duplicates">
    <input type="hidden" name="org_openpsa_contacts_handler_duplicates_person_options[1]" value="<?php echo $data['person1']->guid; ?>" />
    <input type="hidden" name="org_openpsa_contacts_handler_duplicates_person_options[2]" value="<?php echo $data['person2']->guid; ?>" />
    <input type="hidden" name="org_openpsa_contacts_handler_duplicates_person_loop_i" value="<?php echo $data['loop_i']; ?>" />
    <table class="org_openpsa_contacts_duplicates">
        <tr class="contacts">
            <td><?php $contact1->show(); ?></td>
            <td align="center"><?php echo $data['l10n']->get('vs'); ?></td>
            <td><?php $contact2->show(); ?></td>
        </tr>
        <tr align="center" class="choices">
            <td><input type="submit" class="keepone" name="org_openpsa_contacts_handler_duplicates_person_keep[<?php echo $data['person1']->guid; ?>]" value="<?php echo $data['l10n']->get('keep this'); ?>" /></td>
            <td><input type="submit" class="keepboth" name="org_openpsa_contacts_handler_duplicates_person_keep[both]" value="<?php echo $data['l10n']->get('keep both'); ?>" /></td>
            <td><input type="submit" class="keepone" name="org_openpsa_contacts_handler_duplicates_person_keep[<?php echo $data['person2']->guid; ?>]" value="<?php echo $data['l10n']->get('keep this'); ?>" /></td>
        </tr>
        <tr align="center" class="choices">
            <td colspan=3><input type="submit" class="decidelater" name="org_openpsa_contacts_handler_duplicates_person_decide_later" value="<?php echo $data['l10n']->get('decide later'); ?>" /></td>
        </tr>
    </table>
</form>