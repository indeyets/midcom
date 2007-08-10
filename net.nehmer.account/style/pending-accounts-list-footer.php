    </table>
    <div class="form_toolbar">
        <input class="approve" type="submit" name="f_submit" value="<?php echo $data['l10n_midcom']->get('approve'); ?>" />
        <input class="disapprove" type="submit" name="f_mass_reject" value="<?php echo $data['l10n']->get('reject'); ?>" />
        <input class="cancel" type="submit" name="f_cancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
    </div>
</form>

<script type="text/javascript">
    // <![CDATA[
        var image_path = "<?php echo MIDCOM_STATIC_URL; ?>/net.nehmer.account/";
        var image_up = "arrow-up.gif";
        var image_down = "arrow-down.gif";
        var image_none = "arrow-none.gif";
    // ]]>
</script>
