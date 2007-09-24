<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
</ul>
<br/>
<input type="submit" name="approve_pending" value="<?php echo $data['l10n']->get('approve'); ?>"/>
<input type="submit" name="decline_pending" value="<?php echo $data['l10n']->get('decline'); ?>"/>
</form>
