<!-- show_order -->
<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<style>
td
{
    padding-right:15px;
}

hr
{
    color:#000000;
    border: 1px solid #000000;
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
<?php
for ($i=0; $i<2;$i++)
{

if ($i == 1)
{
    echo "<div style=\"page-break-before:always;\">\n";
}
else
{
    echo "<div>\n";
}

?>
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
<h3><?php echo $data['l10n']->get('products'); ?></h3>
<table cellpadding="3" cellspacing="2" border="0">
    <tr>
        <td><?php echo $data['l10n']->get('Product'); ?></td>
        <td><?php echo $data['l10n']->get('code'); ?></td>
        <td align="right"><?php echo $data['l10n']->get('Salesprice'); ?></td>
        <td align="right"><?php echo $data['l10n']->get('Quantity'); ?></td>
        <td align="right"><?php echo $data['l10n']->get('Sum'); ?></td>
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
        <td align="right">&(product['quantity']);</td>
        <td align="right"><?php echo str_replace('.', ',', str_replace('EUR', '', money_format('%i', $product['sum']))); ?></td>
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
</table>

<br /><br />
</div>
<?php

}
?>
<script>
window.print();


function changeLocation() {
     document.location.href="<?php echo $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX); ?>";
}

self.setTimeout('changeLocation()', 1000) 

</script>
<!-- / show_order -->
