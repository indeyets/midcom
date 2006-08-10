<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1><?php echo $data['view_title']; ?></h1>

<?php
if (count($data['groups']) > 0)
{
    echo "<h2>". $data['l10n']->get('groups') ."</h2>\n";
    echo "<ul class=\"groups\">\n";
    foreach ($data['groups'] as $group)
    {
        echo "<li><a href=\"{$prefix}{$group->guid}/\">{$group->code}: {$group->title}</a></li>\n";
    }
    echo "</ul>\n";
}

if (count($data['products']) > 0)
{
    echo "<h2>". $data['l10n']->get('products') ."</h2>\n";
    ?>
    <table>
        <thead>
            <tr>
                <th><?php echo $data['l10n']->get('code'); ?></th>
                <th><?php echo $data['l10n_midcom']->get('title'); ?></th>
                <!-- TODO: Show supplier etc -->
            </tr>
        </thead>
        <tbody>
        <?php
        foreach ($data['products'] as $product)
        {
            ?>
            <tr>
                <td><a href="&(prefix);product/&(product.guid);.html">&(product.code:h);</a></td>
                <td><a href="&(prefix);product/&(product.guid);.html">&(product.title:h);</a></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <?php
}
?>