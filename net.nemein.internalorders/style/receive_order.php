<!-- receive_order -->
<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<style>
.clear_both
{
	display: block;
	clear: both;
}
td
{
	padding-right:15px;
}

textarea
{
	border:1px solid #000000;
}

input
{
	border:1px solid #000000;
}
</style>
<script>
function approve_form()
{
	tmp_bool = confirm("<?php echo "Haluatko varmasti vahvistaa vastaanoton?"; ?>");
	if (tmp_bool)
	{
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_pricelist_approve.value = 1;
		document.forms['net_nemein_internalorders_form'].submit();
	}

}
</script>
<a href="<?php echo $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX); ?>">&lt;-- Back to listing</a>
<br /><br /><form method="post" action="" name="net_nemein_internalorders_form">
<h1>L&auml;hete: <?php echo $data['event']->title; ?></h1>
<h3><?php echo $data['l10n']->get('basic information'); ?></h3>
<table cellpadding="0" border="0" cellspacing="0">
	<tr>
		<td><?php echo $data['l10n']->get('handler'); ?>:</td>
		<td><?php
			$person = mgd_get_person($data['event']->creator);
			echo $person->name;
		?></td>
	</tr>
	<tr>
		<td><?php echo $data['l10n']->get('date'); ?>:</td>
		<td><?php echo date("d.m.Y G:i", $data['event']->start); ?></td>
	</tr>
	<tr>
		<td><?php echo $data['l10n']->get('receiver'); ?>:</td>
		<td><?php
			$person = mgd_get_person($data['event']->extra);
			echo $person->name;
		?></td>
	</tr>
	<tr>
		<td><?php echo $data['l10n']->get('from to'); ?>:</td>
		<td><?php 
switch ($data['event']->parameter('net.nemein.internalorders', 'reason_1'))
{
	case "1":
		echo $data['l10n']->get('trans_reason_1_1');
		break;
	case "2":
		echo $data['l10n']->get('trans_reason_1_2');
		break;
	case "3":
		echo $data['l10n']->get('trans_reason_1_3');
		break;
	case "4":
		echo $data['l10n']->get('trans_reason_1_4');
		break;
	default:
		echo "No reason";
		break;
}
?></td>
	</tr>
	<tr>
		<td><?php echo $data['l10n']->get('transfer reason'); ?>:</td>
		<td><?php 
switch ($data['event']->parameter('net.nemein.internalorders', 'reason_2'))
{
	case "1":
		echo $data['l10n']->get('trans_reason_2_1');
		break;
	case "2":
		echo $data['l10n']->get('trans_reason_2_2');
		break;
	case "3":
		echo $data['l10n']->get('trans_reason_2_3');
		break;
	case "4":
		echo $data['l10n']->get('trans_reason_2_4');
		break;
	case "5":
		echo $data['l10n']->get('trans_reason_2_5');
		break;

	default:
		echo "No reason";
		break;
}
 ?></td>
	</tr>
	<tr>
		<td><?php echo $data['l10n']->get('packing directions'); ?>:</td>
		<td><?php echo $data['event']->parameter('net.nemein.internalorders', 'packing'); ?></td>
	</tr>
</table>
<input type="button" style="float:right; margin-top:15px; margin-right:30px;" onclick="receiveAll();" value="<?php echo $data['l10n']->get('Receive all'); ?>" /><h3><?php echo $data['l10n']->get('products'); ?></h3>
<div class="clear_both"></div>
<script>
function Left(str, n){	if (n <= 0)	    return "";	else if (n > String(str).length)	    return str;	else	    return String(str).substring(0,n);}function Right(str, n){    if (n <= 0)       return "";    else if (n > String(str).length)       return str;    else {       var iLen = String(str).length;       return String(str).substring(iLen, iLen - n);    }}

function receiveAll()
{
	var newFields = document.getElementById('products').cloneNode(true);
	var tmp = newFields.getElementsByTagName('input');
	tmpvalue = 0;
	for (i = 0; i < tmp.length; i++)
	{
    	if ((Left(tmp[i].name, 33) == "net_nemein_internalorders_product") && (Right(tmp[i].name, 10) == "[quantity]"))
    	{
			tmpvalue = tmp[i].value;
		}
		if ((Left(tmp[i].name, 33) == "net_nemein_internalorders_product") && (Right(tmp[i].name, 19) == "[quantity_received]") && (tmpvalue != 0))
    	{
    		document.getElementById(tmp[i].id).value = tmpvalue;
			tmpvalue = 0;
		}
	}
}

</script>

<table cellpadding="3" cellspacing="2" border="0" id="products">
	<tr>
		<td><?php echo $data['l10n']->get('Product'); ?></td>
		<td><?php echo $data['l10n']->get('code'); ?></td>
		<td align="right"><?php echo $data['l10n']->get('Salesprice'); ?></td>
		<td align="right"><?php echo $data['l10n']->get('Quantity'); ?></td>
		<td align="right"><?php echo $data['l10n']->get('Sum'); ?></td>
		<td><?php echo $data['l10n']->get('Received'); ?></td>
		<td><?php echo $data['l10n']->get('Additional'); ?></td>
	</tr>

<?php
setlocale(LC_MONETARY, 'fi_FI.UTF');
foreach ($data['products'] as $guid => $product)
{
?>
	<tr>
		<td>&(product['title']);</td>
		<td>&(product['value']);</td>
		<td align="right"><?php echo str_replace('.', ',', str_replace('EUR', '', money_format('%i', $product['salesprice']))); ?></td>
		<td align="right">&(product['quantity']);<input type="hidden" name="net_nemein_internalorders_product[&(guid);][quantity]" value="&(product['quantity']);" /></td>
		<td align="right"><?php echo str_replace('.', ',', str_replace('EUR', '', money_format('%i', $product['sum']))); ?></td>
		<td><input type="text" class="net_nemein_internalorders cell_title" id="net_nemein_internalorders_product[&(guid);][quantity_received]" name="net_nemein_internalorders_product[&(guid);][quantity_received]" value="&(product['quantity_received']);" size="5" /></td>
		<td><input type="text" class="net_nemein_internalorders cell_title" id="net_nemein_internalorders_product[&(guid);][additional]" name="net_nemein_internalorders_product[&(guid);][additional]" value="&(product['additional']);" size="5" /></td>
	</tr>
<?php
}
?>
</table>
<h3><?php echo $data['l10n']->get('additional info'); ?></h3>
<table cellpadding="0" border="0" cellspacing="0">
	<tr>
		<td><?php echo $data['l10n']->get('packer'); ?>:</td>
		<td><?php echo $data['event']->parameter('net.nemein.internalorders', 'packer'); ?></td>
	</tr>
	<tr>
		<td><?php echo $data['l10n']->get('colls'); ?>:</td>
		<td><?php echo $data['event']->parameter('net.nemein.internalorders', 'colls'); ?></td>
	</tr>
	<tr>
		<td><?php echo $data['l10n']->get('m3'); ?>:</td>
		<td><?php echo $data['event']->parameter('net.nemein.internalorders', 'm3'); ?></td>
	</tr>
	<tr>
		<td><?php echo $data['l10n']->get('sendentry'); ?>:</td>
		<td><?php echo $data['event']->parameter('net.nemein.internalorders', 'sendentry'); ?></td>
	</tr>
	<tr>
		<td><?php echo $data['l10n']->get('senddate'); ?>:<br />
		<td><?php echo date("d.m.Y G:i", $data['event']->parameter('net.nemein.internalorders', 'senddate')); ?></td>
	</tr>
	<tr>
		<td valign="top"><?php echo $data['l10n']->get('receivenotes'); ?>:<br />
		<td><textarea rows="4" cols="60" name="net_nemein_internalorders_receivenotes"><?php echo $data['event']->parameter('net.nemein.internalorders', 'receivenotes'); ?></textarea></td>
	</tr>
</table>

		<br />
		<input type="hidden" value="0" name="net_nemein_internalorders_pricelist_approve" id="net_nemein_internalorders_pricelist_approve" />
		<br />
		<input type="hidden" name="net_nemein_internalorders_pricelist_update" value="1" />
		<input style="float:left;" type="submit" value="<?php echo $data['l10n']->get('submit'); ?>" /><input style="margin-left:30px; float:left;" name="approve" onclick="approve_form();" type="button" value="<?php echo $data['l10n']->get('Approve'); ?>" />
</form>
<br /><br />
<a href="<?php echo $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX); ?>">&lt;-- <?php echo $data['l10n']->get('Back to listing'); ?></a>
<!-- / receive_order -->
