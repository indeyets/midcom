<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<label for="net_nemein_hourview_comments2"><?php echo $data['l10n']->get("comments"); ?>
    <textarea id="net_nemein_hourview2_comments" name="net_nemein_hourview2_comments" class="longtext"></textarea>
</label>
<div class="form_toolbar">
    <input type="submit" name="net_nemein_hourview2_submit" value="<?php echo $data['l10n']->get("send"); ?>" />
</div>
</form>