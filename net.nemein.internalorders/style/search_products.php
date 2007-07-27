<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>Anttila</title>
<script>
statusForOpening = 0;


function openForm()
{
	if(statusForOpening == 0)
	{
		window.top.opener.openForm('<?php echo $_GET['inputID'] ?>');
	}
}

function changeStatus()
{
	statusForOpening = 1;
}
</script>
<style>
body
{
    background: #FFFFFF;
    font-size: 12px;
    font-family: Verdana, Arial;
    line-height: 16px;
    color: #333333;
}

input
{
    background: #FFFFFF;
    font-size: 12px;
    font-family: Verdana, Arial;
    line-height: 16px;
    color: #333333;
}

h1
{
    font-size: 1.2em;
}

h2
{
    font-size: 1.1em;
}
</style>
</head>
  
<body onload="window.focus();" onunload="openForm();">
<!-- edit_order -->
<?php
setlocale(LC_MONETARY, 'fi_FI.UTF');
//$data =& $_MIDCOM->get_custom_context_data('request_data');
global $statuserrors;
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

#internalorders_search
{
	width:400px;
	margin:30px;
}

</style>
<div id="internalorders_search">
<form method="get" action="">
	<input type="hidden" value="<?php echo $_GET['inputID'] ?>" name="inputID" />
	<input type="text" name="search" value="<?php echo $data['searchinput']; ?>" />
	<input type="submit" value="hae" onclick="changeStatus();" />
<!--	<input type="button" value="test" onclick="changeStatus(); window.top.opener.updateValue('<?php echo $_GET['inputID'] ?>', document.forms[0].search.value);" /> -->
</form>
<br /><br />
<?php
if(strstr(strtolower($data['searchinput']), 'yte'))
{
?>

<h2>Tuoteryhm&auml;t</h2>
<table cellpadding="0" cellspacing="2" border="0">
<tr>
	<td width="100"><strong>Koodi</strong></td>
	<td width="200"><strong>Tuote</strong></td>
	<td width="70"><strong>TR<strong></td>
</tr>
<?php
foreach($data['tr'] as $product)
{
		echo "<tr>\n";
		echo "<td align=\"left\"><a href=\"?inputID=".$_GET['inputID']."&search=".$product->code."&nayte=1\" onclick=\"changeStatus();\">".$product->code."</td>\n";
		echo "<td align=\"left\">".$product->title."</td>\n";
		echo "</tr>\n";
}
?>
</table>

<?php
}
elseif(isset($_GET['nayte']) && $_GET['nayte']= '1')
{
?>

<h2>Alaryhm&auml;t</h2>
<table cellpadding="0" cellspacing="2" border="0">
<tr>
	<td width="100"><strong>Koodi</strong></td>
	<td width="200"><strong>Tuote</strong></td>
</tr>
<?php
foreach($data['ar'] as $product)
{
	echo "<tr>\n";
	echo "<td align=\"left\"><a href=\"#\" onclick=\"changeStatus(); window.top.opener.updateValue('".$_GET['inputID']."', '".$_GET['search']."".$product->code."'); window.close();\">".$product->code."</td>\n";
	echo "<td align=\"left\">".$product->title."</td>\n";
	echo "</tr>\n";
}
?>
</table>

<?php
}
else
{
if(count($data['ar']) > 0)
{
?>
<h2>Tuoteryhm&auml;t</h2>
<table cellpadding="0" cellspacing="2" border="0">
<tr>
	<td width="100"><strong>Koodi</strong></td>
	<td width="200"><strong>Nimi</strong></td>
</tr>
<?php
foreach($data['ar'] as $product)
{
	echo "<tr>\n";
	echo "<td align=\"left\"><a href=\"#\" onclick=\"changeStatus(); window.top.opener.updateValue('".$_GET['inputID']."', '". $data['searchinput'] . $product->code."'); window.close();\">".$product->code."</td>\n";
	echo "<td align=\"left\">".$product->title."</td>\n";
	echo "</tr>\n";
}
?>
</table>
<?php
}
if(count($data['products']) > 0 || count($data['products2']) > 0)
{
?>
<h2>Tuotteet</h2>
<table cellpadding="0" cellspacing="2" border="0">
<tr>
	<td width="100"><strong>Koodi</strong></td>
	<td width="200"><strong>Tuote</strong></td>
	<td><strong>Ostohinta</strong></td>
	<td width="50" align="right"><strong>TR</strong></td>
	<td width="50" align="right"><strong>AR</strong></td>
</tr>
<?php
if(count($data['products']) > 0)
{
foreach($data['products'] as $product)
{
	echo "<tr>\n";
	echo "<td align=\"left\"><a href=\"#\" onclick=\"changeStatus(); window.top.opener.updateValue('".$_GET['inputID']."', '".$product->code."'); window.close();\">".$product->code."</td>\n";
	echo "<td align=\"left\">".$product->title."</td>\n";
	echo "<td align=\"right\">".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $product->price)))."</td>\n";
	$QB_ARs = org_openpsa_products_product_group_dba::new_query_builder();
	$QB_ARs->add_constraint('id', '=', $product->productGroup);
	$QB_ARs->add_order('code', 'ASC');
	$ARs = $QB_ARs->execute();

	$QB_TRs = org_openpsa_products_product_group_dba::new_query_builder();
	$QB_TRs->add_constraint('id', '=', $ARs[0]->up);
	$QB_TRs->add_order('code', 'ASC');
	$TRs = $QB_TRs->execute();
	echo "<td align=\"right\">".$TRs[0]->code."</td>\n";
	echo "<td align=\"right\">".$ARs[0]->code."</td>\n";
	echo "</tr>\n";
}
}
if(count($data['products2']) > 0)
{
//print_r($data['products2']);
foreach($data['products2'] as $product_group_id =>$product_group)
{
	foreach($data['products2'][$product_group_id] as $product)
	{
	echo "<tr>\n";
	echo "<td align=\"left\"><a href=\"#\" onclick=\"changeStatus(); window.top.opener.updateValue('".$_GET['inputID']."', '".$product->code."'); window.close();\">".$product->code."</td>\n";
	echo "<td align=\"left\">".$product->title."</td>\n";
	echo "<td align=\"right\">".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $product->price)))."</td>\n";
	$QB_ARs = org_openpsa_products_product_group_dba::new_query_builder();
	$QB_ARs->add_constraint('id', '=', $product->productGroup);
	$QB_ARs->add_order('code', 'ASC');
	$ARs = $QB_ARs->execute();

	$QB_TRs = org_openpsa_products_product_group_dba::new_query_builder();
	$QB_TRs->add_constraint('id', '=', $ARs[0]->up);
	$QB_TRs->add_order('code', 'ASC');
	$TRs = $QB_TRs->execute();
	echo "<td align=\"right\">".$TRs[0]->code."</td>\n";
	echo "<td align=\"right\">".$ARs[0]->code."</td>\n";
	echo "</tr>\n";
	}
}
}
?>
</table>
<?php
}
}
?>


</div>
