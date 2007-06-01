<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="main">
    <div class="area">
        <h2><?php echo $data['l10n']->get('org.openpsa.reports'); ?></h2>
    <?php
        foreach ($data['available_components'] as $component => $loc)
        {
            $parts = explode('.', $component);
            $last = array_pop($parts);
            $data['report_prefix'] = "{$prefix}{$last}/";
            echo "            <h3><a href=\"{$data['report_prefix']}\">{$loc}</a></h3>\n";
            midcom_show_style("show-{$component}-quick_reports");
        }
    ?>
    </div>
</div>