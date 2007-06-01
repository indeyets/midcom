<?php
// // Available request keys: total_count, first_event, last_event, first_month, last_month, 
// year_data, year, url, count, month_data
// month data contains month => url, count pairs.

//$data =& $_MIDCOM->get_custom_context_data('request_data');

?>

<h2><a href="&(data['url']);">&(data['year']); <?php
    if ($data['count'] > 0)
    {
        ?> (&(data['count']);)</a><?php
    }?></a></h2>

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
    <a href="&(month_data['url']);">&(month_data['name']);<?php
    if ($month_data['count'] > 0)
    {
        ?> (&(month_data['count']);)</a><?php
    }
} 
?>
</p>