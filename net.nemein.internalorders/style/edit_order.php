<!-- edit_order -->
<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
global $statuserrors;
global $statuserrors2;
global $statuserrors_focus;
?>
<style>

.clear_both
{
	display: block;
	clear: both;
}


label select, label input
{
	display:block;
	clear:both;
	float:left;
}

fieldset
{
	clear:both;
}

.radios label
{
    margin:0;
    padding:0;
    display:inline;
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

<script type="text/javascript">
 <!--
  var counter = 10;

  function add_field()
  {
      var newFields = document.getElementById('read_form_root').cloneNode(true);
      newFields.id = '';
      newFields.style.display = 'block';
      
      
//      var newField = newFields.getElementsByTagName('input');
		var newField = newFields.childNodes;
      for (i = 0; i < newField.length; i++)
      {
          if (newField[i].name)
          {
              var newName = 'net_nemein_internalorders_product' + '[' + counter + '][' + newField[i].name + ']';
              var newTempName = newField[i].name;
              if (newName)
              {
                  newField[i].name = newName;
                  newField[i].id = newTempName + '_' + counter;
//                  alert(newName);
              }
      //        alert(newName +"::"+ newTempName);
          }
      }
      var insertHere = document.getElementById('write_seed');
      insertHere.parentNode.insertBefore(newFields, insertHere);
      counter++;
  }
  
  window.onload = add_field;


function approve_form()
{
	tmp_bool = confirm("<?php echo "Haluatko varmasti lähettää lähetteen?"; ?>");
	if (tmp_bool)
	{
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_pricelist_approve.value = 1;
		document.forms['net_nemein_internalorders_form'].submit();
	}

}

function delete_form()
{
	tmp_bool = confirm("<?php echo "Haluatko varmasti poistaa lähetteen?"; ?>");
	if (tmp_bool)
	{
		document.location.href = '<?php echo $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."delete/".$data['event']->guid.".html" ?>';
	}
}

function refresh_form()
{
	document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_pricelist_refresh.value = 1;
	document.forms['net_nemein_internalorders_form'].submit();
}

function Left(str, n){	if (n <= 0)	    return "";	else if (n > String(str).length)	    return str;	else	    return String(str).substring(0,n);}function Right(str, n){    if (n <= 0)       return "";    else if (n > String(str).length)       return str;    else {       var iLen = String(str).length;       return String(str).substring(iLen, iLen - n);    }}


function openPopup(rowID, status)
{
	rowID_org = rowID;
	if (status == 2)
	{
	    tmp_str = rowID_org;
	    tmp_pos = tmp_str.indexOf(']');
		tmp_str = Left(tmp_str, tmp_pos);
	    tmp_pos = tmp_str.indexOf('[');
	    tmp_pos = tmp_str.length - tmp_pos - 1;
		rowID = Right(tmp_str, tmp_pos);
	}
	else
	{
//		alert(rowID);
		rowID = Right(Left(rowID, 36), 2);
		rowID = 'value_' + rowID;
//		alert(rowID);
	}

	var str = rowID_org;
	rowValue = str.replace(/search/, "value");

	var inputAtHand = document.getElementById(rowID);
	searchstring = inputAtHand.value;
	if (searchstring == "")
	{
		searchstring = "Etsi";
	}
	if (status == 2)
	{
		var inputAtHand = document.getElementById(rowID);
		inputAtHand.disabled = true;
		var inputAtHand = document.getElementById(rowID+'_quant');
		if(inputAtHand) { inputAtHand.disabled = true; }
		var inputAtHand = document.getElementById(rowID+'_title');
		if(inputAtHand) { inputAtHand.disabled = true; }
		var inputAtHand = document.getElementById(rowID+'_price');
		if(inputAtHand) { inputAtHand.disabled = true; }
		var inputAtHand = document.getElementById(rowID+'_remove');
		if(inputAtHand) { inputAtHand.disabled = true; }
	}
	else
	{
		rowID_tmp = Right(Left(rowID_org, 36),2);
		var inputAtHand = document.getElementById('value_'+rowID_tmp);
		if(inputAtHand) {inputAtHand.disabled = true; }
		var inputAtHand = document.getElementById('quantity_'+rowID_tmp);
		if(inputAtHand) {inputAtHand.disabled = true; }
	}
	window.open('search/?inputID='+rowID+'&inputName='+rowValue+'&search='+searchstring, 'search', 'width=550, height=450, scrollbars=1, toolbar=0, status=0');
}

function updateValue(inputID, newInputValue)
{
	var inputAtHand = document.getElementById(inputID);
	inputAtHand.value = newInputValue;
	openForm(inputID);
	refresh_form();
	closeForm(inputID);
}

function openForm(inputID)
{
	var inputAtHand = document.getElementById(inputID);
	inputAtHand.disabled = false;
	var inputAtHand = document.getElementById(inputID+'_quant');
	if(inputAtHand) { inputAtHand.disabled = false; }
	var inputAtHand = document.getElementById(inputID+'_title');
	if(inputAtHand) { inputAtHand.disabled = false; }
	var inputAtHand = document.getElementById(inputID+'_price');
	if(inputAtHand) { inputAtHand.disabled = false; }
	var inputAtHand = document.getElementById(inputID+'_remove');
	if(inputAtHand) { inputAtHand.disabled = false; }
	var inputAtHand = document.getElementById('value_'+Right(inputID, 2));
	if(inputAtHand) { inputAtHand.disabled = false; }
	var inputAtHand = document.getElementById('quantity_'+Right(inputID, 2));
	if(inputAtHand) { inputAtHand.disabled = false; }
	var inputAtHand = document.getElementById('quantity_'+inputID);
	if(inputAtHand) { inputAtHand.disabled = false; }
}

function closeForm(inputID)
{
        var inputAtHand = document.getElementById(inputID);
        inputAtHand.disabled = true;
        var inputAtHand = document.getElementById(inputID+'_quant');
        if(inputAtHand) { inputAtHand.disabled = true; }
        var inputAtHand = document.getElementById(inputID+'_title');
        if(inputAtHand) { inputAtHand.disabled = true; }
        var inputAtHand = document.getElementById(inputID+'_price');
        if(inputAtHand) { inputAtHand.disabled = true; }
        var inputAtHand = document.getElementById(inputID+'_remove');
        if(inputAtHand) { inputAtHand.disabled = true; }
        var inputAtHand = document.getElementById('value_'+Right(inputID, 2));
        if(inputAtHand) { inputAtHand.disabled = true; }
        var inputAtHand = document.getElementById('quantity_'+Right(inputID, 2));
	if(inputAtHand) { inputAtHand.disabled = true; }
	var inputAtHand = document.getElementById('quantity_'+inputID);
        if(inputAtHand) { inputAtHand.disabled = true; }
}



function check_reason_1(fieldInQuestion)
{
	if(document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_2[1].checked == false)
	{
		document.getElementById('reason_3_1').style.display = 'none';
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_3[0].checked = false;
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_3[1].checked = false;
	}
	if(document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_2[5].checked == false)
	{
		document.getElementById('reason_3_2').style.display = 'none';
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_3[2].checked = false;
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_3[3].checked = false;
	}
	if(fieldInQuestion == 1)
	{
		document.getElementById('reason_2_2').style.display = 'none';
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_2[2].checked = false;
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_2[3].checked = false;
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_2[4].checked = false;
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_2[5].checked = false;
		document.getElementById('reason_2_1').style.display = 'block';
	}
	else
	{
		document.getElementById('reason_2_1').style.display = 'none';
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_2[0].checked = false;
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_2[1].checked = false;
		document.getElementById('reason_2_2').style.display = 'block';
	}
}


function check_reason_2(fieldInQuestion)
{
	document.getElementById('reason_3_1').style.display = 'none';
	document.getElementById('reason_3_2').style.display = 'none';
		
	if(fieldInQuestion == 2)
	{
		document.getElementById('reason_3_1').style.display = 'block';
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_3[2].checked = false;
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_3[3].checked = false;
	}
	else if(fieldInQuestion == 6)
	{
		document.getElementById('reason_3_2').style.display = 'block';
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_3[0].checked = false;
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_3[1].checked = false;
	}
	else
	{
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_3[0].checked = false;
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_3[1].checked = false;
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_3[2].checked = false;
		document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_3[3].checked = false;

	}

}
function focusField(fieldToFocus, status_errors_len)
{
	if (Left(fieldToFocus, 12) == 'prods_quant_')
	{
		status_errors_len2 = status_errors_len-12;
		quant = Right(fieldToFocus, status_errors_len2)+"_quant";
		quant2 = document.getElementById(quant);
		quant2.focus();
	}
	else if(Left(fieldToFocus, 12) == 'prods_price_')
	{
		status_errors_len2 = status_errors_len-12;
		price = Right(fieldToFocus, status_errors_len2)+"_price";
		price2 = document.getElementById(price);
		price2.focus();
	}
	else if(Left(fieldToFocus, 9) == 'prods_id_')
	{
		status_errors_len2 = status_errors_len-9;
		price = Right(fieldToFocus, status_errors_len2);
		price2 = document.getElementById(price);
		price2.focus();
	}
	else
	{
		switch(fieldToFocus)
		{
			case 'cols':
				document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_colls.focus();
			break;
			case 'reason_2':
				document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_2[0].focus();
			break;
			case 'reason_1':
				document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_reason_1[0].focus();
			break;
			case 'receiver':
				document.forms['net_nemein_internalorders_form'].net_nemein_internalorders_receiver.focus();
			break;
		}
	}


}


 -->
</script>
<h1><?php echo sprintf($data['l10n']->get('edit %s'), $data['event']->title); ?></h1>
<div id="read_form_root" class="net_nemein_orders cell" style="display: none;">
	<input type="image" alt="Search" style="padding-left:2px; padding-right:11px; border:none;" src="/midcom-static/stock-icons/16x16/search.png" border="0" name="search" onclick="openPopup(this.name, 1); return false;" />
	<input type="text" class="net_nemein_internalorders cell_title" name="value" value="" style="margin-right:2px;" size="7" />
	<input type="text" class="net_nemein_internalorders cell_title" name="quantity" value="" size="7" />
</div>

<?php
if(strlen($statuserrors)>0)
{
  echo "<div style=\"color:red;\">";
  echo $statuserrors;
  echo "</div>";
}
?>
<form method="post" action="" name="net_nemein_internalorders_form">
	<fieldset>
		<legend><?php echo $data['l10n']->get('internal order'); ?></legend>
		<label>
			<?php echo $data['l10n']->get('order number'); ?>: <?php echo $data['event']->title; ?>
		</label>
		<style>
		#testdata
		{
			clear:both;
		    margin:20px;
		    border:2px solid #000000;
		    padding:10px;
		}
		
		</style>
<!--		<div id="testdata">
		<pre>
		<?php

		?>
		</pre>
		</div>-->
		
		<fieldset>
			<legend><?php echo $data['l10n']->get('basic information'); ?></legend>
			<table cellpadding="1" cellspacing="0" border="0">
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
					<td><?php echo $data['l10n']->get('receiver'); ?></td>
					<td><select name="net_nemein_internalorders_receiver">
						<option value="XX">--Valitse--</option>
			<?php
                                $group = mgd_get_object_by_guid($data['config']->get('user_group'));
				$persons_list = mgd_list_members($group->id);
				while( $persons_list->fetch() )
				{
					$tmp_person = mgd_get_person($persons_list->uid);
					if ($tmp_person->id != $_MIDGARD['user'])
					{
						if ($data['event']->extra == $tmp_person->id)
						{	$selected=" selected"; }
						else
						{	$selected=""; }
						echo "\t\t\t\t\t\t<option ".$selected." value=".$tmp_person->id.">".$tmp_person->name."</option>\n";
					}
				}
			?>
						</select>
					</td>
				</tr>
			</table>
			<br />
			<span style="float:left; height: 170px;" class="radios">
				<?php echo $data['l10n']->get('from to'); ?><br />
				<label onclick="check_reason_1('1')" for="net_nemein_internalorders_reason_1_1"><input type="radio" value="1" name="net_nemein_internalorders_reason_1" id="net_nemein_internalorders_reason_1_1" <?php if ($data['event']->parameter('net.nemein.internalorders', 'reason_1') == "1") { ?> checked<?php } ?> />&nbsp;<?php echo $data['l10n']->get('trans_reason_1_1'); ?></label><br />
				<label onclick="check_reason_1('2')" for="net_nemein_internalorders_reason_1_2"><input type="radio" value="2" name="net_nemein_internalorders_reason_1" id="net_nemein_internalorders_reason_1_2" <?php if ($data['event']->parameter('net.nemein.internalorders', 'reason_1') == "2") { ?> checked<?php } ?> />&nbsp;<?php echo $data['l10n']->get('trans_reason_1_2'); ?></label><br />
			</span>
			<span id="reason_2_1" style="float:left; margin-bottom:20px; margin-left:20px; <?php if ($data['event']->parameter('net.nemein.internalorders', 'reason_1') != "1" ) { ?> display:none;<?php } ?>" class="radios">
				<?php echo $data['l10n']->get('transfer reason'); ?><br />
				<label onclick="check_reason_2('1')" for="net_nemein_internalorders_reason_2_1"><input type="radio" value="1" name="net_nemein_internalorders_reason_2" id="net_nemein_internalorders_reason_2_1" <?php if ($data['event']->parameter('net.nemein.internalorders', 'reason_2') == "1") { ?> checked<?php } ?> />&nbsp;<?php echo $data['l10n']->get('trans_reason_2_1_1'); ?></label><br />
				<label onclick="check_reason_2('2')" for="net_nemein_internalorders_reason_2_2"><input type="radio" value="2" name="net_nemein_internalorders_reason_2" id="net_nemein_internalorders_reason_2_2" <?php if ($data['event']->parameter('net.nemein.internalorders', 'reason_2') == "2") { ?> checked<?php } ?> />&nbsp;<?php echo $data['l10n']->get('trans_reason_2_1_2'); ?></label><br />
			</span>
			<span id="reason_2_2" style="float:left; margin-bottom:20px; margin-left:20px; <?php if ($data['event']->parameter('net.nemein.internalorders', 'reason_1') != "2" ) { ?> display:none;<?php } ?>" class="radios">
				<?php echo $data['l10n']->get('transfer reason'); ?><br />
				<label onclick="check_reason_2('3')" for="net_nemein_internalorders_reason_2_3"><input type="radio" value="3" name="net_nemein_internalorders_reason_2" id="net_nemein_internalorders_reason_2_3" <?php if ($data['event']->parameter('net.nemein.internalorders', 'reason_2') == "3") { ?> checked<?php } ?> />&nbsp;<?php echo $data['l10n']->get('trans_reason_2_2_1'); ?></label><br />
				<label onclick="check_reason_2('4')" for="net_nemein_internalorders_reason_2_4"><input type="radio" value="4" name="net_nemein_internalorders_reason_2" id="net_nemein_internalorders_reason_2_4" <?php if ($data['event']->parameter('net.nemein.internalorders', 'reason_2') == "4") { ?> checked<?php } ?> />&nbsp;<?php echo $data['l10n']->get('trans_reason_2_2_2'); ?></label><br />
				<label onclick="check_reason_2('5')" for="net_nemein_internalorders_reason_2_5"><input type="radio" value="5" name="net_nemein_internalorders_reason_2" id="net_nemein_internalorders_reason_2_5" <?php if ($data['event']->parameter('net.nemein.internalorders', 'reason_2') == "5") { ?> checked<?php } ?> />&nbsp;<?php echo $data['l10n']->get('trans_reason_2_2_3'); ?></label><br />
				<label onclick="check_reason_2('6')" for="net_nemein_internalorders_reason_2_6"><input type="radio" value="6" name="net_nemein_internalorders_reason_2" id="net_nemein_internalorders_reason_2_6" <?php if ($data['event']->parameter('net.nemein.internalorders', 'reason_2') == "6") { ?> checked<?php } ?> />&nbsp;<?php echo $data['l10n']->get('trans_reason_2_2_4'); ?></label><br />
			</span>
			<span id="reason_3_1" style="float:left; margin-left:20px; <?php if ($data['event']->parameter('net.nemein.internalorders', 'reason_2') != "1") { ?> display:none;<?php } ?>" class="radios">
				<?php echo $data['l10n']->get('transfer reason 2'); ?><br />
				<label for="net_nemein_internalorders_reason_3_1"><input type="radio" value="1" name="net_nemein_internalorders_reason_3" id="net_nemein_internalorders_reason_3_1" <?php if ($data['event']->parameter('net.nemein.internalorders', 'reason_3') == "1") { ?> checked<?php } ?> />&nbsp;<?php echo $data['l10n']->get('trans_reason_3_1_1'); ?></label><br />
				<label for="net_nemein_internalorders_reason_3_2"><input type="radio" value="2" name="net_nemein_internalorders_reason_3" id="net_nemein_internalorders_reason_3_2" <?php if ($data['event']->parameter('net.nemein.internalorders', 'reason_3') == "2") { ?> checked<?php } ?> />&nbsp;<?php echo $data['l10n']->get('trans_reason_3_1_2'); ?></label><br />
			</span>
			<span id="reason_3_2" style="float:left; margin-left:20px;  <?php if ($data['event']->parameter('net.nemein.internalorders', 'reason_2') != "2") { ?> display:none;<?php } ?>" class="radios">
				<?php echo $data['l10n']->get('transfer reason 2'); ?><br />
				<label for="net_nemein_internalorders_reason_3_3"><input type="radio" value="3" name="net_nemein_internalorders_reason_3" id="net_nemein_internalorders_reason_3_3" <?php if ($data['event']->parameter('net.nemein.internalorders', 'reason_3') == "3") { ?> checked<?php } ?> />&nbsp;<?php echo $data['l10n']->get('trans_reason_3_2_1'); ?></label><br />
				<label for="net_nemein_internalorders_reason_3_4"><input type="radio" value="4" name="net_nemein_internalorders_reason_3" id="net_nemein_internalorders_reason_3_4" <?php if ($data['event']->parameter('net.nemein.internalorders', 'reason_3') == "4") { ?> checked<?php } ?> />&nbsp;<?php echo $data['l10n']->get('trans_reason_3_2_2'); ?></label><br />
			</span>
			<div class="clear_both"></div>
			<br />
			<label>
				<?php echo $data['l10n']->get('packing directions'); ?><br />
				<textarea rows="4" cols="60" name="net_nemein_internalorders_packing"><?php echo $data['event']->parameter('net.nemein.internalorders', 'packing'); ?></textarea>
			</label>
		</fieldset>
		<br /><br />
		<fieldset>
			<legend>Tuotteet</legend>
			<input style="float:left;" type="button" value="<?php echo $data['l10n']->get('add a field'); ?>" onClick="add_field();" />
			<input style="margin-left:30px; float:left;" name="refresh" onclick="refresh_form();" type="button" value="<?php echo $data['l10n']->get('Refresh'); ?>" /><br />
			<div class="clear_both">&nbsp;<br /></div>
			<table cellpadding="0" cellspacing="2" border="0">
				<thead>
				<tr>
					<td width="30">&nbsp;</td>
					<td width="70"><?php echo $data['l10n']->get('code'); ?></td>
					<td width="70"><?php echo $data['l10n']->get('Quantity'); ?></td>
					<td width="300"><?php echo $data['l10n']->get('Product'); ?></td>
					<td align="right" width="70"><?php echo $data['l10n']->get('Salesprice'); ?></td>
					<td align="right" width="70" style="padding-left:10px;"><?php echo $data['l10n']->get('Sum'); ?></td>
					<td style="padding-left:10px;" width="70"><img alt="Remove" src="/midcom-static/stock-icons/16x16/trash.png" border="0" /></td>
				</tr>
				</thead>
				<tbody>
			
<?php
setlocale(LC_MONETARY, 'fi_FI.UTF');
foreach ($data['products'] as $guid => $product)
{
	if(strlen($product['value']) == 7)
	{
?>
	<div class="net_nemein_orders cell">
		<tr>
		    <td><input type="image" alt="Search" style="padding-right:14px; border:none;" src="/midcom-static/stock-icons/16x16/search.png" border="0" name="net_nemein_internalorders_product[&(guid);][search]" onclick="openPopup(this.name, 2); return false;" /></td>
			<td><input type="text" id="&(guid);" class="net_nemein_internalorders cell_title" name="net_nemein_internalorders_product[&(guid);][value]" value="&(product['value']);" size="7" /></td>
			<td><input type="text" id="&(guid);_quant" class="net_nemein_internalorders cell_title" name="net_nemein_internalorders_product[&(guid);][quantity]" value="&(product['quantity']);" size="7" /></td>
			<td>&(product['title']);</td>
			<td align="right"><?php echo str_replace('.', ',', $product['salesprice']); ?></td>
			<td align="right" style="padding-left:10px;"><?php echo str_replace('.', ',', str_replace('EUR', '', money_format('%i', $product['sum']))); ?></td>
			<td style="padding-left:10px;"><input type="checkbox" id="&(guid);_remove" class="net_nemein_internalorders cell_title" name="net_nemein_internalorders_product[&(guid);][remove]" value="1" /></td>
		</tr>
	</div>
<?php
	}
	else
	{
?>
	<div class="net_nemein_orders cell">
		<tr>
			<td><input type="image" alt="Search" style="padding-right:14px; border:none;" src="/midcom-static/stock-icons/16x16/search.png" border="0" name="net_nemein_internalorders_product[&(guid);][search]" onclick="openPopup(this.name, 2); return false;" /></td>
			<td><input type="text" id="&(guid);" class="net_nemein_internalorders cell_title" name="net_nemein_internalorders_product[&(guid);][value]" value="&(product['value']);" size="7" /></td>
			<td><input type="text" id="&(guid);_quant" class="net_nemein_internalorders cell_title" name="net_nemein_internalorders_product[&(guid);][quantity]" value="&(product['quantity']);" size="7" /></td>
			<td><input type="text" id="&(guid);_title" class="net_nemein_internalorders cell_title" name="net_nemein_internalorders_product[&(guid);][title]" value="&(product['title']);" size="33" /></td>
			<td align="right"><input id="&(guid);_price" type="text" class="net_nemein_internalorders cell_title" name="net_nemein_internalorders_product[&(guid);][salesprice]" value="&(product['salesprice']);" size="7" /></td>
			<td align="right" style="padding-left:10px;"><?php echo str_replace('.', ',', str_replace('EUR', '', money_format('%i', $product['sum']))); ?></td>
			<td style="padding-left:10px;"><input type="checkbox" id="&(guid);_remove" class="net_nemein_internalorders cell_title" name="net_nemein_internalorders_product[&(guid);][remove]" value="1" /></td>
		</tr>
	</div>
<?php	
	}
}
?>
				
				</tbody>
			</table>

			<div id="write_seed"></div><br /><br />
			<input type="button" value="<?php echo $data['l10n']->get('add a field'); ?>" onClick="add_field();" />
		</fieldset>
<br /><br />
		<fieldset>
			<legend>Lis&auml;tiedot</legend>
			<label style="float:left;">
				<?php echo $data['l10n']->get('packer'); ?>
				<input style="width:250px;" type="text" name="net_nemein_internalorders_packer" value="<?php echo $data['event']->parameter('net.nemein.internalorders', 'packer'); ?>" />
			</label>
			<label style="float:left;margin-left:10px;">
				<?php echo $data['l10n']->get('colls'); ?>
				<input style="width:250px;" type="text" name="net_nemein_internalorders_colls" value="<?php echo $data['event']->parameter('net.nemein.internalorders', 'colls'); ?>" />
			</label>
			<div class="clear_both"><br /><br /><br /></div>
			<label style="float:left;">
				<?php echo $data['l10n']->get('m3'); ?>
				<input style="width:250px;" type="text" name="net_nemein_internalorders_m3" value="<?php echo $data['event']->parameter('net.nemein.internalorders', 'm3'); ?>" />
			</label>
			<label style="float:left;margin-left:10px;">
				<?php echo $data['l10n']->get('sendentry'); ?>
				<input style="width:250px;" type="text" name="net_nemein_internalorders_sendentry" value="<?php echo $data['event']->parameter('net.nemein.internalorders', 'sendentry'); ?>" />
			</label>
		</fieldset>
		<input type="hidden" value="0" name="net_nemein_internalorders_pricelist_approve" id="net_nemein_internalorders_pricelist_approve" />
		<input type="hidden" value="0" name="net_nemein_internalorders_pricelist_refresh" id="net_nemein_internalorders_pricelist_refresh" />
		<br /><br />
		<input type="hidden" name="net_nemein_internalorders_pricelist_update" value="1" />
		<input style="float:left;" type="submit" value="<?php echo $data['l10n']->get('submit'); ?>" /><input style="margin-left:30px; float:left;" name="approve" onclick="approve_form();" type="button" value="<?php echo $data['l10n']->get('Approve'); ?>" /><input style="margin-left:30px; float:left;" type="button" value="<?php echo $data['l10n']->get('delete'); ?>" onclick="delete_form();" />
	</fieldset>
<p>
<strong>Sis&auml;inen siirto -lomaketta k&auml;ytet&auml;&auml;n siirrett&auml;ess&auml;</strong><br />
<strong>a) vaihto-omaisuutta</strong><br />
- toimipaikasta toiseen<br />
- ostosta jakelukeskukseen<br />
<strong>b) n&auml;ytteit&auml;</strong><br />
- toimipaikasta ostoon<br />
- ostosta Jakelukeskukseen tai tavarataloihin<br />
<strong>Kirjauslajeina</strong> 2 ja 5<br />
<strong>Arkistointiaika</strong> kuluva + 1 vuosi, vastaanottaja ja l&auml;hett&auml;j&auml; arkistoivat lomakkeen paperitulosteen kirjausp&auml;iv&auml;n mukaan.
</p>

<script>
<?php
if ($statuserrors_focus != "")
{
	$statuserrors_focus_len = strlen($statuserrors_focus);
	echo "focusField('".$statuserrors_focus."', ".$statuserrors_focus_len.");\n";
}
?>
<?php
if(strlen($statuserrors2)>0)
{
  echo "alert('".$statuserrors2."');\n";
}
?>
</script>

</form>
<!-- / edit_order -->
