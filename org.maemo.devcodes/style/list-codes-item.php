<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$code =& $data['code'];
if (!empty($code->recipient))
{
    $recipient =& org_openpsa_contacts_person::get_cached($code->recipient);
}
?>
        <tr>
            <td><a href="&(data['prefix']);code/&(code.guid);.html">&(code.code);</td>
            <td>&(code.area);</td>
            <td><?php
                if (isset($recipient))
                {
                    echo $recipient->name;
                }
                else
                {
                    echo $data['l10n']->get('not assigned');
                }
                ?>
            </td>
        </tr>
