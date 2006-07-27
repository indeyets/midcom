<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

// Available request data: type_list
?>

<h2><?php echo $data['topic']->extra . ': ' . $data['l10n']->get('submit new entry'); ?></h2>

<h3><?php $data['l10n_midcom']->show('search');?></h3>
<ul>
    <?php
    foreach ($data['type_list'] as $name => $config)
    {
        if (! $config["{$data['mode']}_schema"])
        {
            continue;
        }
        $url = $config["{$data['mode']}_create_url"];
        $label = $data['l10n']->get("submit {$data['mode']}");
        ?>
        <li>
            &(config['title']);&nbsp;&nbsp;
            <?php if ($url) { ?>
                <a href="&(url);">&(label);</a>&nbsp;&nbsp;
            <?php } else { ?>
                &(label);
            <?php } ?>
        </li>
    <?php } ?>
</ul>
