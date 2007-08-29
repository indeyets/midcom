<?php
// Available request keys: total_count, first_post, year_data

$summary = sprintf($data['l10n']->get('there is a total of %d issues.'), $data['total_count']);

if ($data['first_post'])
{
    $summary .= ' ' . sprintf($data['l10n']->get('first issue is from %s.'),
        $data['first_post']->format($data['l10n_midcom']->get('short date')));
}
?>

<h1><?php echo $data['topic']->extra; ?>: <?php $data['l10n']->show('archive'); ?></h1>

<p>&(summary);</p>