<?php
// $data =& $_MIDCOM->get_custom_context_data('request_data');
?>
    </ul>
    </div>
    <input type="submit" name="f_submit" value="<?php echo $_MIDCOM->i18n->get_string('save', 'midcom'); ?>" />
    <input type="submit" name="f_cancel" value="<?php echo $_MIDCOM->i18n->get_string('cancel', 'midcom'); ?>" />
</form>
<script type="text/javascript">
    // <!--
        initialize_ordering();
        var l10n_tooltip_doubleclick = '<?php echo $data['l10n']->get('double click to edit'); ?>';
        var l10n_tooltip_drag_n_drop = '<?php echo $data['l10n']->get('drag and drop to sort'); ?>';
        var edit_button_src = '<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/edit.png';
        var save_button_src = '<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/save.png';
        var cancel_button_src = '<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/cancel.png';
    // -->
</script>
