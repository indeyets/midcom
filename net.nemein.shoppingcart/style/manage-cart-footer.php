<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
            <tr class="actions">
                <td colspan="3">
                    <input type="submit" class="button update_cart" id="net_nemein_shoppingcart_manage_update" name="net_nemein_shoppingcart_managecart_update" value="<?php echo $data['l10n']->get('recalculate'); ?>" />
                    <input type="submit" class="button checkout" name="net_nemein_shoppingcart_managecart_checkout" value="<?php echo $data['l10n']->get('proceed to checkout'); ?>" />
                </td>
            </tr>
        </tfoot>
    </table>
</form>
