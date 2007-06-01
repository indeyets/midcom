<?php
// Available request keys:
// top_bids, top_asks
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2><?php echo $data['topic']->extra; ?></h2>

<table border="0" width="100%">
<tr>
<td width="50%" valign="top">
    <h3><?php $data['l10n']->show('latest bids');?></h3>
    <?php if ($data['top_bids']) { ?>
        <ul>
            <?php foreach ($data['top_bids'] as $entry) { ?>
                <li>
                    <a href="&(prefix);entry/view/&(entry.guid);.html">&(entry.title);</a>
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
<td width="50%" valign="top">
    <h3><?php $data['l10n']->show('latest asks');?></h3>
    <?php if ($data['top_asks']) { ?>
        <ul>
            <?php foreach ($data['top_asks'] as $entry) { ?>
                <li>
                    <a href="&(prefix);entry/view/&(entry.guid);.html">&(entry.title);</a>
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
