<?php
// Available request keys: total_count, first_post, year_data, year, url, count, month_data
// month data contains month => url, count pairs.

$year_string = "{$data['year']} ({$data['count']})";
if ($data['config']->get('archive_years_enable'))
{
    $year_string = "<a href=\"{$data['url']}\">{$year_string}</a>";
}
?>

<h2>&(year_string:h);</h2>

<p>
<?php
$first = true;
foreach ($data['month_data'] as $month => $month_data)
{
    if ($first)
    {
        $first = false;
    }
    else
    {
        echo " - ";
    }
    ?>
    <a href="&(month_data['url']);">&(month_data['name']); (&(month_data['count']);)</a>
<?php } ?>
</p>