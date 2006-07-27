<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2><?php echo $data['topic']->extra; ?></h2>

<table border="0" width="100%">
<tr>
<td width="34%" valign="top">
    <h3><?php $data['l10n_midcom']->show('search');?></h3>
    <ul>
        <?php foreach ($data['type_list'] as $name => $config) { ?>
            <li>
                &(config['title']);&nbsp;&nbsp;
                <?php
                if ($config['offer_schema'])
                {
                    if ($config['offer_search_url'])
                    {
                        ?>
                        <a href="&(config['offer_search_url']);"><?php $data['l10n']->show('offers'); ?></a>&nbsp;&nbsp;
                        <?php
                    }
                    else
                    {
                        echo $data['l10n']->get('offers') . '&nbsp;&nbsp;';
                    }
                }
                if ($config['application_schema'])
                {
                    if ($config['application_search_url'])
                    {
                        ?>
                        <a href="&(config['application_search_url']);"><?php $data['l10n']->show('applications'); ?></a>
                        <?php
                    }
                    else
                    {
                        echo $data['l10n']->get('applications') . '&nbsp;&nbsp;';
                    }
                }
                ?>
            </li>
        <?php } ?>
    </ul>
</td>
<td width="33%" valign="top">
    <h3><?php $data['l10n']->show('latest offers');?></h3>
    <?php if ($data['top_offers']) { ?>
        <ul>
            <?php foreach ($data['top_offers'] as $offer) { ?>
                <li>
                    <a href="&(prefix);entry/view/&(offer.guid);.html">&(offer.title);</a>
                </li>
            <?php } ?>
        </ul>
    <?php
    }
    else
    {
        $data['l10n']->show('none found');
    }
    ?>
</td>
<td width="33%" valign="top">
    <h3><?php $data['l10n']->show('latest applications');?></h3>
    <?php if ($data['top_applications']) { ?>
        <ul>
            <?php foreach ($data['top_applications'] as $application) { ?>
                <li>
                    <a href="&(prefix);entry/view/&(application.guid);.html">&(application.title);</a>
                </li>
            <?php } ?>
        </ul>
    <?php
    }
    else
    {
        $data['l10n']->show('none found');
    }
    ?>
</td>
</tr>
</table>
