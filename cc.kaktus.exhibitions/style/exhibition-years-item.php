<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h2><a href="&(prefix);&(data['year']);/"><?php echo sprintf($data['l10n']->get('year %s'), $data['year']); ?></a></h2>
<p>
    <?php
    if ($data['count'] === 1)
    {
        echo sprintf($data['l10n']->get('one exhibition'), $data['count']);
    }
    else
    {
        echo sprintf($data['l10n']->get('%s exhibitions'), $data['count']);
    }
    
    echo ' ' . sprintf($data['l10n']->get('for year %s'), $data['year']);
    ?>
</p>
