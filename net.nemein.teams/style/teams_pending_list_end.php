<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
</ul>
<?php
if ($data['total_pending'] > 0)
{
?>
<br/>
<input type="submit" name="approve_pending" value="<?php echo $data['l10n']->get('approve'); ?>"/>
<input type="submit" name="decline_pending" value="<?php echo $data['l10n']->get('decline'); ?>"/>
<?php
}
?>
</form>
