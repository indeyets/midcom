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