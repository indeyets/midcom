<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

if (count($data['persons']) > 0)
{
    if ($data['enabled'] == 0)
    {
        $disabled = ' disabled="disabled"';
    }
    else
    {
        $disabled = '';
    }
    ?>
        </tbody>
        <tfoot>
            <tr>
                <td>&nbsp;</td>
                <td colspan="<?php echo count($data['list_fields']); ?>">
                    <select id="midcom_admin_user_action" name="midcom_admin_user_action"<?php echo $disabled; ?>>
                        <option value=""><?php echo $_MIDCOM->i18n->get_string('choose action', 'midcom.admin.user'); ?></option>
                        <?php
                        if ($data['config']->get('allow_manage_accounts'))
                        {
                            ?>
                            <option value="removeaccount"><?php echo $_MIDCOM->i18n->get_string('remove account', 'midcom.admin.user'); ?></option>
                            <?php
                        }
                        ?>
                        <option value="groupadd" onclick="javascript:document.getElementById('midcom_admin_user_group').style.display='inline';"><?php echo $_MIDCOM->i18n->get_string('add to group', 'midcom.admin.user'); ?></option>
                        <option value="passwords"><?php echo $_MIDCOM->i18n->get_string('generate new passwords', 'midcom.admin.user'); ?></option>
                    </select>
                    <select name="midcom_admin_user_group" id="midcom_admin_user_group" style="display: none;"<?php echo $disabled; ?>>
                        <?php
                        foreach ($data['groups'] as $group)
                        {
                            if (!is_object($group))
                            {
                                continue;
                            }
                            echo "<option value=\"{$group->id}\">{$group->official}</option>\n";
                        }
                        ?>
                    </select>
                    <input type="submit" value="<?php echo $_MIDCOM->i18n->get_string('apply to selected', 'midcom.admin.user'); ?>"<?php echo $disabled; ?> />
                </td>
            </tr>
        </tfoot>
    </table>
    </form>
    <script type="text/javascript">
        // <![CDATA[
            $j('#midcom_admin_user_action').change(function()
            {
                if (this.value == 'passwords')
                {
                    $j('<div></div>')
                        .attr('id', 'midcom_admin_user_action_passwords')
                        .appendTo('#midcom_admin_user_batch_process');
                    
                    // Load the form for outputting the style
                    $j('#midcom_admin_user_action_passwords').load('&(prefix);__mfa/asgard_midcom.admin.user/password/batch/?ajax&timestamp=<?php echo time(); ?>');
                    
                    $j('#midcom_admin_user_batch_process').submit(function()
                    {
                        var action = '&(prefix);__mfa/asgard_midcom.admin.user/password/batch/?ajax';
                        $j(this).attr('action', action);
                    });
                }
                else
                {
                    $j('#midcom_admin_user_action_passwords').css('display', 'none');
                    
                    // Return the original submit functionality
                    $j('#midcom_admin_user_batch_process').submit(function()
                    {
                        var action = '&(prefix);__mfa/asgard_midcom.admin.user/';
                        $j(this).attr('action', action);
                        
                        return true;
                    });
                }
            });
            $j('#midcom_admin_user_batch_process table').tablesorter({sortList: [[2,0]]});
        // ]]>
    </script>
    <?php
}
?>
