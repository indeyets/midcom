<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
        </ul>
        <label for="auto_approval">
            <input id="auto_approval" type="checkbox" name="approvals" /> <?php echo $data['l10n']->get('approve automatically'); ?>
        </label>
        <br /><br />
        <input type="submit" name="f_submit" value="<?php echo $data['l10n_midcom']->get('save'); ?>" />
        <input type="submit" name="f_cancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
    </form>
    <script type="text/javascript">
        <!--
            var edit_icon_src = '<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/edit.png';
            var save_button_src = '<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/save.png';
            var cancel_button_src = '<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/cancel.png';
            initialize_ordering();
        -->
    </script>
</div>