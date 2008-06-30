<?php
// Available request keys:
// event, view_url, edit_url, delete_url
// registrations

//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
        </tbody>
    </table>
    <input type="submit" class="process save" accesskey="s" value="<?php echo $data['l10n_midcom']->get('save'); ?>" />
</form>

<p><a href="&(data['view_url']);"><?php $data['l10n_midcom']->show('back'); ?></a></p>
