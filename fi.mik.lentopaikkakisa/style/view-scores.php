<?php
// Available request keys: controller, schema, schemadb
$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1><?php echo $data['view_title']; ?></h1>

<table class="scores">
    <tbody>
    <?php
    foreach ($data['scores'] as $owner => $score)
    {
        if (   $data['total'] == 0
            || $score == 0)
        {
            $percentage = 0;
        }
        else
        {
            $percentage = 100 / $data['total'] * $score;
        }
        echo "<tr>\n";
        echo "<td>{$owner}</td>\n";
        echo "<td>{$score}</td>\n";
        echo "<td style=\"width: 200px; white-space: nowrap;\">";
        echo "<img src=\"".MIDCOM_STATIC_URL."/fi.mik.lentopaikkakisa/bar-left.jpg\" />";
        echo "<img src=\"".MIDCOM_STATIC_URL."/fi.mik.lentopaikkakisa/bar-center.jpg\" style=\"width: {$percentage}%; height: 24px;\" />";
        echo "<img src=\"".MIDCOM_STATIC_URL."/fi.mik.lentopaikkakisa/bar-right.jpg\" />";
        echo "</tr>\n";
    }
    ?>
        <tr class="totals">
            <td><?php echo $data['l10n']->get('total'); ?></td>
            <td><?php echo $data['total']; ?></td>
            <td></td>
        </tr>
    </tbody>
</table>