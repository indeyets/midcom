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
                <td colspan="<?php echo count($data['list_fields']) + 1; ?>">
                    <label for="select_all">
                        <input type="checkbox" name="select_all" id="select_all" value="" onclick="$j(this).check_all('#midcom_admin_user_batch_process table tbody');" /> <?php echo $_MIDCOM->i18n->get_string('select all', 'midcom.admin.user');; ?>
                    </label>
                    <label for="invert_selection">
                        <input type="checkbox" name="invert_selection" id="invert_selection" value="" onclick="$j(this).invert_selection('#midcom_admin_user_batch_process table tbody');" /> <?php echo $_MIDCOM->i18n->get_string('invert selection', 'midcom.admin.user');; ?>
                    </label>
                </td>
            </tr>
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
                        <option value="groupadd"><?php echo $_MIDCOM->i18n->get_string('add to group', 'midcom.admin.user'); ?></option>
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
            var active = null;
            $j('#midcom_admin_user_action').change(function()
            {
                if (active)
                {
                    $j(active).css({display: 'none'});
                }
                
                $j(this).attr('value');
                switch ($j(this).attr('value'))
                {
                    case 'passwords':
                        active = '#midcom_admin_user_action_passwords';
                        
                        if (document.getElementById('midcom_admin_user_action_passwords'))
                        {
                            $j('#midcom_admin_user_action_passwords').css({display:'block'});
                            break;
                        }
                        
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
                        break;
                    
                    case 'groupadd':
                        $j('#midcom_admin_user_group').css({display: 'inline'});
                        active = '#midcom_admin_user_group';
                        break;
                    
                    default:
                        active = null;
                        
                        // Return the original submit functionality
                        $j('#midcom_admin_user_batch_process').submit(function()
                        {
                            var action = '&(prefix);__mfa/asgard_midcom.admin.user/';
                            $j(this).attr('action', action);
                            
                            return true;
                        });
                }
            });
            $j('#midcom_admin_user_batch_process table').tablesorter(
            {
//                widgets: ['column_highlight'],
                sortList: [[2,0]]
            });
        // ]]>
    </script>
    <?php
}
?>
