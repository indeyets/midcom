<?php
// Available request keys: total_count, first_event, last_event, first_month, last_month, 
// year_data

//$data =& $_MIDCOM->get_custom_context_data('request_data');

$summary = sprintf($data['l10n']->get('there is a total of %d events.'), $data['total_count']);

if ($data['first_event'])
{
    $summary .= ' ' . sprintf($data['l10n']->get('first event started on %s.'),
        strftime('%x', strtotime($data['first_event']->start)));
}

$summary .= ' ' . $data['l10n']->get('numbers in parentheses show ongoing events.')
?>

<h1><?php echo $data['topic']->extra; ?>: <?php $data['l10n']->show('archive'); ?></h1>

<p>&(summary);</p>