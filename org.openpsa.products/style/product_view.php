<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['view_product'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1>&(view['code']:h); &(view['title']:h);</h1>

<table>
    <tbody>
        <tr>
            <td><?php echo $data['l10n']->get('delivery type'); ?></td>
            <td>&(view['delivery']:h);</td>
        </tr>
        <tr>
            <td><?php echo $data['l10n']->get('type'); ?></td>
            <td>&(view['orgOpenpsaObtype']:h);</td>
        </tr>
        <tr>
            <td><?php echo $data['l10n']->get('price'); ?></td>
            <td>&(view['price']:h); / &(view['unit']:h);</td>
        </tr>
        <!-- TODO: Show supplier, etc -->
    </tbody>
</table>

&(view['description']:h);

<?php
if ($data['enable_components'])
{
    ?>
    &(view['components']:h);
    <?php
}
?>