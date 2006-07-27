<?php
// Bind the view data, remember the reference assignment:
$view =& $_MIDCOM->get_custom_context_data('request_data');

$img_base = MIDCOM_STATIC_URL . '/stock-icons/16x16/';
?>
<tr>
  <td align='left' class='maildate' nowrap='nowrap'>&nbsp;</td>
  <td align='left' class='mailsender'>&nbsp;</td>
  <td align='left' class='mailsubject'>&nbsp;</td>
  <td align='center' class='mailcommands' nowrap='nowrap'>
    <input type="submit"
           name="&(view['delete_submit_button_name']);"
           value="<?php $view['l10n']->show('delete selected'); ?>"
    />
  </td>
</tr>
<tr>
  <td align='left' class='maillegendheader' valign='top'><?php $view['l10n']->show('legend:'); ?></td>
  <td align='left' class='maillegendcontent' valign='top' colspan='3'>
    <img src='&(img_base);stock_mail.png'> <?php $view['l10n']->show('unread mail'); ?><br />
    <img src='&(img_base);stock_mail-open.png'> <?php $view['l10n']->show('read mail'); ?><br />
    <img src='&(img_base);stock_mail-replied.png'> <?php $view['l10n']->show('replied mail'); ?><br />
    <img src='&(img_base);stock_mail-send.png'> <?php $view['l10n']->show('new mail to user'); ?><br />
    <img src='&(img_base);stock_mail-reply.png'> <?php $view['l10n']->show('reply mail'); ?><br />
  </td>
</tr>
</table>
</form>