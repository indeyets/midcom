<?php
if (count($data['persons']) > 0)
{
    ?>
        </tbody>
        <tfoot>
            <tr>
                <td>&nbsp;</td>
                <td colspan="<?php echo count($data['list_fields']); ?>">
                    <select name="midcom_admin_user_action">
                        <option value=""><?php echo $_MIDCOM->i18n->get_string('choose action', 'midcom.admin.user'); ?></option>
                        <option value="removeaccount"><?php echo $_MIDCOM->i18n->get_string('remove account', 'midcom.admin.user'); ?></option>
                        <option value="groupadd" onclick="javascript:document.getElementById('midcom_admin_user_group').style.display='inline';"><?php echo $_MIDCOM->i18n->get_string('add to group', 'midcom.admin.user'); ?></option>
                    </select>
                    <select name="midcom_admin_user_group" id="midcom_admin_user_group" style="display: none;">
                        <?php
                        $qb = midcom_db_group::new_query_builder();
                        $groups = $qb->execute();
                        foreach ($groups as $group)
                        {
                            echo "<option value=\"{$group->id}\">{$group->official}</option>\n";
                        }
                        ?>
                    </select>
                    <input type="submit" value="<?php echo $_MIDCOM->i18n->get_string('apply to selected', 'midcom.admin.user'); ?>" />
                </td>
            </tr>
        </tfoot>
    </table>
    </form>
    <?php
}
?>