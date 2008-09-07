<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX,0);

$results =& $data['stats'];
$nav_left="";
$nav_right="";
$nav_bar=array();
$np = $results['pagenumber'];
$ps = $results['pageresults'];
$isnext = $results['isnext'];
$found = $results['found'];
$q = urlencode($data['query']);
$m = (array_key_exists('m',$_GET))?$_GET['m']:"";
$wm =(array_key_exists('wm',$_GET))? $_GET['wm']:"";

$tp=ceil($found/$ps);

if($np>0)
{
    $prevp=$np-1;
    $prev_href="?q=$q&np=$prevp&m=$m&ps=$ps";
    $nav_left="<a href='{$prev_href}'>".$data['l10n']->get('prev')."</a>";
}
elseif ($np==0 && $tp > 1) 
{
    $nav_left="<span style='color: #707070'>".$data['l10n']->get('prev')."</span>";
}

if($isnext==1) 
{
    $nextp=$np+1;
    $next_href="?q=$q&np=$nextp&m=$m&ps=$ps";
    $nav_right="<a href='{$next_href}'>".$data['l10n']->get('next')."</a>";

}
else if($tp > 1)
{
    $nav_right="<span style='color: #707070'>".$data['l10n']->get('next')."</span>";
}



$cp=$np+1;

if ($cp>5)
{
    $lp=$cp-5;
}
else
{
    $lp=1;
}

$rp=$lp+10-1;

if ($rp>$tp)
{
    $rp=$tp;
    $lp=$rp-10+1;

    if ($lp<1)
    {
    $lp=1;
    }
}

if ($lp!=$rp)
{
    for ($i=$lp; $i<=$rp;$i++) 
    {
        $realp=$i-1;
   
        if ($i==$cp) 
    {
            $nav_bar[] = $i;
        }
    else
    {
            $nav_bar[] = "<a href='?q={$q}&np={$realp}&m={$m}&ps={$ps}'>{$i}</a>";
    }
    }
   
}

?>
<table border="0" align="center">
  <tr>
    <td>&(nav_left:h);</td>
<?php
foreach ($nav_bar as $bar)
{
?>
    <td>&(bar:h);</td>
<?php
}
?>
    <td>&(nav_right:h);</td>
  </tr>
</table>