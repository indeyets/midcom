<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$view_data =& $_MIDCOM->get_custom_context_data('midcom_helper_datamanager2_widget_composite');
$view = $view_data['item_html'];
if (isset($view_data['item']->id))
{
    $view['id'] = $view_data['item']->id;    
}
$vote_count = 0;
if (isset($data['vote_count']))
{
    $vote_count_totals = $data['vote_count'];
}
else
{
    $vote_count_totals = 0;
}
$midcom_static_url = MIDCOM_STATIC_URL;
$width = 0;
$percentage = 0;
if (   isset($view['id'])
    && $view['id'])
{
    $qb_vote = net_nemein_quickpoll_vote_dba::new_query_builder();
    $qb_vote->add_constraint('selectedoption', '=', $view['id']);
    $vote_count = $qb_vote->count();
    if (   $vote_count > 0
        && $vote_count_totals > 0)
    {
        $percentage = round(100 / $vote_count_totals * $vote_count);
        $width = round(100 / $vote_count_totals * $vote_count);
    }
}

?>

<?php
if (array_key_exists('manage',$data) && $data['manage'])
{
?>
&(view['title']:h);
<br />
<?php
}
elseif (array_key_exists('voted',$data) && $data['voted'])
{
?>
&(view['title']:h);<br />

     <img src='&(midcom_static_url);/net.nemein.quickpoll/pollmeter.gif' height="8" alt="&(vote_count);" title="&(vote_count);" width="&(width);">&nbsp;&(vote_count); (&(percentage); %)
<br />
<?php
}
else
{
    if (isset($view['id']))
    {
?>
<input type="radio" name="net_nemein_quickpoll_option" value="&(view['id']);" id="net_nemein_quickpoll_option_&(view['id']);" />
<label for="net_nemein_quickpoll_option_&(view['id']);"> &(view['title']:h);</label>
<?php
    }
}
?>