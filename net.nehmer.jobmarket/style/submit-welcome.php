<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

// Available request data: type_list
?>

<h2><?php echo $data['topic']->extra . ': ' . $data['l10n']->get('submit new entry'); ?></h2>

<h3><?php $data['l10n_midcom']->show('search');?></h3>
<ul>
    <?php foreach ($data['type_list'] as $name => $config) { ?>
        <li>
            &(config['title']);&nbsp;&nbsp;
            <?php
            if ($config['offer_schema'])
            {
                if ($config['offer_create_url'])
                {
                    ?>
                    <a href="&(config['offer_create_url']);"><?php $data['l10n']->show('submit offer'); ?></a>&nbsp;&nbsp;
                <?php
                }
                else
                {
                    echo $data['l10n']->get('submit offer') . '&nbsp;&nbsp;';
                }
            }
            if ($config['application_schema'])
            {
                if ($config['application_create_url'])
                {
                    ?>
                    <a href="&(config['application_create_url']);"><?php $data['l10n']->show('submit application'); ?></a>
                <?php
                }
                else
                {
                    echo $data['l10n']->get('submit application') . '&nbsp;&nbsp;';
                }
            }
            ?>
        </li>
    <?php } ?>
</ul>
