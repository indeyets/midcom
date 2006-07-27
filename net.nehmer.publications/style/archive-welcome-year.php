<?php
// Available request keys: total_count, first_post, year_data, year, url, count, month_data
// month data contains month => url, count pairs.

$data =& $_MIDCOM->get_custom_context_data('request_data');

?>

<h2><a href="&(data['url']);">&(data['year']); (&(data['count']);)</a></h2>

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